<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\DBAL\DriverManager;

class StatisticController extends AbstractActionController {
    public function indexAction() {
        return new ViewModel();
    }
    
    public function ordersAction() {
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');

        // old, more complex queries that do not make use of the cached fields and are no longer needed
        /*$qb = $em->createQueryBuilder();
        $order_data = $qb
                ->select('COUNT(DISTINCT o.id) ordercount', 'SUM(i.price) ordersum')
                ->from('ErsBase\Entity\Order', 'o')
                ->join('o.packages', 'p')
                ->join('p.items', 'i', 'WITH', "i.status != 'cancelled' AND i.status != 'refund'")
                ->getQuery()->getSingleResult();
        
        $qb = $em->createQueryBuilder();
        $paymentFees = $qb
                ->select('(pay.fixFee + SUM(i.price)*pay.percentageFee/100) AS fee')
                ->from('ErsBase\Entity\Order', 'o')
                ->join('o.paymentType', 'pay')
                ->join('o.packages', 'p')
                ->join('p.items', 'i', 'WITH', "i.status != 'cancelled' AND i.status != 'refund'")
                ->groupBy('o.id')
                ->getQuery()->getArrayResult();
        
        
        $order_data['paymentfees'] = array_sum(array_map(function($row){ return floatval($row['fee']); }, $paymentFees));
        
        $fastResults = $em->createQueryBuilder()
                ->select('SUM(o.total_sum) totalsum_fast', 'SUM(o.order_sum) ordersum_fast')->from('ErsBase\Entity\Order o')
                ->getQuery()->getSingleResult();
        
        $order_data['ordersum_fast'] = $fastResults['ordersum_fast'];
        $order_data['totalsum_fast'] = $fastResults['totalsum_fast'];
        $order_data['paymentfees_fast'] = $fastResults['totalsum_fast'] - $fastResults['ordersum_fast'];
        */
        
        
        
        $orderSelectFields = array('COUNT(o.id) AS ordercount', 'SUM(o.order_sum) AS ordersum, SUM(o.total_sum) AS totalsum');
        
        $paymentStatusStats = $em->createQueryBuilder()
                #->select(array_merge(array('o.payment_status AS label'), $orderSelectFields))
                ->select(array_merge(array('s status, s.value label'), $orderSelectFields))
                ->from('ErsBase\Entity\Status', 's')
                ->leftJoin('s.orders', 'o')
                #->groupBy('o.payment_status')
                ->groupBy('s.value')
                ->orderBy('s.position')
                ->getQuery()->getResult();
        
        
        
        $byStatusGroups = array('active' => array(), 'inactive' => array());
        foreach($paymentStatusStats AS $statusData) {
            $group = ($statusData['status']->getActive() ? 'active' : 'inactive');
            $byStatusGroups[$group][] = $statusData;
        }
        
        $paymentTypeStats = $em->createQueryBuilder()
                ->select(array_merge(array('pt.name AS label'), $orderSelectFields))
                ->from('ErsBase\Entity\PaymentType', 'pt')
                #->join('pt.orders', 'o', 'WITH', "o.payment_status != 'cancelled' AND o.payment_status != 'refund'")
                ->join('pt.orders', 'o')
                ->join('o.status', 's', 'WITH', "s.active = 1")
                ->groupBy('pt.id')
                ->getQuery()->getResult();
        
        
        
        return new ViewModel(array(
            'stats_paymentStatusGroups' => $byStatusGroups,
            'stats_paymentTypes' => $paymentTypeStats
        ));
    }
    
    
    public function participantsAction() {
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        /*
         * === by agegroups & country ===
         */
        $qb = $em->createQueryBuilder();
        $users = $qb
                ->select(
                        'u.birthday', 
                        'c.id country_id', 
                        'c.name country_name', 
                        'SUM(i.price) ordersum',
                        'i.shipped shipped',
                        's.value status'
                        )
                ->from('ErsBase\Entity\User', 'u')
                ->leftJoin('u.country', 'c')
                ->join('u.packages', 'p')
                #->join('p.items', 'i', 'WITH', "i.status != 'cancelled' AND i.status != 'refund'")
                ->join('p.items', 'i')
                ->join('i.status', 's', 'WITH', "s.active = 1")
                ->groupBy('u.id')
                ->getQuery()->getResult();
        
        $agegroupServicePrice = $this->getServiceLocator()
                ->get('ErsBase\Service\AgegroupService:price');
        $agegroupServiceTicket = $this->getServiceLocator()
                ->get('ErsBase\Service\AgegroupService:ticket');
        
        $agegroupStatsPrice = array();
        $agegroupStatsTicket = array();
        $countryStats = array();
        
        $defEntry = array(
            'count' => 0, # number of participants
            'amount' => 0, # amount of money
            'paid' => 0, # number of participants who paid
            'onsite' => 0, # number of participants onsite
            );
        
        // initialize all groups with 0
        $agegroupStatsPrice['adult'] = $defEntry;
        $agegroupStatsTicket['adult'] = $defEntry;
        foreach($agegroupServicePrice->getAgegroups() AS $agegroup) {
            $agegroupStatsPrice[$agegroup->getName()] = $defEntry;
        }
        foreach($agegroupServiceTicket->getAgegroups() AS $agegroup) {
            $agegroupStatsTicket[$agegroup->getName()] = $defEntry;
        }
        
        // calculate aggregate values
        foreach($users as $user) {
            $agPrice = $agegroupServicePrice->getAgegroupByDate($user['birthday']);
            $agTicket = $agegroupServiceTicket->getAgegroupByDate($user['birthday']);
            
            $aggregatePrice = &$agegroupStatsPrice[($agPrice ? $agPrice->getName() : 'adult')];
            $aggregateTicket = &$agegroupStatsTicket[($agTicket ? $agTicket->getName() : 'adult')];
            
            $aggregatePrice['count']++;
            $aggregatePrice['amount'] += $user['ordersum'];
            $aggregateTicket['count']++;
            
            if($user['status'] == 'paid') {
                $aggregateTicket['paid']++;
            }
            if($user['shipped'] == 1) {
                $aggregateTicket['onsite']++;
            } 
            
            $countryId = $user['country_id'] ?: 0;
            $countryName = $user['country_name'] ?: "unknown";
            if(!isset($countryStats[$countryId])) {
                $countryStats[$countryId] = array('name' => $countryName, 'count' => 0);
            }
            $countryStats[$countryId]['count']++;
        }
        
        // postprocess countries: sort descending by count and move "unknown" (ID 0) to the front
        uasort($countryStats, function($a, $b){ return $b['count'] - $a['count']; });
        if(isset($countryStats[0])) {
            $unknownData = $countryStats[0];
            unset($countryStats[0]);
            array_unshift($countryStats, $unknownData);
        }
        
        
        
        /*
         * === by product type ===
         */
        
        // make sure the column we are indexing by with array_column does not have numeric keys,
        // otherwise array_merge does not do what we want (overwrite default values if present)
        $pseudoIdColumn = "CONCAT('x', prod.id) pseudoId";
        
        $productStats = array_merge(
                // get all products with all counts at 0 first (default values)
                array_column($em->createQueryBuilder()
                        ->select($pseudoIdColumn, 'prod.name label', '0 usercount', '0 itemcount')
                        ->from('ErsBase\Entity\Product', 'prod')
                        ->orderBy('prod.position')
                        ->getQuery()->getResult(),
                    NULL, 'pseudoId'),
                
                // calculate actual counts by product
                array_column($em->createQueryBuilder()
                        ->select($pseudoIdColumn, 'prod.name label', 'COUNT(DISTINCT u.id) AS usercount', 'COUNT(i.id) itemcount')
                        ->from('ErsBase\Entity\Product', 'prod')
                        ->join('prod.items', 'i')
                        ->join('i.status', 's', 'WITH', 's.active = 1')
                        ->join('i.package', 'p')
                        ->join('p.user', 'u')
                        ->groupBy('prod.id')
                        ->orderBy('prod.position')
                        ->getQuery()->getResult(),
                    NULL, 'pseudoId')
            );
        
        
        /*
         * === by product variant ===
         */
        $itemsByVariantByProduct = [];
        $allProducts = $em->getRepository('ErsBase\Entity\Product')->findBy([], ['position' => 'ASC']);
        /* @var $product \ErsBase\Entity\Product */
        foreach($allProducts as $product) {
            $qb = $em->createQueryBuilder()
                        ->select('COUNT(i.id) itemcount')
                        ->from('ErsBase\Entity\Item', 'i')
                        ->join('i.status', 's', 'WITH', 's.active = 1')
                        ->where('i.Product_id = :prod_id')
                        ->setParameter('prod_id', $product->getId());
            
            $variantNames = [];
            $variantValueColumns = [];
            $i = 0;
            foreach($product->getProductVariants() as $variant) {
                $variantNames[] = $variant->getName();
                
                $idParamName = 'variantId' . $i; // parameter to bind the variant id to
                $ivarName = 'ivar' . $i; // internal name of the "ItemVariant" entities
                $varValName = 'varvalue' . $i; // internal name of the "ProductVariantValue" entities
                $varValCol = 'label' . $i; // column name of the string value of the variant
                
                $qb = $qb->join('i.itemVariants', $ivarName, 'WITH', "$ivarName.product_variant_id = :$idParamName")
                         ->join("$ivarName.productVariantValue", $varValName)
                         ->addSelect("$varValName.value $varValCol")
                         ->addGroupBy("$varValName.id")
                         ->addOrderBy("$varValName.position")
                         ->setParameter($idParamName, $variant->getId());
                
                $variantValueColumns[] = $varValCol;
                
                $i++;
            }
            
            // skip products that don't have any variants
            if(empty($variantNames)) continue;
            
            
            $variantData = [];
            foreach($qb->getQuery()->getResult() as $row) {
                // TODO show ALL possible combinations of variant values, not only ones where items are present
                $variantData[] = [
                    "variantLabels" => array_map(function($col) use ($row) { return $row[$col]; }, $variantValueColumns),
                    "itemCount" => $row["itemcount"]
                ];
            }
            
            $itemsByVariantByProduct[] = [
                "productName" => $product->getName(),
                "variantNames" => $variantNames,
                "variantData" => $variantData
            ];
        }
        
        
        
        return new ViewModel(array(
           'stats_agegroupPrice' => $agegroupStatsPrice,
           'stats_agegroupTicket' => $agegroupStatsTicket,
           'stats_productType' => $productStats,
           'stats_productVariant' => $itemsByVariantByProduct,
           'stats_country' => $countryStats,
        ));
    }
    
