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

# for sending emails
use Zend\Mail\Message;
use Zend\Mail\Transport;
use Zend\Mime;

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
    
    public function mailtestAction() {
        $content  = new Mime\Message();
        
        $textContent = 'This is the text of the email.';
        $textPart = new Mime\Part($textContent);
        $textPart->type = 'text/plain';
        
        $htmlMarkup = '<html><body><h1>This is the text of the email.</h1></body></html>';
        $htmlPart = new Mime\Part($htmlMarkup);
        $htmlPart->type = 'text/html';
        
        $content->setParts(array($textPart, $htmlPart));

        $contentPart = new Mime\Part($content->generateMessage());        
        $contentPart->type = 'multipart/alternative;' . PHP_EOL . ' boundary="' . $content->getMime()->boundary() . '"';

        $pathToImage = getcwd() . '/public/img/logo.jpg';
        $attachment = new Mime\Part(fopen($pathToImage, 'r'));
        #$attachment->type = 'application/pdf';
        $attachment->type = 'image/jpeg';
        $attachment->filename = 'logo.jpg';
        $attachment->encoding    = Mime\Mime::ENCODING_BASE64;
        $attachment->disposition = Mime\Mime::DISPOSITION_ATTACHMENT;

        $body = new Mime\Message();
        $body->setParts(array($contentPart, $attachment));

        $message = new Message();
        $message->setEncoding('utf-8');
        $message->addTo('andi@inbaz.org');
        $message->addFrom('prereg@eja.net');
        $message->setSubject('Testmail');
        $message->setBody($body);

        $transport = new Transport\Sendmail();
        $transport->send($message);
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
        $qr->setDimensions(200, 200);
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
        
        $qr_content = $response->getContent();
        $base64_qrcode = "data:image/png;base64,".  \base64_encode($qr_content);
        
        #file_put_contents(getcwd().'/public/img/qrcode.png', $qr_content);
        
        /*
         * Barcode creation
         */
        
        // Only the text to draw is required
        $barcodeOptions = array(
            'text' => $code, 
            'barHeight' => 40,
            'factor' => 1.5,
        );

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
        
        #file_put_contents(getcwd().'/public/img/barcode2.jpg', $contents);
        
        $base64_barcode = "data:image/png;base64,".  \base64_encode($contents);
        
        /*
         * PDF generation
         */
        /*
        $pdf->setVariables(array(
            'name' => $name,
            'code' => $code,
            'qrcode' => $base64_qrcode,
            'barcode' => $base64_barcode,
        ));
        $filename = "EJC2015_eTicket_".preg_replace('/\ /', '_', $name);
        $pdf->setOption("filename", $filename);
        return $pdf;
        */

        
        
        /***********************************/
        $pdfView = new ViewModel();
        $pdfView->setTemplate('pre-reg/order/generatepdf');
        $pdfView->setVariables(array(
            'name' => $name,
            'code' => $code,
            'qrcode' => $base64_qrcode,
            'barcode' => $base64_barcode,
        ));
        $pdfRenderer = $this->getServiceLocator()->get('ViewPdfRenderer');
        $html = $pdfRenderer->getHtmlRenderer()->render($pdfView);
        $pdfEngine = $pdfRenderer->getEngine();

        $pdfEngine->load_html($html);
        $pdfEngine->render();
        $pdfContent = $pdfEngine->output();
        
        $filename = "EJC2015_eTicket_".preg_replace('/\ /', '_', $name);
        file_put_contents(getcwd().'/public/img/'.$filename.'.pdf', $pdfContent);
        
        return new ViewModel();
        
    }
}