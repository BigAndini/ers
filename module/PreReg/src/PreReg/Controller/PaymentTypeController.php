<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace PreReg\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;

class PaymentTypeController extends AbstractActionController { 
    /*
     * display long description of payment type
     */
    public function infoAction() {
        $id = (int) $this->params()->fromRoute('id', 0);
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $forrest = new Container('forrest');
        
        return new ViewModel(array(
            'paymenttype' => $em->getRepository("ersEntity\Entity\PaymentType")->findOneBy(array('id' => $id)),
            'forrest' => $forrest,
        ));
    }
}