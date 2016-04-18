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
    private $paypalContext = NULL;
    
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
    
		
    private function initPaypal() {
        if (!$this->paypalContext) {
            $configArr = $this->getServiceLocator()->get('Config');
            $config = $configArr['ERS\PayPal'];
            
            $this->paypalContext = new ApiContext(new OAuthTokenCredential($config['client_id'], $config['client_secret']));
            $this->paypalContext->setConfig([
                'mode' => ($config['sandbox_mode'] ? 'sandbox' : 'live'),
                'log.LogEnabled' => (bool)$config['log_file'],
                'log.FileName' => $config['log_file'],
                'log.LogLevel' => ($config['sandbox_mode'] ? 'DEBUG' : 'INFO'),
                'http.CURLOPT_CONNECTTIMEOUT' => 40
            ]);
        }
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
        $this->initPaypal();

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
            $payment->create($this->paypalContext);
        } catch (PayPalConnectionException $ex) {
            throw new PayPalServiceException("Error creating PayPal payment.", $ex);
        }

        return [$payment->getId(), $payment->getApprovalLink()];
    }

    public function executePayment($paymentId, $payerId) {
        $this->initPaypal();

        try {
            $payment = Payment::get($paymentId, $this->paypalContext);
        } catch (PayPalConnectionException $ex) {
            throw new PayPalServiceException("Error fetching PayPal payment.", $ex);
        }

        $paymentExec = new PaymentExecution();
        $paymentExec->setPayerId($payerId);

        try {
            $payment->execute($paymentExec, $this->paypalContext);
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
