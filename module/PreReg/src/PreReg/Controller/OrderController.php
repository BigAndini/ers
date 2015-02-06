<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace PreReg\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
#use StickyNotes\Model\User;
use PreReg\Form;
use Zend\Session\Container;

# for pdf generation
use DOMPDFModule\View\Model\PdfModel;

class OrderController extends AbstractActionController {
 
    /*
     * overview of this order
     */
    public function indexAction() {
        $session_cart = new Container('cart');
        $packages = $session_cart->order->getPackages();
        
        return new ViewModel(array(
            'order' => $session_cart->order,
            #'packages' => $packages,
        ));
    }
    
    /*
     * collect data for the purchaser
     */
    public function registerAction() {
        return new ViewModel();
    }
    
    /*
     * collect payment data and complete purchase
     */
    public function paymentAction() {
        return new ViewModel();
    }
    
    /*
     * last check and checkout
     */
    public function checkoutAction() {
        return new ViewModel();
    }
    
    /*
     * collect data for the invoice
     */
    public function invoiceAction() {
        
    }
    
    /*
     * delete a Package of this Order
     */
    public function deleteAction() {
        
    }
    
    public function generatepdfAction() {
        
        if(!extension_loaded('gd')) {
            die('PHP Extension gd needs to be loaded.');
        }
        
        /*
         * PDF creation
         */
        $pdf = new PdfModel();
        $pdf->setOption("paperSize", "a4"); //Defaults to 8x11
        $pdf->setOption("paperOrientation", "portrait"); //Defaults to portrait
        $name = 'Andreas Nitsche';
        $code = 'AA-BB-CC-DD';

        /*
         * QR-Code creation
         */
        $qr = $this->getServiceLocator()->get('QRCode');
        $qr->isHttps(); // or $qr->isHttp();
        $qr->setData('https://prereg.eja.net/onsite/register/'.  \urlencode($code));
        $qr->setCorrectionLevel('H', 0);
        $qr->setDimensions(100, 100);
        #error_log('QR-Code: '.$qr->getResult());
        $config = array(
            'adapter'      => 'Zend\Http\Client\Adapter\Socket',
            'ssltransport' => 'tls',
            'sslcapath'    => '/etc/ssl/certs/',
            'sslverifypeer' => false,
        );

        // Instantiate a client object
        $client = new \Zend\Http\Client($qr->getResult(), $config);

        // The following request will be sent over a TLS secure connection.
        $response = $client->send();
        
        #error_log('tmpdir: '.sys_get_temp_dir());
        #$qrcodeFile = sys_get_temp_dir() . '/'.md5($response->getContent()).'.png';
        #file_put_contents($qrcodeFile, $response->getContent());
        
        
        $base64_qrcode = "data:image/png;base64,".  \base64_encode($response->getContent());
        #error_log('base64: '.$base64_qrcode);
        
        /*
         * Barcode creation
         */
        
        // Only the text to draw is required
        $barcodeOptions = array('text' => $code);

        // No required options
        $rendererOptions = array();

        // Draw the barcode in a new image,
        $imageResource = \Zend\Barcode\Barcode::factory(
            'code39', 'image', $barcodeOptions, $rendererOptions
        )->draw();
        
        ob_start(); //Start output buffer.
            imagejpeg($imageResource); //This will normally output the image, but because of ob_start(), it won't.
            $contents = ob_get_contents(); //Instead, output above is saved to $contents
        ob_end_clean(); //End the output buffer.
        
        $base64_barcode = "data:image/png;base64,".  \base64_encode($contents);
        
        /*
         * PDF generation
         */
        $pdf->setVariables(array(
            'name' => $name,
            'code' => $code,
            'qrcode' => $base64_qrcode,
            'barcode' => $base64_barcode,
        ));
        $pdf->setOption("filename", "EJC2015_eTicket_".preg_replace('/\ /', '_', $name));
        return $pdf;
    }
}