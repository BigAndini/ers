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
         * bankaccounts
         */
        // ca. 5500 ms --> optimized getBankStatementColByNumber in BankStatement
        $bankaccounts = $em->getRepository("ersEntity\Entity\BankAccount")
                ->findAll();
        
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
        $users = $em->createQueryBuilder()->select('u.birthday')->from('ersEntity\Entity\User u')->getQuery()->getResult();
        $agegroupService = $this->getServiceLocator()
                ->get('PreReg\Service\AgegroupService:ticket');
        
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
            'bankaccounts' => $bankaccounts,
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
        
        $matches = $em->getRepository("ersEntity\Entity\Match")
                ->findAll();
        
        $stats = array();
        $sum = 0;
        foreach($matches as $match) {
            $statement = $match->getBankStatement();
            $bankaccountName = $statement->getBankAccount()->getName();
            $value = (float) $statement->getAmount()->getValue();
            
            if(!isset($stats[$bankaccountName])) {
                $stats[$bankaccountName] = 0;
            }
            
            $stats[$bankaccountName] += $value;
            $sum += $value;
        }
        $stats['total sum'] = $sum;
        
        return new ViewModel(array(
            'stats' => $stats,
        ));
    }
}