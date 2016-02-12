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
                ->select(array_merge(array('s.value AS label'), $orderSelectFields))
                ->from('ErsBase\Entity\Order', 'o')
                ->join('o.status', 's')
                #->groupBy('o.payment_status')
                ->groupBy('s.value')
                ->getQuery()->getResult();
        
        
        $byStatusGroups = array('active' => array(), 'inactive' => array());
        foreach($paymentStatusStats AS $status) {
            if($status["label"] === "cancelled" || $status["label"] === "refund")
                $byStatusGroups['inactive'][] = $status;
            else
                $byStatusGroups['active'][] = $status;
        }
        
        $paymentTypeStats = $em->createQueryBuilder()
                ->select(array_merge(array('pt.name AS label'), $orderSelectFields))
                ->from('ErsBase\Entity\PaymentType', 'pt')
                #->join('pt.orders', 'o', 'WITH', "o.payment_status != 'cancelled' AND o.payment_status != 'refund'")
                ->join('pt.orders', 'o')
                ->join('o.status', 's', 'WITH', "s.value != 'cancelled' AND s.value != 'refund'")
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
                ->join('i.status', 's', 'WITH', "s.value != 'cancelled' AND s.value != 'refund'")
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
        
        
        /*
         * === by product type ===
         */
        $productStats = $em->createQueryBuilder()
                ->select('prod.display_name', 'COUNT(DISTINCT u.id) AS usercount', 'COUNT(i.id) itemcount')
                ->from('ErsBase\Entity\Package', 'p')
                ->join('p.user', 'u')
                ->join('p.items', 'i', 'WITH', "i.status != 'cancelled' AND i.status != 'refund'")
                ->join('i.product', 'prod')
                ->groupBy('prod.id')
                ->getQuery()->getResult();
        
        
        /*
         * === by product variant ===
         */
        $variants = $em->getRepository('ErsBase\Entity\ProductVariant')
                ->findBy(array('type' => 'select'));
        
        $variantStats = array();
        /* @var $variant \ErsBase\Entity\ProductVariant */
        foreach($variants as $variant) {
            $variantStats[$variant->getName()] = array();
            foreach($variant->getProductVariantValues() as $value) {
                $qb = $em->createQueryBuilder()
                        ->select('count(DISTINCT i.id)')
                        ->from('ErsBase\Entity\ItemVariant', 'ivar')
                        ->join('ivar.item', 'i', 'WITH', "i.status != 'cancelled' AND i.status != 'refund'")
                        ->where('ivar.product_variant_value_id = :value_id')
                        ->setParameter('value_id', $value->getId());
                
                $count = $qb->getQuery()->getSingleScalarResult();
                $variantStats[$variant->getName()][$value->getValue()] = $count;
            }
        }
        
        
        // postprocess countries: sort descending by count and move "unknown" to the front
        uasort($countryStats, function($a, $b){ return $b['count'] - $a['count']; });
        if(isset($countryStats[0])) {
            $unknownData = $countryStats[0];
            unset($countryStats[0]);
            array_unshift($countryStats, $unknownData);
        }
        
        return new ViewModel(array(
           'stats_agegroupPrice' => $agegroupStatsPrice,
           'stats_agegroupTicket' => $agegroupStatsTicket,
           'stats_productType' => $productStats,
           'stats_variant' => $variantStats,
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