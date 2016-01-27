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
use ErsBase\Entity;

use PreReg\Form;

class PaymentController extends AbstractActionController { 
    public function indexAction() {
        $this->notFoundAction();
    }
    
    /**
     * Formular for paying the order via bank transfer
     */
    public function banktransferAction() {
        $hashkey = $this->params()->fromRoute('hashkey', '');
        
        if($hashkey == '') {
            return $this->notFoundAction();
        }
        
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $order = $em->getRepository('ErsBase\Entity\Order')
                ->findOneBy(array('hashkey' => $hashkey));
        
        return new ViewModel(array(
            'order' => $order,
        ));
    }
    
    /**
     * Formular for paying the order via cheque
     */
    public function chequeAction() {
        $hashkey = $this->params()->fromRoute('hashkey', '');
        
        if($hashkey == '') {
            return $this->notFoundAction();
        }
        
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $order = $em->getRepository('ErsBase\Entity\Order')
                ->findOneBy(array('hashkey' => $hashkey));
        
        return new ViewModel(array(
            'order' => $order,
        ));
    }
    
    /**
     * Formular for paying the order via cheque
     */
    /*public function paypalAction() {
        $hashkey = $this->params()->fromRoute('hashkey', '');
        
        if($hashkey == '') {
            return $this->notFoundAction();
        }
        
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $order = $em->getRepository('ErsBase\Entity\Order')
                ->findOneBy(array('hashkey' => $hashkey));
        
        //setup config object
        $config = array(
            'username'      => 'your_username',
            'password'      => 'your_password',
            'signature'     => 'your_signature',
            'endpoint'      => 'https://api-3t.sandbox.paypal.com/nvp' //this is sandbox endpoint
        );
        $paypalConfig = new \SpeckPaypal\Element\Config($config);

        //set up http client
        $client = new \Zend\Http\Client;
        $client->setMethod('POST');
        $client->setAdapter(new \Zend\Http\Client\Adapter\Curl);
        $paypalRequest = new \SpeckPaypal\Service\Request;
        $paypalRequest->setClient($client);
        $paypalRequest->setConfig($paypalConfig);
        
        $paymentDetails = new \SpeckPaypal\Element\PaymentDetails(array(
            'amt' => '20.00'
        ));
        $express = new \SpeckPaypal\Request\SetExpressCheckout(array('paymentDetails' => $paymentDetails));
        $express->setReturnUrl('http://www.someurl.com/return');
        $express->setCancelUrl('http://www.someurl.com/cancel');

        $response = $paypalRequest->send($express);

        echo $response->isSuccess();

        $token = $response->getToken();
        
        return new ViewModel(array(
            'order' => $order,
        ));
    }*/
    
