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
        /*$variant_data = array();
        foreach($variants as $variant) {
            $tmp['name'] = $variant->getName();
            $tmp['values'] = $em->getRepository("ersEntity\Entity\ProductVariantValue")
                ->findBy(array('ProductVariant_id' => $variant->getId()));
            $variant_data[] = $tmp;
        }*/
        
        
        /*
         * participants
         */
        $users = $em->getRepository("ersEntity\Entity\User")
                ->findBy(array(), array('created' => 'DESC'));
        return new ViewModel(array(
            'orders' => $orders,
            'variants' => $variants,
            'participants' => $users,
        ));
    }
}