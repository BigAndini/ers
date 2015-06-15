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
 
    public function ordersAction() {
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');

        // TODO figure out correctness of the fast accumulator fields - then remove the more complex queries
        $qb = $em->createQueryBuilder();
        $order_data = $qb
                ->select('COUNT(DISTINCT o.id) ordercount', 'SUM(i.price) totalsum')
                ->from('ersEntity\Entity\Order', 'o')
                ->join('o.packages', 'p')
                ->join('p.items', 'i', 'WITH', $qb->expr()->neq('i.status', $qb->expr()->literal('cancelled')))
                ->getQuery()->getSingleResult();
        
        $qb = $em->createQueryBuilder();
        $paymentFees = $qb
                ->select('(pay.fixFee + SUM(i.price)*pay.percentageFee/100) AS fee')
                ->from('ersEntity\Entity\Order', 'o')
                ->join('o.paymentType', 'pay')
                ->join('o.packages', 'p')
                ->join('p.items', 'i', 'WITH', $qb->expr()->neq('i.status', $qb->expr()->literal('cancelled')))
                ->groupBy('o.id')
                ->getQuery()->getArrayResult();
        
        
        $order_data['paymentfees'] = array_sum(array_map(function($row){ return floatval($row['fee']); }, $paymentFees));
        
        
        $fastResults = $em->createQueryBuilder()
                ->select('SUM(o.total_sum) totalsum_fast', 'SUM(o.order_sum) ordersum_fast')->from('ersEntity\Entity\Order o')
                ->getQuery()->getSingleResult();
        
        $order_data['totalsum_fast'] = $fastResults['totalsum_fast'];
        $order_data['paymentfees_fast'] = $fastResults['totalsum_fast'] - $fastResults['ordersum_fast'];
        
        
        
        $orderSelectFields = array('count(o.id) AS ordercount', 'sum(o.order_sum) AS ordersum, sum(o.total_sum) AS totalsum');
        
        $paymentStatusStats = $em->createQueryBuilder()
                ->select(array_merge(array('o.payment_status AS label'), $orderSelectFields))
                ->from('ersEntity\Entity\Order', 'o')
                ->groupBy('o.payment_status')
                ->getQuery()->getResult();
        
        
        $paymentTypeStats = $em->createQueryBuilder()
                ->select(array_merge(array('pt.name AS label'), $orderSelectFields))
                ->from('ersEntity\Entity\PaymentType', 'pt')
                ->join('pt.orders', 'o')
                ->groupBy('pt.id')
                ->getQuery()->getResult();
        
        
        
        return new ViewModel(array(
            'order_data' => $order_data,
            'stats_paymentStatuses' => $paymentStatusStats,
            'stats_paymentTypes' => $paymentTypeStats
        ));
    }
    
    
    public function participantsAction() {
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        /*
         * === by agegroups ===
         */
        $qb = $em->createQueryBuilder();
        $users = $qb
                ->select('u.birthday', 'SUM(i.price) ordersum')
                ->from('ersEntity\Entity\User', 'u')
                ->join('u.packages', 'p')
                ->join('p.items', 'i', 'WITH', $qb->expr()->neq('i.status', $qb->expr()->literal('cancelled')))
                ->groupBy('u.id')
                ->getQuery()->getResult();
        
        $agegroupServicePrice = $this->getServiceLocator()
                ->get('PreReg\Service\AgegroupService:price');
        $agegroupServiceTicket = $this->getServiceLocator()
                ->get('PreReg\Service\AgegroupService:ticket');
        
        $agegroupStatsPrice = array();
        $agegroupStatsTicket = array();
        
        $defEntry = array('count' => 0, 'amount' => 0, 'amount2' => 0);
        
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
        }
        
        
        /*
         * === by product type ===
         */
        $productStats = $em->createQueryBuilder()
                ->select('prod.displayName', 'COUNT(DISTINCT u.id) AS usercount', 'COUNT(i.id) itemcount')
                ->from('ersEntity\Entity\Package', 'p')
                ->join('p.participant', 'u')
                ->join('p.items', 'i')
                ->join('i.product', 'prod')
                ->groupBy('prod.id')
                ->getQuery()->getResult();
        
        
        /*
         * === by product variant ===
         */
        $variants = $em->getRepository("ersEntity\Entity\ProductVariant")
                ->findBy(array('type' => 'select'));
        
        $variantStats = array();
        foreach($variants as $variant) {
            $variantStats[$variant->getName()] = array();
            foreach($variant->getProductVariantValues() as $value) {
                $repository = $em->getRepository("ersEntity\Entity\ItemVariant");

                $qb = $repository->createQueryBuilder('i')
                        ->select('count(i.id)')
                        ->where('i.ProductVariantValue_id = :value_id')
                        ->setParameter('value_id', $value->getId());
                
                $count = $qb->getQuery()->getSingleScalarResult();
                $variantStats[$variant->getName()][$value->getValue()] = $count;
            }
        }
        
        return new ViewModel(array(
           'stats_agegroupPrice' => $agegroupStatsPrice,
           'stats_agegroupTicket' => $agegroupStatsTicket,
           'stats_productType' => $productStats,
           'stats_variant' => $variantStats
        ));
    }
    
    
    public function bankaccountsAction() {
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $allStats = array();
        $matchingStats = array();
        
        $bankaccounts = $em->getRepository("ersEntity\Entity\BankAccount")
                ->findAll();
        
        /* @var $bankaccount \ersEntity\Entity\BankAccount */
        foreach($bankaccounts as $bankaccount) {
            $statementFormat = json_decode($bankaccount->getStatementFormat());
            
            // TODO maybe exclude disabled statements in both views?
            $qb = $em->createQueryBuilder()
                    ->select('COUNT(s.id) AS stmtcount', 'SUM(col.value) AS amount')
                    ->from('ersEntity\Entity\BankAccount', 'acc')
                    ->join('acc.bankStatements', 's')
                    ->join('s.bankStatementCols', 'col', 'WITH', 'col.column = :colNum')
                    ->where('acc.id = :accountId')
                    
                    ->setParameter('accountId', $bankaccount->getId())
                    ->setParameter('colNum', $statementFormat->amount);
            
            $allStats[$bankaccount->getName()] = $qb
                    ->getQuery()->getSingleResult();
            
            // extend the query to filter statements based on whether on not they have a match
            $matchingStats[$bankaccount->getName()] = $qb
                    ->andWhere('(SELECT COUNT(m.id) FROM ersEntity\Entity\Match m WHERE m.BankStatement_id = s.id) > 0')
                    ->getQuery()->getSingleResult();
            
            // TODO only count matches that have status == "active"?
        }
        
        
        
        return new ViewModel(array(
            'allStats' => $allStats,
            'matchingStats' => $matchingStats,
        ));
    }
}