    /**
     * Formular for paying the order via credit card
     */
    public function iPaymentAction() {
        $hashkey = $this->params()->fromRoute('hashkey', '');
        
        if($hashkey == '') {
            return $this->notFoundAction();
        }
        
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $order = $em->getRepository('ErsBase\Entity\Order')
                ->findOneBy(array('hashkey' => $hashkey));
        
       
        
        $config = $this->getServiceLocator()->get('Config');
        
        $account_id     = $config['ERS\iPayment']['account_id'];
        $trxuser_id     = $config['ERS\iPayment']['trxuser_id'];
        $trx_currency   = $config['ERS\iPayment']['trx_currency'];
        $trxpassword    = $config['ERS\iPayment']['trxpassword'];
        $sec_key        = $config['ERS\iPayment']['sec_key'];
        $tmp_action     = $config['ERS\iPayment']['action'];
        $action = preg_replace('/%account_id%/', $account_id, $tmp_action);
        
        $logger = $this->getServiceLocator()->get('Logger');
        
        if($order != null) {
            $a = new \NumberFormatter("de-DE", \NumberFormatter::PATTERN_DECIMAL);
            $a->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, 0);
            $a->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, 0);
            $trx_amount = $a->format($order->getSum()*100); # amount in cents
        } else {
            return $this->redirect()->toRoute('order', array('action' => 'cc-error'));
        }
        
        #https://ipayment.de/merchant/99999/processor.php?trxuser_id=99999&trxpassword=0&trx_amount=12300&trx_paymenttyp=cc&trx_currency=EUR&redirect_url=http://ejc2016.ers.inbaz.org/thankyou&hidden_trigger_url=https://ejc2016.ers.inbaz.org/payment/cc-check
        
        $logger->info('trxuser_id: '.$trxuser_id);
        $logger->info('trx_amount: '.$trx_amount);
        $logger->info('trx_currency: '.$trx_currency);
        $logger->info('trxpassword: '.$trxpassword);
        $logger->info('sec_key: '.$sec_key);
        
        $logger->info('sec_string: '.$trxuser_id.$trx_amount.$trx_currency.$trxpassword.$sec_key);
        
        $trx_securityhash = \md5($trxuser_id.$trx_amount.$trx_currency.$trxpassword.$sec_key);
        
        $logger->info('trx_securityhash: '.$trx_securityhash);
        
        $param = array(
            'trxuser_id' => $trxuser_id,
            'trxpassword' => $trxpassword,
            'trx_amount' => $trx_amount,
            'trx_paymenttyp' => 'cc',
            'trx_currency' => $trx_currency,
            'trx_redirect_url' => $this->url()->fromRoute(
                        'order', 
                        array(
                            'action' => 'thankyou',
                            'hashkey' => $order->getHashkey(),
                            ), 
                        array('force_canonical' => true)
                ),
            'hidden_trigger_url' => $this->url()->fromRoute(
                        'payment', 
                        array(
                            'action' => 'cc-check',
                            'hashkey' => $order->getHashkey(),
                            ), 
                        array('force_canonical' => true)
                ),
            'trx_securityhash' => $trx_securityhash,
        );
        
        $ipayment_url = $action.'?'.http_build_query($param);
        
        return new ViewModel(array(
            'ipayment_url' => $ipayment_url,
        ));
    }
    
    public function creditcardAction() {
        $hashkey = $this->params()->fromRoute('hashkey', '');
        
        if($hashkey == '') {
            return $this->notFoundAction();
        }
        
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $order = $em->getRepository("ErsBase\Entity\Order")
                ->findOneBy(array('hashkey' => $hashkey));
        
       
        
        $config = $this->getServiceLocator()->get('Config');
        
        $account_id     = $config['ERS\iPayment']['account_id'];
        $trxuser_id     = $config['ERS\iPayment']['trxuser_id'];
        $trx_currency   = $config['ERS\iPayment']['trx_currency'];
        $trxpassword    = $config['ERS\iPayment']['trxpassword'];
        $sec_key        = $config['ERS\iPayment']['sec_key'];
        $tmp_action     = $config['ERS\iPayment']['action'];
        $action = preg_replace('/%account_id%/', $account_id, $tmp_action);
        
        $logger = $this->getServiceLocator()->get('Logger');
        
        if($order != null) {
            $a = new \NumberFormatter("de-DE", \NumberFormatter::PATTERN_DECIMAL);
            $a->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, 0);
            $a->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, 0);
            $trx_amount = $a->format($order->getSum()*100); # amount in cents
        } else {
            return $this->redirect()->toRoute('order', array('action' => 'cc-error'));
        }
        
        $form = $this->getServiceLocator()->get('PreReg\Form\CreditCard');
        
        #$form->setAttribute('action', 'https://ipayment.de/merchant/'.$trxuser_id.'/processor/2.0/');
        $form->setAttribute('action', $action);
        
        if($order != null) {
            $silent_error_url = $this->url()->fromRoute(
                        'order', 
                        array(
                            'action' => 'cc-error',
                            'hashkey' => $order->getHashkey(),
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
                            'hashkey' => $order->getHashkey(),
                            ), 
                        array('force_canonical' => true)
                ));
        $form->get('hidden_trigger_url')->setValue(
                $this->url()->fromRoute(
                        'payment', 
                        array(
                            'action' => 'cc-check',
                            'hashkey' => $order->getHashkey(),
                            ), 
                        array('force_canonical' => true)
                ));
        $form->get('trxuser_id')->setValue($trxuser_id);
        $form->get('trxpassword')->setValue($trxpassword);
        
        /*$logger->info('trxuser_id: '.$trxuser_id);
        $logger->info('trx_amount: '.$trx_amount);
        $logger->info('trx_currency: '.$trx_currency);
        $logger->info('trxpassword: '.$trxpassword);
        $logger->info('sec_key: '.$sec_key);*/
        
        $trx_securityhash = \md5($trxuser_id.$trx_amount.$trx_currency.$trxpassword.$sec_key);
        #$logger->info('trx_securityhash: '.$trx_securityhash);
        
        $form->get('trx_securityhash')->setValue($trx_securityhash);
        $form->get('trx_amount')->setValue($trx_amount);
        $form->get('trx_currency')->setValue($trx_currency);
        if($order != null) {
            $form->get('shopper_id')->setValue($order->getCode()->getValue());
        }
        
        $options = array();
        $options[] = array(
            'value' => 'VisaCard',
            'label' => 'VISA Card',
        );
        $options[] = array(
            'value' => 'MasterCard',
            'label' => 'MasterCard',
        );
        $form->get('cc_typ')->setAttribute('options', $options);
        
        
        return new ViewModel(array(
            'order' => $order,
            'form' => $form,
        ));
    }
    
    public function ccCheckAction() {
        $response = $this->getResponse();
        $response->setStatusCode(200);
        $response->setContent("Thank you");
        
        $config = $this->getServiceLocator()->get('Config');
        $sec_key        = $config['ERS\iPayment']['sec_key'];
        
        $allowed_ips = array(
            '212.227.34.218',
            '212.227.34.219',
            '212.227.34.220',
        );
        
        $logger = $this->getServiceLocator()->get('Logger');
        
        $request = new \Zend\Http\PhpEnvironment\Request();
        
        $ipmatch = false;
        if(in_array($request->getServer('REMOTE_ADDR'), $allowed_ips)) {
            $ipmatch = true;
        } else {
            $logger->info('unauthorized hidden trigger from: '.$request->getServer('REMOTE_ADDR'));
            return $response;
        }
        
        $post_param = $this->params()->fromPost();
        $logger->info('$_POST:');
        $logger->info($post_param);
        
        $return_checksum = array();
        if (isset($post_param["trxuser_id"])) {
            $return_checksum[] = $post_param["trxuser_id"];
        }
        if (isset($post_param["trx_amount"])) {
            $return_checksum[] = $post_param["trx_amount"];
        }
        if (isset($post_param["trx_currency"])) {
            $return_checksum[] = $post_param["trx_currency"];
        }
        if (isset($post_param["ret_authcode"])) {
            $return_checksum[] = $post_param["ret_authcode"];
        }
        
        if (isset($post_param["ret_trx_number"])) {
            $return_checksum[] = $post_param["ret_trx_number"];
        }
        $return_checksum[] = $sec_key;
        $logger->info($return_checksum);
        $logger->info('ret_param: '.$post_param["ret_param_checksum"]);
        $logger->info('hash     : '.md5(implode($return_checksum)));
        if ($post_param["ret_param_checksum"] != md5(implode($return_checksum))) {
            // Error because hash do not match!
            $logger->emerg('Unable to finish payment, checksums do not match.');
            return $response;
        }
        
        $hashkey = $this->params()->fromRoute('hashkey', '');
        if($hashkey == '') {
            $logger->warn('no hashkey given in route');
            return $response;
        }
        
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $order = $em->getRepository('ErsBase\Entity\Order')
                ->findOneBy(array('hashkey' => $hashkey));
        
        if($order == null) {
            $logger->warn('unable to find order with hashkey: '.$hashkey);
            return $response;
        }
        
        $status = $em->getRepository("ErsBase\Entity\Status")
                ->findOneBy(array('value' => 'paid'));
        
        $order->setPaymentStatus('paid');
        foreach($order->getItems() as $item) {
            #$item->setStatus('paid');
            $item->setStatus($status);
            $em->persist($item);
        }
        
        $order->setStatus($status);
        
        /*$orderStatus = new Entity\OrderStatus;
        $orderStatus->setOrder($order);
        $orderStatus->setValue('paid');
        $order->addOrderStatus($orderStatus);*/
        $em->persist($order);
        #$em->persist($orderStatus);
        $em->flush();
        
        return $response;
    }
}