<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace PreReg\Service;

use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Api\Payer;
use PayPal\Api\Transaction;
use PayPal\Api\Amount;
use PayPal\Api\ItemList;
use PayPal\Api\Item;
use PayPal\Api\Payment;
use PayPal\Api\RedirectUrls;
use PayPal\Api\PaymentExecution;
use PayPal\Exception\PayPalConnectionException;

/**
 * PayPal Serivce
 */
class PayPalService
{
    
    public function __construct() {
    }
    
    /**
     * set ServiceLocator
     * 
     * @param ServiceLocator $sl
     */
    public function setServiceLocator($sl) {
        $this->_sl = $sl;
    }
    
    /**
     * get ServiceLocator
     * 
     * @return ServiceLocator
     */
    protected function getServiceLocator() {
        return $this->_sl;
    }
    
    
    /**
     * Configures a PayPal ApiContext according to a PaymentType and returns it.
     */
    private function initPaypalService(\ErsBase\Entity\PaymentType $paymentType) {
        $clientId = $paymentType->getClientId();
        $clientSecret = $paymentType->getClientSecret();
        $sandboxMode = $paymentType->getSandboxMode();
        $logFile = $paymentType->getLogFile();
        
        $paypalContext = new ApiContext(new OAuthTokenCredential($clientId, $clientSecret));
        $paypalContext->setConfig([
            'mode' => ($sandboxMode ? 'sandbox' : 'live'),
            'log.LogEnabled' => (bool)$logFile,
            'log.FileName' => $logFile,
            'log.LogLevel' => ($sandboxMode ? 'DEBUG' : 'INFO'),
            'http.CURLOPT_CONNECTTIMEOUT' => 40
        ]);
        
        return $paypalContext;
    }

    /**
     * Initiates a payment with PayPal.
     * 
     * @param Order $order         the order to be paid
     * @param string $returnUrl    URL to return to
     * @param string $cancelUrl    URL where the user is redirected if they cancel the payment
     * 
     * @return array [(payment id), (target url to authorize payment)]
     * @throws PayPalServiceException if the request to PayPal failed for some reason
     * */
    public function createPayment(\ErsBase\Entity\Order $order, $returnUrl, $cancelUrl) {
        $paypalContext = $this->initPaypalService($order->getPaymentType());

        $str_total = $this->formatPrice($order->getTotalSum());
        $invoiceNumber = $order->getCode()->getValue(); // use order code as invoice number
        
        $paypalItems = [];
        foreach($order->getPackages() as $package) {
            foreach($package->getItems() as $item) {
                $paypalItems[] = (new Item())
                    ->setName($item->getProduct()->getName())
                    ->setQuantity(1)
                    ->setCurrency('EUR')
                    ->setPrice($this->formatPrice($item->getPrice()))
                ;
            }
        }
        
        $paymentFees = $order->getPaymentFees();
        if($paymentFees > 0) {
            $paypalItems[] = (new Item())
                ->setName('Payment fees')
                ->setQuantity(1)
                ->setCurrency('EUR')
                ->setPrice($this->formatPrice($paymentFees))
            ;
        }
        
        // build request to PayPal
        $payer = new Payer();
        $payer->setPaymentMethod('paypal');

        $transaction = new Transaction();
        $transaction->setAmount((new Amount())
                        ->setCurrency('EUR')
                        ->setTotal($str_total)
        );
        
        $transaction->setItemList((new ItemList())
                        ->setItems($paypalItems)
        );
        
        $transaction->setInvoiceNumber($invoiceNumber);

        $redirectUrls = new RedirectUrls();
        $redirectUrls->setReturnUrl($returnUrl);
        $redirectUrls->setCancelUrl($cancelUrl);

        $payment = new Payment();
        $payment->setIntent('sale');
        $payment->setPayer($payer);
        $payment->setTransactions([$transaction]);
        $payment->setRedirectUrls($redirectUrls);

        try {
            $payment->create($paypalContext);
        } catch (PayPalConnectionException $ex) {
            throw new PayPalServiceException("Error creating PayPal payment.", $ex);
        }

        return [$payment->getId(), $payment->getApprovalLink()];
    }

    public function executePayment(\ErsBase\Entity\PaymentType $paymentType, $paymentId, $payerId) {
        $paypalContext = $this->initPaypalService($paymentType);

        try {
            $payment = Payment::get($paymentId, $paypalContext);
        } catch (PayPalConnectionException $ex) {
            throw new PayPalServiceException("Error fetching PayPal payment.", $ex);
        }

        $paymentExec = new PaymentExecution();
        $paymentExec->setPayerId($payerId);

        try {
            $payment->execute($paymentExec, $paypalContext);
        } catch (PayPalConnectionException $ex) {
            throw new PayPalServiceException("Error executing PayPal payment.", $ex);
        }

        return ($payment->getState() === "approved");
    }
    
    /**
     * Formats a price to a string usable by the PayPal API.
     * @param type $price price in EUR as a number
     * @return string
     */
    private function formatPrice($price) {
        return number_format($price, 2, '.', '');
    }

}
