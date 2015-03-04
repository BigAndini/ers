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
use PreReg\Service;

use PreReg\Form;

class PaymentController extends AbstractActionController { 
    public function indexAction() {
        $this->getResponse()->setStatusCode(404);
    }
    /*
     * display long description of payment type
     */
    public function infoAction() {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('product');
        }
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $forrest = new Service\BreadcrumbFactory();
        $breadcrumb = $forrest->get('paymenttype');
        
        return new ViewModel(array(
            'paymenttype' => $em->getRepository("ersEntity\Entity\PaymentType")->findOneBy(array('id' => $id)),
            'breadcrumb' => $breadcrumb,
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
        
        $trxuser_id = '99999';
        $trx_amount = $order->getSum()*100; # amount in cents
        $trx_currency = 'EUR';
        $trxpassword = '0';
        $sec_key = 'ohPinei6chahnahcoesh';
        
        
        $form = new Form\CreditCard();
        
        $form->setAttribute('action', 'https://ipayment.de/merchant/'.$trxuser_id.'/processor/2.0/');
        
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
        
        $form->get('silent_error_url')->setValue(
                $this->url()->fromRoute(
                        'order', 
                        array('action' => 'cc-error'), 
                        array('force_canonical' => true)
                ));
        $form->get('redirect_url')->setValue(
                $this->url()->fromRoute(
                        'order', 
                        array('action' => 'thankyou'), 
                        array('force_canonical' => true)
                ));
        $form->get('trx_amount')->setValue($trx_amount);
        $form->get('trx_currency')->setValue($trx_currency);
        
        $form->get('trx_securityhash')->setValue(
                    md5($trxuser_id.$trx_amount.$trx_currency.$trxpassword.$sec_key)
                );
        $form->get('shopper_id')->setValue($order->getCode()->getValue());
        
        return new ViewModel(array(
            'order' => $order,
            'form' => $form,
        ));
    }
    
    public function checkCCPaymentAction() {
        $security_key= "qundhft67dnft";
        $return_checksum="";
        if (isset($_GET["trxuser_id "]))
            $return_checksum.= $_GET["trxuser_id"];
        if (isset($_GET["trx_amount "]))
            $return_checksum.= $_GET["trx_amount"];
        if (isset($_GET["trx_currency "]))
            $return_checksum.= $_GET["trx_currency"];
        if (isset($_GET["ret_authcode "]))
            $return_checksum.= $_GET["ret_authcode"];
        if (isset($_GET["ret_trx_number "]))
            $return_checksum.= $_GET["ret_trx_number"];
        $return_checksum.= $security_key;
        if ($_GET["ret_param_checksum "]!=md5($return_checksum)) {
            // Error because hash do not match!
            exit;
        }
        
        $security_key= "qundhft67dnft";
        $url= "https://".$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
        $url_without_checksum=
        substr($url, 0, strpos($url, "&ret_url_checksum") + 1);
        if ($_REQUEST['ret_url_checksum'] != md5($url_without_checksum.$security_key)) {
            // Error because hash does not match
        }
        else {
            // URL ok
        }
    }
    
    /**
     * Formular for paying the order via PayPal
     */
    public function paypalAction() {
        return new ViewModel();
    }
}