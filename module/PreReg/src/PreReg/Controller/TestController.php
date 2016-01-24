<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace PreReg\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use PreReg\Form;
use PreReg\InputFilter;
use PreReg\Service;
use Zend\Session\Container;
use ErsBase\Entity;

# for pdf generation
use DOMPDFModule\View\Model\PdfModel;

# for sending emails
use Zend\Mail\Message;
use Zend\Mail\Transport;
use Zend\Mime;

class TestController extends AbstractActionController {    
    public function barcodetestAction() {
        $logger = $this->getServiceLocator()->get('Logger');
        
        $start = microtime(true);
        
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $codes = array();
        $count = 0;
        $found = 0;
        while(true) {
            $code = new Entity\Code();
            $code->genCode();
            
            $code = $em->getRepository("ErsBase\Entity\Code")->findOneBy(array('value' => $code->getValue()));
            #if(in_array($code->getValue(), $codes)) {
            if($code) {
                $logger->info('found existing code after '.$count.' tries.');
                $logger->info('time spend: '.(microtime(true)-$start));
                $found++;
                return new ViewModel();                    
                if($found >= 5) {
                    return new ViewModel();                    
                }
            }
            if(!$code->checkCode()) {
                $logger->info('BARCODE IS NOT VALID');
                $logger->info('time spend: '.(microtime(true)-$start));
                return new ViewModel();
            }
            #$codes[] = $code->getValue();
            #$logger->info('added code: '.$code->getValue().' '.(microtime(true)-$start));
            $em->persist($code);
            $em->flush();
            
            $count++;
            if(($count%1000) == 0) {
                $logger->info('time spend: '.(microtime(true)-$start));
            }
        }
        
        return new ViewModel();
    }
    
