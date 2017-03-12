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
        
        $config = $this->getServiceLocator()->get('config');
        
        return new ViewModel(array(
            'order' => $order,
            'config' => $config,
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
     * Action that creates a payment with PayPal and redirects the user to authorize it.
     */
    public function paypalAction() {
        $hashkey = $this->params()->fromRoute('hashkey', '');
        if (!$hashkey)
            return $this->notFoundAction();

        $em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        $order = $em->getRepository('ErsBase\Entity\Order')->findOneBy(array('hashkey' => $hashkey));

        if (!$order || $order->getPaymentType()->getType() !== 'paypal') {
            return new ViewModel(['notfound' => true]);
        }

        if ($order->getStatus()->getValue() !== 'ordered') {
            if ($order->getStatus()->getValue() === 'paid')
                return new ViewModel(['error' => 'This order has already been paid.']);
            else
                return new ViewModel(['error' => 'This order cannot be paid in its current state.']);
        }

        $paypalService = $this->getServiceLocator()->get('ErsBase\Service\PayPalService');

        // initiate the payment with paypal
        $returnUrl = $this->url()->fromRoute('payment', ['action' => 'paypal-confirm'], ['force_canonical' => true]);
        $cancelUrl = $this->url()->fromRoute('order', ['action' => 'view', 'hashkey' => $hashkey], ['force_canonical' => true]);

        try {
            list($paymentId, $paypalUrl) = $paypalService->createPayment($order, $returnUrl, $cancelUrl);
        } catch (\ErsBase\Service\PayPalServiceException $ex) {
            $logger = $this->getServiceLocator()->get('Logger');
            $logger->err($ex);
            return new ViewModel(['error' => 'There was an error while creating the PayPal payment. Please try again later.']);
        }

        $sessionData = new Container('paypal_payment');
        $sessionData->hashkey = $hashkey;
        $sessionData->paymentId = $paymentId;

        // redirect the user to paypal so they can authorize the payment
        return $this->redirect()->toUrl($paypalUrl);
    }

    /**
     * Action that executes a PayPal payment and sets the order status to paid after it was authorized.
     */
    public function paypalConfirmAction() {
        $queryPaymentId = $this->params()->fromQuery('paymentId');
        //$queryToken = $this->params()->fromQuery('token');
        $queryPayerId = $this->params()->fromQuery('PayerID');

        $em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');

        $vm = new ViewModel();
        $vm->setTemplate('pre-reg/payment/paypal.phtml');

        $sessionData = new Container('paypal_payment');
        if (!$sessionData->hashkey || !$sessionData->paymentId) {
            return $vm->setVariables(['error' => 'No active PayPal payment was found. The session probably expired. Please try again.']);
        }
        if ($queryPaymentId !== $sessionData->paymentId) {
            return $vm->setVariables(['error' => 'The payment id is not valid.']);
        }

        $paypalService = $this->getServiceLocator()->get('ErsBase\Service\PayPalService');
        $paidStatus = $em->getRepository('ErsBase\Entity\Status')->findOneBy(['value' => 'paid']);
        $order = $em->getRepository('ErsBase\Entity\Order')->findOneBy(['hashkey' => $sessionData->hashkey]);

        if (!$order) {
            // somehow a valid payment was started with an order hash that is now non-existant
            return $vm->setVariables(['error' => 'The order you are trying to pay no longer exists.']);
        }

        try {
            if ($paypalService->executePayment($order->getPaymentType(), $sessionData->paymentId, $queryPayerId)) {
                // payment was successful, set order to paid
                $order->setPaymentStatus('paid');
                foreach ($order->getPackages() as $package) {
                    $package->setStatus($paidStatus);
                    foreach ($package->getItems() as $item) {
                        $item->setStatus($paidStatus);
                    }
                }
                $order->setStatus($paidStatus);

                $em->persist($order);
                $em->flush();

                // delete the session
                $sessionData->exchangeArray([]);

                return $this->redirect()->toRoute('order', ['action' => 'thankyou', 'hashkey' => $order->getHashkey()]);
            }
        } catch (\ErsBase\Service\PayPalServiceException $ex) {
            $logger = $this->getServiceLocator()->get('Logger');
            $logger->err($ex);
            return $vm->setVariables(['error' => 'There was an error while executing the PayPal payment. Please try again. No money has been charged yet.']);
        }
    }
    
    
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
        
       
        
        /*$config = $this->getServiceLocator()->get('Config');
        
        $account_id     = $config['ERS\iPayment']['account_id'];
        $trxuser_id     = $config['ERS\iPayment']['trxuser_id'];
        $trx_currency   = $config['ERS\iPayment']['trx_currency'];
        $trxpassword    = $config['ERS\iPayment']['trxpassword'];
        $sec_key        = $config['ERS\iPayment']['sec_key'];
        $tmp_action     = $config['ERS\iPayment']['action'];
        $action = preg_replace('/%account_id%/', $account_id, $tmp_action);*/
        
        $paymentType = $order->getPaymentType();
        $account_id     = $paymentType->getAccountId();
        $trxuser_id     = $paymentType->getTrxuserId();
        #$trx_currency   = $order->getCurrency()->getShort();
        $trx_currency   = $paymentType->getTrxcurrency();
        $trxpassword    = $paymentType->getTrxpassword();
        $sec_key        = $paymentType->getSecKey();
        $tmp_action     = $paymentType->getAction();
        $action = preg_replace('/%account_id%/', $account_id, $tmp_action);
        
        if($action == '') {
            throw new \Exception('iPayment configuration is missing');
        }
        
        #$logger = $this->getServiceLocator()->get('Logger');
        
        if($order != null) {
            $a = new \NumberFormatter("de-DE", \NumberFormatter::PATTERN_DECIMAL);
            $a->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, 0);
            $a->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, 0);
            $trx_amount = $a->format($order->getSum()*100); # amount in cents
        } else {
            return $this->redirect()->toRoute('order', array('action' => 'cc-error'));
        }
        
        $trx_securityhash = \md5($trxuser_id.$trx_amount.$trx_currency.$trxpassword.$sec_key);
        
        $param = array(
            'trxuser_id' => $trxuser_id,
            'trxpassword' => $trxpassword,
            'trx_amount' => $trx_amount,
            'trx_paymenttyp' => 'cc',
            'trx_currency' => $trx_currency,
            'redirect_url' => $this->url()->fromRoute(
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
            'shopper_id' => $order->getCode()->getValue(),
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
        
        $trx_securityhash = \md5($trxuser_id.$trx_amount.$trx_currency.$trxpassword.$sec_key);
        
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
            $logger->warn('unauthorized hidden trigger from: '.$request->getServer('REMOTE_ADDR'));
            return $response;
        }
        
        $post_param = $this->params()->fromPost();
        
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
        foreach($order->getPackages() as $package) {
            $package->setStatus($status);
            foreach($package->getItems() as $item) {
                $item->setStatus($status);
            }
        }
        /*foreach($order->getItems() as $item) {
            #$item->setStatus('paid');
            $item->setStatus($status);
            $em->persist($item);
        }*/
        
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