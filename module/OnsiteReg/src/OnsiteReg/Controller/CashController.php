<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace OnsiteReg\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use OnsiteReg\Form;
use Zend\Session\Container;

class CashController extends AbstractActionController {
    public function indexAction() {
        $container = new Container('ers');
        
        $container->agegroup = 'adult';
        
        $em = $this->getServiceLocator()
                ->get('Doctrine\ORM\EntityManager');
        $agegroups = $em->getRepository('ErsBase\Entity\Agegroup')
                ->findBy(['price_change' => 1]);
        $products = $em->getRepository('ErsBase\Entity\Product')
                ->findBy([], ['position' => 'ASC']);
        
        $sum = 0;
        return new ViewModel(array(
            'agegroups' => $agegroups,
            'agegroup' => $container->agegroup,
            'products' => $products,
            'sum' => $sum,
        ));
    }
    public function checkoutAction() {
        $sum = 0;
        return new ViewModel(array(
            'sum' => $sum,
        ));
    }
}