    public function mailtestAction() {
        $emailService = new Service\EmailFactory();
        $emailService->setFrom('prereg@eja.net');
        
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $user = $em->getRepository("ErsBase\Entity\User")->findOneBy(array('email' => 'andi@sixhop.net'));
        $user = new Entity\User();
        $user->setEmail('andi@inbaz.org');
        $emailService->addTo($user);
        $emailService->setSubject('Testmail');
        $emailService->setHtmlMessage('<h1>Testmail</h1>');
        #$emailService->setTextMessage('Testmail');
        $emailService->addAttachment(getcwd().'/public/img/EJC2015_Terms_and_Services.pdf');
        $emailService->addAttachment(getcwd().'/public/img/logo.jpg');
        $emailService->addAttachment(getcwd().'/public/img/ejc_logo.png');
        $emailService->send();
        
        return true;
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
    
    public function generateInvoiceAction() {
        /*
         * PDF creation
         */
        $pdf = new PdfModel();
        $pdf->setOption("paperSize", "a4"); //Defaults to 8x11
        $pdf->setOption("paperOrientation", "portrait"); //Defaults to portrait
        
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $order = $em->getRepository("ErsBase\Entity\Order")
                    ->findOneBy(array('id' => '87'));
        
        if($order == null) {
            throw new \Exception('unable to find order');
        }
        
        $pdfView = new ViewModel();
        $pdfView->setTemplate('pdf/invoice');
        $pdfView->setVariables(array(
            'order' => $order,
        ));
        $pdfRenderer = $this->getServiceLocator()->get('ViewPdfRenderer');
        $html = $pdfRenderer->getHtmlRenderer()->render($pdfView);
        $pdfEngine = $pdfRenderer->getEngine();

        $pdfEngine->load_html($html);
        $pdfEngine->render();
        $pdfContent = $pdfEngine->output();
        
        $filename = "EJC2016_Invoice";
        file_put_contents(getcwd().'/public/img/'.$filename.'.pdf', $pdfContent);
        
        return new ViewModel();
    }
    public function generatepdfAction() {
        
        if(!extension_loaded('gd')) {
            throw new Exception('PHP Extension gd needs to be loaded.');
        }
        
        /*
         * PDF creation
         */
        $pdf = new PdfModel();
        $pdf->setOption("paperSize", "a4"); //Defaults to 8x11
        $pdf->setOption("paperOrientation", "portrait"); //Defaults to portrait
        $name = 'Andreas Nitsche';
        $code = 'AABBCCDD';

        /*
         * QR-Code creation
         */
        $qr = $this->getServiceLocator()->get('QRCode');
        $qr->isHttps(); // or $qr->isHttp();
        $qr->setData('http://prereg.eja.net/onsite/register/'.  \urlencode($code));
        $qr->setCorrectionLevel('H', 0);
        $qr->setDimensions(200, 200);
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
         * Code creation
         */
        
        // Only the text to draw is required
        $barcodeOptions = array(
            'text' => $code, 
            'barHeight' => 40,
            'factor' => 1.1,
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
         * PDF generation to view
         */
        /*
        $pdf->setVariables(array(
            'name' => $name,
            'code' => $code,
            'qrcode' => $base64_qrcode,
            'barcode' => $base64_barcode,
        ));
        $filename = "EJC2015_e-ticket_".preg_replace('/\ /', '_', $name);
        $pdf->setOption("filename", $filename);
        return $pdf;
        */

        
        
        /***********************************/
        $pdfView = new ViewModel();
        $pdfView->setTemplate('pre-reg/test/generatepdf');
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
        
        $filename = "EJC2016_e-ticket_".preg_replace('/\ /', '_', $name);
        file_put_contents(getcwd().'/public/img/'.$filename.'.pdf', $pdfContent);
        
        return new ViewModel();
        
    }
    public function eticketAction() {
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $order = $em->getRepository("ErsBase\Entity\Order")
                #->findOneBy(array('id' => '297'));
                ->findOneBy(array('id' => '12'));
                #->findOneBy(array('id' => '54'));
        
        /*$this->getServiceLocator()
                ->setShared('DOMPDF', false);*/
        foreach($order->getPackages() as $package) {
            $eticketService = $this->getServiceLocator()
                ->get('PreReg\Service\ETicketService');
            $eticketService->setPackage($package);
            $eticketService->generatePdf();
        }
    }
    public function ccErrorAction() {
        return new ViewModel();
    }
    public function encodingAction() {
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $order = $em->getRepository("ErsBase\Entity\Order")
                    ->findOneBy(array('id' => '17'));
        $viewModel = new ViewModel(array(
            'order' => $order,
        ));
        $viewModel->setTemplate('email/purchase-info.phtml');
        $viewRender = $this->getServiceLocator()->get('ViewRenderer');
        $html = $viewRender->render($viewModel);
        
        $response = new \Zend\Http\Response();
        $response->getHeaders();
                #->addHeaderLine('Content-Type', 'charset=utf-8');
                #->addHeaderLine('Content-Disposition', 'attachment; filename=orders-'.date('Ymd\THis').'.xls')
                #->addHeaderLine('Content-Length', filesize($filename));
        $response->setContent($html);
        return $response;
    }
    public function mailEncodingAction() {
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        #$logger = $this->getServiceLocator()->get('Logger');
        
        $order = $em->getRepository("ErsBase\Entity\Order")
                    ->findOneBy(array('id' => '17'));
        $viewModel = new ViewModel(array(
            'order' => $order,
        ));
        #$viewModel->setTemplate('email/purchase-info.phtml');
        $viewModel->setTemplate('email/order-confirmation.phtml');
        $viewRender = $this->getServiceLocator()->get('ViewRenderer');
        $html = $viewRender->render($viewModel);
        
        #$logger->info('html: '.$html);
        
        $emailService = new Service\EmailFactory();
        $emailService->setFrom('prereg@eja.net');
        
        $buyer = new Entity\User();
        $buyer->setEmail('andi@inbaz.org');
        $emailService->addTo($buyer);
        $emailService->setSubject('order confirmation');
        
        
        $emailService->setHtmlMessage($html);
        #$emailService->setTextMessage('Encoding Test: 42,- €');
        $emailService->send();
        
        return true;
    }
}