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
        $this->notFoundAction();
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
        $hashKey = $this->params()->fromRoute('hashkey', '');
        
        if($hashKey == '') {
            return $this->notFoundAction();
        }
        
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $order = $em->getRepository("ersEntity\Entity\Order")
                ->findOneBy(array('hashKey' => $hashKey));
        
        /*$session_order = new Container('order');
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $order = $em->getRepository("ersEntity\Entity\Order")->findOneBy(array('id' => $session_order->order_id));*/
        
        $config = $this->getServiceLocator()->get('Config');
        
        $account_id     = $config['ERS\iPayment']['account_id'];
        $trxuser_id     = $config['ERS\iPayment']['trxuser_id'];
        $trx_currency   = $config['ERS\iPayment']['trx_currency'];
        $trxpassword    = $config['ERS\iPayment']['trxpassword'];
        $sec_key        = $config['ERS\iPayment']['sec_key'];
        $tmp_action     = $config['ERS\iPayment']['action'];
        $action = preg_replace('/%account_id%/', $account_id, $tmp_action);
        
        $logger = $this
            ->getServiceLocator()
            ->get('Logger');
        
        
        #$trxuser_id = '99999';
        #$trx_currency = 'EUR';
        #$trxpassword = '0';
        #$sec_key = 'ohPinei6chahnahcoesh';
        
        if($order != null) {
            $a = new \NumberFormatter("de-DE", \NumberFormatter::PATTERN_DECIMAL);
            $a->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, 0);
            $a->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, 0);
            $trx_amount = $a->format($order->getSum()*100); # amount in cents
        } else {
            $trx_amount = 0;
        }
        
        
        #$form = new Form\CreditCard();
        $form = $this->getServiceLocator()->get('PreReg\Form\CreditCard');
        
        #$form->setAttribute('action', 'https://ipayment.de/merchant/'.$trxuser_id.'/processor/2.0/');
        $form->setAttribute('action', $action);
        
        if($order != null) {
            $silent_error_url = $this->url()->fromRoute(
                        'order', 
                        array(
                            'action' => 'cc-error',
                            'hashkey' => $order->getHashKey(),
                            ), 
                        array('force_canonical' => true)
                );
            $form->get('silent_error_url')->setValue($silent_error_url);
        }
        $form->get('redirect_url')->setValue(
                $this->url()->fromRoute(
                        'order', 
                        array(
                            'action' => 'thankyou',
                            'hashkey' => $order->getHashKey(),
                            ), 
                        array('force_canonical' => true)
                ));
        $form->get('trxuser_id')->setValue($trxuser_id);
        $form->get('trxpassword')->setValue($trxpassword);
        
        $logger->info('trxuser_id: '.$trxuser_id);
        $logger->info('trx_amount: '.$trx_amount);
        $logger->info('trx_currency: '.$trx_currency);
        $logger->info('trxpassword: '.$trxpassword);
        $logger->info('sec_key: '.$sec_key);
        
        $trx_securityhash = \md5($trxuser_id.$trx_amount.$trx_currency.$trxpassword.$sec_key);
        #$logger->info('trx_securityhash: '.$trx_securityhash);
        
        $form->get('trx_securityhash')->setValue($trx_securityhash);
        $form->get('trx_amount')->setValue($trx_amount);
        $form->get('trx_currency')->setValue($trx_currency);
        #$form->get('trx_securityhash')->setValue($trx_securityhash);
        if($order != null) {
            $form->get('shopper_id')->setValue($order->getCode()->getValue());
        }
        
        
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