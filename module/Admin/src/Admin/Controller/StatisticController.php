<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class StatisticController extends AbstractActionController {
    public function indexAction() {
        return new ViewModel();
    }
    
    public function orgasAction() {
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $qb1 = $em->getRepository('ErsBase\Entity\Order')->createQueryBuilder('o');
        $qb1->select(array('SUM(o.order_sum) as ordersum'));
        $qb1->join('o.status', 's');
        $qb1->join('o.paymentType', 'pt');
        $qb1->where($qb1->expr()->eq('s.value', ':status'));
        #$qb1->groupBy('pt.name');
        
        $qb1->setParameter('status', 'paid');
        
        $ordersums = $qb1->getQuery()->getSingleResult();
        
        
        /* SELECT SUM( order_sum ) , SUM( total_sum )
            FROM `order`
            JOIN `match` ON `order`.id = `match`.order_id
            JOIN bank_statement ON bank_statement.id = `match`.bank_statement_id
            JOIN bank_account ON bank_account.id = bank_statement.bank_account_id
            WHERE bank_account.id =2
         */
        $qb2 = $em->getRepository('ErsBase\Entity\Match')->createQueryBuilder('m');
        $qb2->select(array('SUM(o.order_sum) as ordersum'));
        $qb2->join('m.order', 'o');
        $qb2->join('m.bankStatement', 'bs');
        $qb2->join('bs.bankAccount', 'ba');
        $qb2->where($qb2->expr()->eq('ba.id', ':bank_account_id'));
        #$qb2->groupBy('pt.name');
        
        $qb2->setParameter('bank_account_id', '2');
        
        $volunteers1 = $qb2->getQuery()->getSingleResult();
        
        $qb3 = $em->getRepository('ErsBase\Entity\Package')->createQueryBuilder('p');
        $qb3->select(array('COUNT(p.id) as participants'));
        $qb3->join('p.status', 's');
        $qb3->where($qb3->expr()->eq('s.value', ':status1'));
        $qb3->orWhere($qb3->expr()->eq('s.value', ':status2'));
        
        $qb3->setParameter('status1', 'paid');
        $qb3->setParameter('status2', 'ordered');
        
        $participants = $qb3->getQuery()->getSingleResult();
        
        $deadlines = $em->getRepository('ErsBase\Entity\Deadline')
                ->findBy(array(), array('deadline' => 'DESC'));
        $agegroups = $em->getRepository('ErsBase\Entity\Agegroup')
                ->findBy(array('price_change' => 1), array('agegroup' => 'DESC'));
        $last_agegroup = new \ErsBase\Entity\Agegroup();
        $last_agegroup->setAgegroup(new \DateTime('01.01.1000'));
        $last_agegroup->setName('adult');
        $agegroups[] = $last_agegroup;
        $participant_stats = array();
        
        foreach($deadlines as $deadline) {
            foreach($agegroups as $agegroup) {
                $qb4 = $em->getRepository('ErsBase\Entity\Package')->createQueryBuilder('p');
                $qb4->select(array('COUNT(p.id) as participants', 's.value'));
                $qb4->join('p.status', 's');
                $qb4->join('p.order', 'o');
                $qb4->join('p.user', 'u');
                $qb4->where($qb4->expr()->eq('s.value', ':status1'));
                $qb4->orWhere($qb4->expr()->eq('s.value', ':status2'));
                $qb4->andWhere($qb4->expr()->between('o.created', ':date_from', ':date_to'));
                $qb4->andWhere($qb4->expr()->gt('u.birthday', ':agegroup1'));
                $qb4->groupBy('s.value');

                $qb4->setParameter('status1', 'paid');
                $qb4->setParameter('status2', 'ordered');
                $qb4->setParameter('date_from', $deadline->getDeadline());
                $qb4->setParameter('date_to', new \DateTime());
                $qb4->setParameter('agegroup1', $agegroup->getAgegroup());

                $participant_stats[$deadline->getId()][$agegroup->getName()] = $qb4->getQuery()->getResult();
            }
        }
        
        $stat_deadline = array();
        foreach($deadlines as $deadline) {
            $stat_deadline[$deadline->getId()] = $deadline;
        }
        $stat_agegroup = array();
        foreach($agegroups as $agegroup) {
            $stat_agegroup[$agegroup->getName()] = $agegroup;
        }
        
        return new ViewModel(array(
            'ordersums' => $ordersums,
            'volunteers1' => $volunteers1,
            'participants' => $participants,
            'participant_stats' => $participant_stats,
            'deadlines' => $stat_deadline,
            'agegroups' => $stat_agegroup,
        ));
    }
    
    public function ordersAction() {
        $em = $this->getServiceLocator()
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
                ->groupBy('s.value', 's.id')
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
                ->groupBy('u.id', 'shipped', 's.value')
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
            $aggregateTicket['amount'] += $user['ordersum'];
            
            if($user['status'] == 'paid') {
                $aggregatePrice['paid']++;
                $aggregateTicket['paid']++;
            }
            if($user['shipped'] == 1) {
                $aggregatePrice['onsite']++;
                $aggregateTicket['onsite']++;
            } 
            
            $countryId = $user['country_id'] ?: 0;
            $countryName = $user['country_name'] ?: "unknown";
            if(!isset($countryStats[$countryId])) {
                $countryStats[$countryId] = array('name' => $countryName, 'count' => 0);
            }
            $countryStats[$countryId]['count']++;
        }
        
        // postprocess countries: sort descending by count (then by name)
        uasort($countryStats, function($a, $b){ return ($b['count'] - $a['count']) ?: strcmp($a['name'], $b['name']); });
        // move 'unknown' (ID 0) to front
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
        
        $productStatsBase = array_column($em->createQueryBuilder()
                        ->select(
                                $pseudoIdColumn,
                                'prod.name label', 
                                'COUNT(DISTINCT u.id) AS usercount', 
                                'COUNT(i.id) itemcount')
                        ->from('ErsBase\Entity\Product', 'prod')
                        ->join('prod.items', 'i')
                        ->join('i.status', 's', 'WITH', 's.active = 1')
                        ->join('i.package', 'p')
                        ->join('p.user', 'u')
                        ->groupBy('prod.id')
                        ->orderBy('prod.position')
                        ->getQuery()->getResult(), NULL, 'pseudoId');
        
        $productStatsPaid = array_column($em->createQueryBuilder()
                        ->select(
                                $pseudoIdColumn,
                                #'prod.name label', 
                                #'COUNT(DISTINCT u.id) AS usercount', 
                                'COUNT(i.id) paid')
                        ->from('ErsBase\Entity\Product', 'prod')
                        ->join('prod.items', 'i')
                        ->join('i.status', 's', 'WITH', 's.active = 1')
                        ->join('i.package', 'p')
                        ->join('p.user', 'u')
                        ->where($qb->expr()->eq('s.value', ':status'))
                        ->groupBy('prod.id')
                        ->orderBy('prod.position')
                        ->setParameter('status', 'paid')
                        ->getQuery()->getResult(), NULL, 'pseudoId');
        
        $productStatsOrdered = array_column($em->createQueryBuilder()
                        ->select(
                                $pseudoIdColumn,
                                #'prod.name label', 
                                #'COUNT(DISTINCT u.id) AS usercount', 
                                'COUNT(i.id) ordered')
                        ->from('ErsBase\Entity\Product', 'prod')
                        ->join('prod.items', 'i')
                        ->join('i.status', 's', 'WITH', 's.active = 1')
                        ->join('i.package', 'p')
                        ->join('p.user', 'u')
                        ->where($qb->expr()->eq('s.value', ':status'))
                        ->groupBy('prod.id')
                        ->orderBy('prod.position')
                        ->setParameter('status', 'ordered')
                        ->getQuery()->getResult(), NULL, 'pseudoId');
        
        $productStats = array_merge_recursive(
                $productStatsBase, 
                $productStatsPaid, 
                $productStatsOrdered);
        
        
        /*
         * === by payment type ===
         */
        
        $pseudoIdColumn = "CONCAT('x', pt.id) pseudoId";
        
        $paymentTypeStatsBase = array_column($em->createQueryBuilder()
                ->select(
                        $pseudoIdColumn,
                        'pt.name label', 
                        'COUNT(DISTINCT u.id) AS usercount', 
                        'COUNT(i.id) itemcount',
                        'SUM(i.price*i.amount) as amount')
                ->from('ErsBase\Entity\PaymentType', 'pt')
                ->join('pt.orders', 'o')
                ->join('o.packages', 'p')
                ->join('p.user', 'u')
                ->join('p.items', 'i')
                ->join('i.status', 's', 'WITH', 's.active = 1')
                ->groupBy('pt.id')
                ->orderBy('pt.position')
                ->getQuery()->getResult(), NULL, 'pseudoId');
        
        $paymentTypeStatsPaid = array_column($em->createQueryBuilder()
                ->select(
                        $pseudoIdColumn,
                        'COUNT(i.id) paid')
                ->from('ErsBase\Entity\PaymentType', 'pt')
                ->join('pt.orders', 'o')
                ->join('o.packages', 'p')
                ->join('p.user', 'u')
                ->join('p.items', 'i')
                ->join('i.status', 's', 'WITH', 's.active = 1')
                ->where($qb->expr()->eq('s.value', ':status'))
                ->groupBy('pt.id')
                ->orderBy('pt.position')
                ->setParameter('status', 'paid')
                ->getQuery()->getResult(), NULL, 'pseudoId');
        
        $paymentTypeStatsOrdered = array_column($em->createQueryBuilder()
                ->select(
                        $pseudoIdColumn,
                        'COUNT(i.id) ordered')
                ->from('ErsBase\Entity\PaymentType', 'pt')
                ->join('pt.orders', 'o')
                ->join('o.packages', 'p')
                ->join('p.user', 'u')
                ->join('p.items', 'i')
                ->join('i.status', 's', 'WITH', 's.active = 1')
                ->where($qb->expr()->eq('s.value', ':status'))
                ->groupBy('pt.id')
                ->orderBy('pt.position')
                ->setParameter('status', 'ordered')
                ->getQuery()->getResult(), NULL, 'pseudoId');
        
        $paymentTypeStats = array_merge_recursive(
                $paymentTypeStatsBase,
                $paymentTypeStatsPaid,
                $paymentTypeStatsOrdered);
        
        /*
         * === by bankaccount ===
         */
        
        $pseudoIdColumn = "CONCAT('x', ba.id) pseudoId";
        
        $bankAccountStatsBase = array_column($em->createQueryBuilder()
                ->select(
                        $pseudoIdColumn,
                        'ba.name label', 
                        'COUNT(DISTINCT u.id) AS usercount', 
                        'COUNT(i.id) itemcount',
                        'SUM(i.price*i.amount) as amount')
                ->from('ErsBase\Entity\BankAccount', 'ba')
                ->join('ba.bankStatements', 'bs')
                ->join('bs.matches', 'm')
                ->join('m.order', 'o')
                ->join('o.packages', 'p')
                ->join('p.user', 'u')
                ->join('p.items', 'i')
                ->join('i.status', 's', 'WITH', 's.active = 1')
                ->groupBy('ba.id')
                #->orderBy('ba.position')
                ->getQuery()->getResult(), NULL, 'pseudoId');
        
        $bankAccountStatsPaid = array_column($em->createQueryBuilder()
                ->select(
                        $pseudoIdColumn,
                        'COUNT(i.id) paid')
                ->from('ErsBase\Entity\BankAccount', 'ba')
                ->join('ba.bankStatements', 'bs')
                ->join('bs.matches', 'm')
                ->join('m.order', 'o')
                ->join('o.packages', 'p')
                ->join('p.user', 'u')
                ->join('p.items', 'i')
                ->join('i.status', 's', 'WITH', 's.active = 1')
                ->where($qb->expr()->eq('s.value', ':status'))
                ->groupBy('ba.id')
                #->orderBy('ba.position')
                ->setParameter('status', 'paid')
                ->getQuery()->getResult(), NULL, 'pseudoId');
        
        $bankAccountStatsOrdered = array_column($em->createQueryBuilder()
                ->select(
                        $pseudoIdColumn,
                        'COUNT(i.id) ordered')
                ->from('ErsBase\Entity\BankAccount', 'ba')
                ->join('ba.bankStatements', 'bs')
                ->join('bs.matches', 'm')
                ->join('m.order', 'o')
                ->join('o.packages', 'p')
                ->join('p.user', 'u')
                ->join('p.items', 'i')
                ->join('i.status', 's', 'WITH', 's.active = 1')
                ->where($qb->expr()->eq('s.value', ':status'))
                ->groupBy('ba.id')
                #->orderBy('ba.position')
                ->setParameter('status', 'ordered')
                ->getQuery()->getResult(), NULL, 'pseudoId');
        
        $bankAccountStats = array_merge_recursive(
                $bankAccountStatsBase,
                $bankAccountStatsPaid,
                $bankAccountStatsOrdered);
        
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
            $variantValueIdColumns = []; // list of query variables that hold the ProductVariantValue.id results
            $variantValueLabelColumns = []; // list of query variables that hold the ProductVariantValue.value results
            $i = 0;
            foreach($product->getProductVariants() as $variant) {
                $variantNames[] = $variant->getName();
                
                $ivarName = 'ivar' . $i; // internal name of the "ItemVariant" entities
                $idParamName = 'variantId' . $i; // parameter to bind the variant id to
                $varValName = 'varvalue' . $i; // internal name of the "ProductVariantValue" entities
                $varValIdCol = 'valueId' . $i; // column name of the id of the ProductVariantValue
                $varValLabelCol = 'label' . $i; // column name of the string value of the ProductVariantValue
                
                $qb = $qb->join('i.itemVariants', $ivarName, 'WITH', "$ivarName.product_variant_id = :$idParamName")
                         ->join("$ivarName.productVariantValue", $varValName)
                         ->addSelect("$varValName.id $varValIdCol", "$varValName.value $varValLabelCol")
                         ->addGroupBy("$varValName.id")
                         ->addOrderBy("$varValName.position")
                         ->setParameter($idParamName, $variant->getId());
                
                $variantValueIdColumns[] = $varValIdCol;
                $variantValueLabelColumns[] = $varValLabelCol;
                
                $i++;
            }
            
            // skip products that don't have any variants
            if(empty($variantNames)) continue;
            
            $variantData = [];
            
            // prepopulate with default values for all variant combinations
            $allCombinations = $this->generateAllVariantCombinations($product);
            foreach($allCombinations as $combinationKey => $combinationLabels) {
               $variantData[$combinationKey] = [
                    "variantLabels" => $combinationLabels,
                    "itemCount" => 0
                ];
            }
            
            // fill with real itemcount values for present entries
            foreach($qb->getQuery()->getResult() as $row) {
                // ':'-separated list of ids as the key
                $combinationKey = ':' . implode(':', array_map(function($col) use ($row) { return $row[$col]; }, $variantValueIdColumns));
                $variantData[$combinationKey]["itemCount"] = $row["itemcount"];
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
            'stats_paymentType' => $paymentTypeStats,
            'stats_bankaccount' => $bankAccountStats,
            'stats_productVariant' => $itemsByVariantByProduct,
            'stats_country' => $countryStats,
        ));
    }
    
    private function generateAllVariantCombinations(\ErsBase\Entity\Product $product) {
        $results = [ '' => [] ];
        
        // produce an array of all possible combinations of variant values
        foreach ($product->getProductVariants() as $variant) {
            $newResults = [];
            // combine each entry so far with each value of the new variant
            foreach($results as $key => $tuple) {
                foreach($variant->getProductVariantValues() as $variantValue) {
                    $newKey = $key . ':' . $variantValue->getId();
                    $newTuple = $tuple;
                    $newTuple[] = $variantValue->getValue();
                    $newResults[$newKey] = $newTuple;
                }
            }
            
            $results = $newResults;
        }
        
        return $results;
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