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

use PreReg\Form;

class PaymentController extends AbstractActionController { 
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
    
    /**
     * Formular for paying the order via bank transfer
     */
    public function banktransferAction() {
        $session_order = new Container('order');
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $order = $em->getRepository("ersEntity\Entity\Order")->findOneBy(array('id' => $session_order->order_id));
        return new ViewModel(array(
            'order' => $order,
        ));
    }
    
    /**
     * Formular for paying the order via credit card
     */
    public function creditcardAction() {
        $session_order = new Container('order');
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $order = $em->getRepository("ersEntity\Entity\Order")->findOneBy(array('id' => $session_order->order_id));
        
        $form = new Form\CreditCard();
        
        $form->setAttribute('action', 'https://ipayment.de/merchant/99999/processor/2.0/');
        
        $years = array();
        for($i=date('Y'); $i<=(date('Y')+15); $i++) {
            #$years[$i] = $i;
            $years[] = array(
                'value' => $i,
                'label' => $i,
            );
        }
        $form->get('cc_expdate_year')->setAttribute('options', $years);
        
        $months = array();
        for($i=1; $i<=12; $i++) {
            $months[] = array(
                'value' => $i,
                'label' => sprintf('%02d', $i),
            );
        }
        $form->get('cc_expdate_month')->setAttribute('options', $months);
        
        return new ViewModel(array(
            'order' => $order,
            'form' => $form,
        ));
    }
    
    /**
     * Formular for paying the order via PayPal
     */
    public function paypalAction() {
        return new ViewModel();
    }
}