    public function bankaccountsAction() {
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $activeStats = array();
        $matchingStats = array();
        
        $bankaccounts = $em->getRepository('ErsBase\Entity\BankAccount')
                ->findAll();
        
        /* @var $bankaccount \ErsBase\Entity\BankAccount */
        foreach($bankaccounts as $bankaccount) {
            $statementFormat = json_decode($bankaccount->getStatementFormat());
            
            $qb = $em->createQueryBuilder()
                    ->select('COUNT(s.id) AS stmtcount', 'SUM(col.value) AS amount, MAX(s.created) AS latestentry')
                    ->from('ErsBase\Entity\BankAccount', 'acc')
                    ->join('acc.bankStatements', 's', 'WITH', "s.status != 'disabled'")
                    ->join('s.bankStatementCols', 'col', 'WITH', 'col.column = :colNum')
                    ->where('acc.id = :accountId')
                    
                    ->setParameter('accountId', $bankaccount->getId())
                    ->setParameter('colNum', $statementFormat->amount);
            
            $activeStats[$bankaccount->getName()] = $qb
                    ->getQuery()->getSingleResult();
            
            // extend the query to only include matched statements
            $matchingStats[$bankaccount->getName()] = $qb
                    ->andWhere("s.status = 'matched'")
                    ->getQuery()->getSingleResult();
        }
        
        
        
        return new ViewModel(array(
            'activeStats' => $activeStats,
            'matchingStats' => $matchingStats,
        ));
    }
    
    public function onsiteAction() {
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $qb = $em->getRepository('ErsBase\Entity\Item')->createQueryBuilder('i');
        $qb->where("i.shipped = 1");
        $qb->andWhere($qb->expr()->orX(
                $qb->expr()->eq("i.Product_id", "1"),
                $qb->expr()->eq("i.Product_id", "4")));
        $shippedItems = $qb->getQuery()->getResult();
        
        $itemStats = array();
        foreach($shippedItems as $item) {
            if(isset($itemStats[$item->getShippedDate()->format('Y-m-d')][$item->getShippedDate()->format('H')])) {
                $itemStats[$item->getShippedDate()->format('Y-m-d')][$item->getShippedDate()->format('H')]++;
            } else {
                $itemStats[$item->getShippedDate()->format('Y-m-d')][$item->getShippedDate()->format('H')] = 1;
            }
        }
        
        return new ViewModel(array(
            'itemStats' => $itemStats,
        ));
    }
}