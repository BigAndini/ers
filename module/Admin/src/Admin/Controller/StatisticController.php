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
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        /*
         * orders
         */
        $orders = $em->getRepository("ersEntity\Entity\Order")
                ->findBy(array(), array('created' => 'DESC'));
        
        /*
         * bankaccounts
         */
        $bankaccounts = $em->getRepository("ersEntity\Entity\BankAccount")
                ->findAll();
        
        /*
         * product variants
         */
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
        $paymenttypes = $em->getRepository("ersEntity\Entity\PaymentType")
                ->findAll();
        
        /*
         * products
         */
        $products = $em->getRepository("ersEntity\Entity\Product")
                ->findBy(array('visible' => 1));
        
        /*
         * participants
         */
        $users = $em->getRepository("ersEntity\Entity\User")
                ->findBy(array(), array('created' => 'DESC'));
        return new ViewModel(array(
            'orders' => $orders,
            'bankaccounts' => $bankaccounts,
            'products' => $products,
            'variants' => $variants,
            'variant_stats' => $variant_stats,
            'paymenttypes' => $paymenttypes,
            'participants' => $users,
            'agegroupService' => $this->getServiceLocator()
                ->get('PreReg\Service\AgegroupService:ticket'),
        ));
    }
    
    public function ordersAction() {
        $em = $this->getServiceLocator()
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
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $matches = $em->getRepository("ersEntity\Entity\Match")
                ->findAll();
        
        $stats = array();
        $sum = 0;
        foreach($matches as $match) {
            $statement = $match->getBankStatement();
            $bankaccount = $statement->getBankAccount();
            #$order = $match->getOrder();
            
            if(isset($stats[$bankaccount->getName()])) {
                $stats[$bankaccount->getName()] += (float) $statement->getAmount()->getValue();
            } else {
                $stats[$bankaccount->getName()] = (float) $statement->getAmount()->getValue();
            }
            $sum += (float) $statement->getAmount()->getValue();
        }
        $stats['total sum'] = $sum;
        
        return new ViewModel(array(
            'stats' => $stats,
        ));
    }
}