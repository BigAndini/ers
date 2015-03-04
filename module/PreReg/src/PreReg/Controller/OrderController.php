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
use ersEntity\Entity;

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
        $forrest = new Service\BreadcrumbFactory();
        $forrest->reset();
        $forrest->set('product', 'order');
        $forrest->set('participant', 'order');
        $forrest->set('cart', 'order');
        
        $session_cart = new Container('cart');
        
        return new ViewModel(array(
            'order' => $session_cart->order,
        ));
    }
    public function overviewAction() {
        $view = $this->indexAction();
        $view->setTemplate('pre-reg/order/index.phtml');
        return $view;
    }
    
    /*
     * collect data for the purchaser
     */
    public function registerAction() {
        $form = new Form\Purchaser();
        
        $session_cart = new Container('cart');
        
        # even if it's not displayed, this is needed to recognize the possible 
        # values.
        if(count($session_cart->order->getPackages()) > 1) {
            $users = $session_cart->order->getParticipants();
            $purchaser = array();
            foreach($users as $participant) {
                $purchaser[] = array(
                    'value' => $participant->getSessionId(),
                    'label' => $participant->getPrename().' '.$participant->getSurname().' ('.$participant->getEmail().')',
                );
            }
            $purchaser[] = array(
                'value' => 0,
                'label' => 'Add purchaser',
            );
            $form->get('purchaser_id')->setValueOptions($purchaser);
        }
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $inputFilter = new InputFilter\Purchaser();
            $form->setInputFilter($inputFilter->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $data = $form->getData();
                if($data['purchaser_id'] == 0) {
                    $purchaser = new Entity\User();
                    $purchaser->populate($data);
                } else {
                    $purchaser = $session_cart->order->getParticipantBySessionId($data['purchaser_id']);
                }
                        
                # add purchser to order
                $session_cart->order->setPurchaser($purchaser);
                
                return $this->redirect()->toRoute('order', array('action' => 'payment'));
            } else {
                error_log(var_export($form->getMessages(), true));
            }
        }
       
        $forrest = new Service\BreadcrumbFactory();
        $forrest->set('participant', 'order', array('action' => 'register'));
        
        /*$forrest = new Container('forrest');
        $breadcrumb = new \ArrayObject();
        $breadcrumb->route = 'order';
        $breadcrumb->params = array(
            'action' => 'register'
        );
        $breadcrumb->options = array();
        $forrest->trace->participant = $breadcrumb;*/
        
        $participants = $session_cart->order->getParticipants();
        
        return new ViewModel(array(
            'form' => $form,
            'order' => $session_cart->order,
            'participants' => $participants,
        ));
    }
    
    /*
     * collect payment data and complete purchase
     */
    public function paymentAction() {
        $forrest = new Service\BreadcrumbFactory();
        $forrest->set('paymenttype', 'order', array('action' => 'payment'));
        
        /*$forrest = new Container('forrest');
        $breadcrumb = new \ArrayObject();
        $breadcrumb->route = 'order';
        $breadcrumb->params = array(
            'action' => 'payment'
        );
        $breadcrumb->options = array();
        $forrest->trace->paymenttype = $breadcrumb;*/
        
        $form = new Form\PaymentType();
        
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        # even if it's not displayed, this is needed to recognize the possible 
        # values.
        $paymenttypes = $em->getRepository("ersEntity\Entity\PaymentType")->findBy(array(), array('ordering' => 'ASC'));
        $types = array();
        foreach($paymenttypes as $paymenttype) {
            $types[] = array(
                'value' => $paymenttype->getId(),
                'label' => $paymenttype->getName(),
            );
        }
        $form->get('paymenttype_id')->setValueOptions($types);
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $inputFilter = new InputFilter\PaymentType();
            $form->setInputFilter($inputFilter->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $data = $form->getData();
                
                $paymenttype = $em->getRepository("ersEntity\Entity\PaymentType")
                        ->findOneBy(array('id' => $data['paymenttype_id']));
                
                $session_cart = new Container('cart');
                $session_cart->order->setPaymentType($paymenttype);
                
                return $this->redirect()->toRoute('order', array('action' => 'checkout'));
            } else {
                error_log(var_export($form->getMessages(), true));
            }
        }
        
        
        return new ViewModel(array(
            'form' => $form,
            'paymenttypes' => $paymenttypes,
        ));
    }
    
    /*
     * last check and checkout
     */
    public function checkoutAction() {
        $session_cart = new Container('cart');
        
        $form = new Form\Checkout();
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $inputFilter = new InputFilter\Checkout();
            $form->setInputFilter($inputFilter->getInputFilter());
            $form->setData($request->getPost());

            # check if the session cart container has all data to finish this order
            $em = $this
                ->getServiceLocator()
                ->get('Doctrine\ORM\EntityManager');
            
            $purchaser = $session_cart->order->getPurchaser();
            $user = $em->getRepository("ersEntity\Entity\User")
                    ->findOneBy(array('email' => $purchaser->getEmail()));
            if($user instanceof Entity\User) {
                $session_cart->order->setPurchaser($user);
                $session_cart->order->setPurchaserId($user->getId());
            } else {
                $em->persist($purchaser);
                $session_cart->order->setPurchaser($purchaser);
                $session_cart->order->setPurchaserId($purchaser->getId());
            }
            
            $paymenttype = $em->getRepository("ersEntity\Entity\PaymentType")
                    ->findOneBy(array('id' => $session_cart->order->getPaymentTypeId()));
            $session_cart->order->setPaymentType($paymenttype);
            $session_cart->order->setPaymentTypeId($paymenttype->getId());
            
            # get the order_id
            $code = new Entity\Code();
            $code->genCode();
            $codecheck = 1;
            while($codecheck != null) {
                $code->genCode();
                error_log('found code: '.$code->getValue());
                $codecheck = $em->getRepository("ersEntity\Entity\Code")
                    ->findOneBy(array('value' => $code->getValue()));
            }
            $em->persist($code);
            $session_cart->order->setCode($code);
            $session_cart->order->setCodeId($code->getId());
            
            $packages = $session_cart->order->getPackages();
            foreach($packages as $package) {
                if(count($package->getItems()) <= 0) {
                    $session_cart->order->removePackage($package);
                    continue;
                }
                $package->setOrder($session_cart->order);
                $package->setOrderId($session_cart->order->getId());
                
                $participant = $package->getParticipant();
                
                if($participant->getPrename() == '' || $participant->getSurname() == '') {
                    $participant = $purchaser;
                }
                
                $user = $em->getRepository("ersEntity\Entity\User")
                        ->findOneBy(array('email' => $participant->getEmail()));
                $role = $em->getRepository("ersEntity\Entity\Role")
                        ->findOneBy(array('roleId' => 'participant'));
                if($user instanceof Entity\User) {
                    $package->setParticipant($user);
                    if(!$user->hasRole($role)) {
                        $user->addRole($role);
                        $em->persist($user);
                    }
                    $package->setParticipantId($user->getId());
                    
                } else {
                    $em->persist($participant);
                    $package->setParticipant($participant);
                    $package->setParticipantId($participant->getId());
                }
                $code = new Entity\Code();
                $code->genCode();
                $codecheck = 1;
                while($codecheck != null) {
                    $code->genCode();
                    $codecheck = $em->getRepository("ersEntity\Entity\Code")
                        ->findOneBy(array('value' => $code->getValue()));
                }
                $em->persist($code);
                $package->setCode($code);
                $package->setCodeId($code->getId());
                
                foreach($package->getItems() as $item) {
                    $product = $em->getRepository("ersEntity\Entity\Product")
                        ->findOneBy(array('id' => $item->getProductId()));
                    $item->setProduct($product);
                    $item->setPackage($package);
                    $code = new Entity\Code();
                    $code->genCode();
                    $codecheck = 1;
                    while($codecheck != null) {
                        $code->genCode();
                        $codecheck = $em->getRepository("ersEntity\Entity\Code")
                            ->findOneBy(array('value' => $code->getValue()));
                    }
                    $item->setCode($code);
                }
                
                $em->persist($package);
            }
            
            $em->persist($session_cart->order);
            $em->flush();
        
            $session_order = new Container('order');
            $session_order->order_id = $session_cart->order->getId();
            
            $session_cart->init = 0;
            
            switch(strtolower($session_cart->order->getPaymentType()->getType())) {
                case 'banktransfer':
                    $this->sendBankTransferEmail();
                    return $this->redirect()->toRoute('payment', array('action' => 'banktransfer'));
                    break;
                case 'creditcard':
                    return $this->redirect()->toRoute('payment', array('action' => 'creditcard'));
                    break;
                case 'paypal':
                    break;
                default:
            }
            
        }
        
        return new ViewModel(array(
            'form' => $form,
            'order' => $session_cart->order,
        ));
    }
    
    private function sendBankTransferEmail() {
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $session_order = new Container('order');
        $order = $em->getRepository("ersEntity\Entity\Order")
                    ->findOneBy(array('id' => $session_order->order_id));
        $purchaser = $order->getPurchaser();
        
        $emailService = new Service\EmailFactory();
        #$emailService->setFrom('prereg@eja.net');
        $emailService->setFrom('prereg@inbaz.org');
        
        $emailService->addTo($purchaser);
        $emailService->setSubject('EJC Registration System: Payment Information');
        
        $viewModel = new ViewModel(array(
            'order' => $order,
        ));
        $viewModel->setTemplate('email/purchase-info.phtml');
        $viewRender = $this->getServiceLocator()->get('ViewRenderer');
        $html = $viewRender->render($viewModel);
        
        $emailService->setHtmlMessage($html);
        #$emailService->setTextMessage('Testmail');
        $emailService->send();
        
        return true;
    }
    
    /*
     * say thank you after purchaser
     */
    public function thankyouAction() {
        $session_cart = new Container('cart');
        #$session_cart->getManager()->getStorage()->clear('cart');
        $session_cart->init = 0;
        return new ViewModel(array(
            'order' => $session_cart->order,
        ));
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
    
    public function barcodetestAction() {
        $start = microtime(true);
        
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $codes = array();
        $count = 0;
        $found = 0;
        while(true) {
            $code = new Entity\Code();
            $code->genCode();
            
            $code = $em->getRepository("ersEntity\Entity\Code")->findOneBy(array('value' => $code->getValue()));
            #if(in_array($code->getValue(), $codes)) {
            if($code) {
                error_log('found existing code after '.$count.' tries.');
                error_log('time spend: '.(microtime(true)-$start));
                $found++;
                return new ViewModel();                    
                if($found >= 5) {
                    return new ViewModel();                    
                }
            }
            if(!$code->checkCode()) {
                error_log('BARCODE IS NOT VALID');
                error_log('time spend: '.(microtime(true)-$start));
                return new ViewModel();
            }
            #$codes[] = $code->getValue();
            #error_log('added code: '.$code->getValue().' '.(microtime(true)-$start));
            $em->persist($code);
            $em->flush();
            
            $count++;
            if(($count%1000) == 0) {
                error_log('time spend: '.(microtime(true)-$start));
            }
        }
        
        return new ViewModel();
    }
    
    public function mailtestAction() {
        $emailService = new Service\EmailFactory();
        #$emailService->setFrom('prereg@eja.net');
        $emailService->setFrom('prereg@inbaz.org');
        
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $user = $em->getRepository("ersEntity\Entity\User")->findOneBy(array('email' => 'andi@sixhop.net'));
        $user = new Entity\User();
        $user->setEmail('web-vtR4MV@mail-tester.com');
        $emailService->addTo($user);
        $emailService->setSubject('Testmail');
        $emailService->setHtmlMessage('<h1>Testmail</h1>');
        #$emailService->setTextMessage('Testmail');
        $emailService->addAttachment('public/img/logo.jpg');
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
         * PDF generation to view
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
    public function ccErrorAction() {
        return new ViewModel();
    }
}