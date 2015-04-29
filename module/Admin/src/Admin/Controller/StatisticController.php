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
        $orders = $em->getRepository("ersEntity\Entity\Order")
                ->findBy(array(), array('created' => 'DESC'));
        
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
            'products' => $products,
            'variants' => $variants,
            'variant_stats' => $variant_stats,
            'paymenttypes' => $paymenttypes,
            'participants' => $users,
        ));
    }
}