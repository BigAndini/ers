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
 
    public function indexAction()
    {
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        /*
         * orders
         */
        // TODO figure out correctness of the fast accumulator fields - then remove the more complex queries
        $qb = $em->createQueryBuilder();
        $order_data = $qb
                ->select('COUNT(DISTINCT o.id) ordercount', 'SUM(i.price) totalsum')
                ->from('ersEntity\Entity\Order', 'o')
                ->leftJoin('o.packages', 'p')
                ->leftJoin('p.items', 'i', 'WITH', $qb->expr()->neq('i.status', $qb->expr()->literal('cancelled')))
                ->getQuery()->getSingleResult();
        
        $qb = $em->createQueryBuilder();
        $paymentFees = $qb
                ->select('(pay.fixFee + SUM(i.price)*pay.percentageFee/100) AS fee')
                ->from('ersEntity\Entity\Order', 'o')
                ->join('o.paymentType', 'pay')
                ->leftJoin('o.packages', 'p')
                ->leftJoin('p.items', 'i', 'WITH', $qb->expr()->neq('i.status', $qb->expr()->literal('cancelled')))
                ->groupBy('o.id')
                ->getQuery()->getArrayResult();
        
        
        $order_data['paymentfees'] = array_sum(array_map(function($row){ return floatval($row['fee']); }, $paymentFees));
        
        
        $fastResults = $em->createQueryBuilder()
                ->select('SUM(o.total_sum) totalsum_fast', 'SUM(o.order_sum) ordersum_fast')->from('ersEntity\Entity\Order o')
                ->getQuery()->getSingleResult();
        
        $order_data['totalsum_fast'] = $fastResults['totalsum_fast'];
        $order_data['paymentfees_fast'] = $fastResults['totalsum_fast'] - $fastResults['ordersum_fast'];
        
        
        /*
         * product variants
         */
        // acceptable performance
        $variants = $em->getRepository("ersEntity\Entity\ProductVariant")
                ->findBy(array('type' => 'select'));
        
        $variant_stats = array();
        foreach($variants as $variant) {
            #$varaint_stats[$variant->getId()] = array();
            foreach($variant->getProductVariantValues() as $value) {
                $repository = $em->getRepository("ersEntity\Entity\ItemVariant");

                $qb = $repository->createQueryBuilder('i')
                        ->select('count(i.id)')
                        ->where('i.ProductVariantValue_id = :value_id')
                        ->setParameter('value_id', $value->getId());
                
                $count = $qb->getQuery()->getSingleScalarResult();
                $variant_stats[$variant->getId()][$value->getId()] = $count;
            }
        }
        
        /*
         * payment types
         */
        // ca. 500 ms --> done optimized!
        $paymenttypes = $em->getRepository("ersEntity\Entity\PaymentType")
                ->findAll();
        
        /*
         * products
         */
        // ca. 2000 ms ---> done optimized!
        $products = $em->getRepository("ersEntity\Entity\Product")
                ->findBy(['visible' => 1]);
        
        /*
         * participants
         */
        // only load birthdays for performance reasons
        $users = $em->createQueryBuilder()->select('u.birthday')->from('ersEntity\Entity\User u')->getQuery()->getResult();
        $agegroupService = $this->getServiceLocator()
                ->get('PreReg\Service\AgegroupService:price');
        
        $participant_stats = array();
        $participant_stats['all'] = count($users);
        $participant_stats['adult'] = 0;
        /* @var $agegroup \ersEntity\Entity\Agegroup */
        foreach($agegroupService->getAgegroups() AS $agegroup) {
            $participant_stats[$agegroup->getName()] = 0;
        }
        
        foreach($users as $user) {
            $agegroup = $agegroupService->getAgegroupByDate($user['birthday']);
            if($agegroup) {
                $key = $agegroup->getName();
            } else {
                $key = 'adult';
            }
            
            $participant_stats[$key]++;
        }
        
        
        return new ViewModel(array(
            'order_data' => $order_data,
            'products' => $products,
            'variants' => $variants,
            'variant_stats' => $variant_stats,
            'paymenttypes' => $paymenttypes,
            'participant_stats' => $participant_stats
        ));
    }
    
    public function ordersAction() {
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $repository = $em->getRepository("ersEntity\Entity\Order");

        $qb = $repository->createQueryBuilder('o')
                ->select(array('o.payment_status', 'count(o.id)', 'sum(o.order_sum)'))
                ->groupBy('o.payment_status');
        $result = $qb->getQuery()->getResult();
        
        return new ViewModel(array(
            'order_stats' => $result,
        ));
    }
    
    public function bankaccountsAction() {
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $allStats = array();
        $allStatsSum = array("amount" => 0, "count" => 0);
        $matchingStats = array();
        $matchingStatsSum = array("amount" => 0, "count" => 0);
        
        $statementValueCache = array();
        
        $bankaccounts = $em->getRepository("ersEntity\Entity\BankAccount")
                ->findAll();
        
        /* @var $bankaccount \ersEntity\Entity\BankAccount */
        foreach($bankaccounts as $bankaccount) {
            $statVals = array();
            
            /* @var $statement \ersEntity\Entity\BankStatement */
            foreach($bankaccount->getBankStatements() as $statement) {
                $statVals[$statement->getId()] = (float) $statement->getAmount()->getValue();
            }
            
            // keep individual computed values in cache for reuse below
            $statementValueCache[$bankaccount->getName()] = $statVals;
            
            $currSum = array_sum($statVals);
            $currCount = count($statVals);
            $allStats[$bankaccount->getName()] = array("amount" => $currSum, "count" => $currCount);
            $allStatsSum["amount"] += $currSum;
            $allStatsSum["count"] += $currCount;
            
            // pre-initialize matched statements account
            $matchingStats[$bankaccount->getName()] = array("amount" => 0, "count" => 0);
        }
        
        
        
        
        $matches = $em->getRepository("ersEntity\Entity\Match")
                ->findAll();
        
        /* @var $match \ersEntity\Entity\Match */
        foreach($matches as $match) {
            $statement = $match->getBankStatement();
            $bankaccountName = $statement->getBankAccount()->getName();
            $value = $statementValueCache[$bankaccountName][$statement->getId()];
            //$value = (float) $statement->getAmount()->getValue();
            
            $matchingStats[$bankaccountName]["amount"] += $value;
            $matchingStats[$bankaccountName]["count"]++;
            $matchingStatsSum["amount"] += $value;
            $matchingStatsSum["count"]++;
        }
        
        
        
        return new ViewModel(array(
            'allStats' => $allStats,
            'allStatsSum' => $allStatsSum,
            'matchingStats' => $matchingStats,
            'matchingStatsSum' => $matchingStatsSum,
        ));
    }
}