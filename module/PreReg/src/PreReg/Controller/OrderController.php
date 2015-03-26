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
use Zend\Validator;

class OrderController extends AbstractActionController {
 
    /*
     * overview of this order
     */
    public function indexAction() {
        $orderContainer = new Container('order');
        $orderContainer->getManager()->getStorage()->clear('order');
        
        $forrest = new Service\BreadcrumbFactory();
        $forrest->reset();
        $forrest->set('product', 'order');
        $forrest->set('participant', 'order');
        $forrest->set('cart', 'order');
        
        $cartContainer = new Container('cart');
        
        $this->checkItemPrices();
        
        $logger = $this
                ->getServiceLocator()
                ->get('Logger');
        $logger->info('=== shopping cart start ===');
        foreach($cartContainer->order->getPackages() as $package) {
            $participant = $package->getParticipant();
            $logger->info('participant: '.$participant->getFirstname().' '.$participant->getSurname());
            $logger->info('has '.count($package->getItems()).' items:');
            foreach($package->getItems() as $item) {
                $logger->info(' - '.$item->getName().' '.$item->getPrice());
                foreach($item->getItemVariants() as $variant) {
                    $logger->info('   - '.$variant->getName().': '.$variant->getValue());
                }
                $logger->info('  has '.count($item->getChildItems()).' sub items:');
                foreach($item->getChildItems() as $itemPackage) {
                    $subItem = $itemPackage->getSubItem();
                    $logger->info('   - '.$subItem->getName());
                    foreach($subItem->getItemVariants() as $subVariant) {
                        $logger->info('     - '.$subVariant->getName().': '.$subVariant->getValue());
                    }
                }
            }
        }
        $logger->info('=== shopping cart end ===');
        
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $agegroupService = new Service\AgegroupService();
        $agegroups = $em->getRepository("ersEntity\Entity\Agegroup")
                    ->findBy(array('priceChange' => '1'));
        $agegroupService->setAgegroups($agegroups);
        
        return new ViewModel(array(
            'order' => $cartContainer->order,
            'agegroupService' => $agegroupService,
        ));
    }
    public function overviewAction() {
        $view = $this->indexAction();
        $view->setTemplate('pre-reg/order/index.phtml');
        return $view;
    }
    
    private function checkItemPrices() {
        $cartContainer = new Container('cart');
        $order = $cartContainer->order;
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $deadlineService = new Service\DeadlineService();
        $deadlines = $em->getRepository("ersEntity\Entity\Deadline")
                    ->findBy(array('priceChange' => '1'));
        $deadlineService->setDeadlines($deadlines);
        $deadline = $deadlineService->getDeadline();
        
        $agegroupService = new Service\AgegroupService();
        $agegroups = $em->getRepository("ersEntity\Entity\Agegroup")
                    ->findBy(array('priceChange' => '1'));
        $agegroupService->setAgegroups($agegroups);
        foreach($order->getPackages() as $package) {
            $participant = $package->getParticipant();
            if($participant == null) {
                continue;
            }
            if($participant->getSessionId() == 0) {
                continue;
            }
            $agegroup = $agegroupService->getAgegroupByUser($participant);
            foreach($package->getItems() as $item) {
                $product = $em->getRepository("ersEntity\Entity\Product")
                    ->findOneBy(array('id' => $item->getProductId()));
                if($product != null) {
                    $productPrice = $product->getProductPrice($agegroup, $deadline);
                    if($item->getPrice() != $productPrice->getCharge()) {
                        $item->setPrice($productPrice->getCharge());
                    }
                }
                
            }
        }
    }
    
    /**
     * Action that allows viewing an order by the hash key
     */
    public function viewAction() {
        $hashkey = $this->params()->fromRoute('hashkey', '');
        
        if($hashkey == '') {
            return $this->notFoundAction();
        }
        
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $order = $em->getRepository("ersEntity\Entity\Order")
                ->findOneBy(array('hashkey' => $hashkey));
        
        if($order == null) {
            $logger = $this
                ->getServiceLocator()
                ->get('Logger');
            $logger->info('order for hash key '.$hashkey.' not found');
            return $this->notFoundAction();
        }
        
        return new ViewModel(array(
            'order' => $order,
        ));
    }
    
    /*
     * collect data for the purchaser
     */
    public function purchaserAction() {
        $orderContainer = new Container('order');
        $orderContainer->getManager()->getStorage()->clear('order');
        
        $form = new Form\Register();
        
        $cartContainer = new Container('cart');
        
        # even if it's not displayed, this is needed to recognize the possible 
        # values.
        if(count($cartContainer->order->getPackages()) > 1) {
            $users = $cartContainer->order->getParticipants();
            $purchaser = array();
            foreach($users as $participant) {
                $purchaser[] = array(
                    'value' => $participant->getSessionId(),
                    'label' => $participant->getFirstname().' '.$participant->getSurname().' ('.$participant->getEmail().')',
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
            $inputFilter = new InputFilter\Register();
            $em = $this
                ->getServiceLocator()
                ->get('Doctrine\ORM\EntityManager');
            $inputFilter->setEntityManager($em);
            if($this->zfcUserAuthentication()->hasIdentity()) {
                $inputFilter->setLoginEmail($this->zfcUserAuthentication()->getIdentity()->getEmail());
            }
            $form->setInputFilter($inputFilter->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $data = $form->getData();
                
                $purchaser = $cartContainer->order->getParticipantBySessionId($data['purchaser_id']);
                        
                # add purchser to order
                $cartContainer->order->setPurchaser($purchaser);
                
                return $this->redirect()->toRoute('order', array('action' => 'payment'));
            } else {
                $logger = $this
                    ->getServiceLocator()
                    ->get('Logger');
                $logger->warn($form->getMessages());
            }
        }
       
        $forrest = new Service\BreadcrumbFactory();
        $forrest->set('participant', 'order', array('action' => 'purchaser'));
        $forrest->set('purchaser', 'order', array('action' => 'purchaser'));
        
        $participants = $cartContainer->order->getParticipants();
        
        return new ViewModel(array(
            'form' => $form,
            'order' => $cartContainer->order,
            'participants' => $participants,
        ));
    }
    
    /*
     * collect payment data and complete purchase
     */
    public function paymentAction() {
        $forrest = new Service\BreadcrumbFactory();
        $forrest->set('paymenttype', 'order', array('action' => 'payment'));
        
        $orderContainer = new Container('order');
        $orderContainer->getManager()->getStorage()->clear('order');
        
        $form = new Form\PaymentType();
        
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $paymenttypes = $em->getRepository("ersEntity\Entity\PaymentType")
                ->findBy(array(), array('ordering' => 'ASC'));
        $types = array();
        $now = new \DateTime();
        
        $pts = array();
        foreach($paymenttypes as $paymenttype) {
            if(!$paymenttype->getVisible()) {
                continue;
            }
            $activeFrom = $paymenttype->getActiveFrom();
            $activeUntil = $paymenttype->getActiveUntil();
            if(
                    ($activeFrom == null || $activeFrom->getDeadline()->getTimestamp() <= $now->getTimestamp()) && 
                    ($activeUntil == null || $activeUntil->getDeadline()->getTimestamp() >= $now->getTimestamp())
            ) {
                $pts[] = $paymenttype;
                
            }
        }
        
        foreach($pts as $paymenttype) {
            $types[] = array(
                'value' => $paymenttype->getId(),
                'label' => $paymenttype->getName(),
            );
        }
        $form->get('paymenttype_id')->setValueOptions($types);
        
        $cartContainer = new Container('cart');
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $inputFilter = new InputFilter\PaymentType();
            $form->setInputFilter($inputFilter->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $data = $form->getData();
                
                $paymenttype = $em->getRepository("ersEntity\Entity\PaymentType")
                        ->findOneBy(array('id' => $data['paymenttype_id']));
                
                
                $cartContainer->order->setPaymentType($paymenttype);
                
                return $this->redirect()->toRoute('order', array('action' => 'checkout'));
            } else {
                $logger = $this
                    ->getServiceLocator()
                    ->get('Logger');
                $logger->warn($form->getMessages());
            }
        }
        
        return new ViewModel(array(
            'form' => $form,
            'order' => $cartContainer->order,
            'paymenttypes' => $pts,
        ));
    }
    
    /*
     * last check and checkout
     */
    public function checkoutAction() {
        $cartContainer = new Container('cart');
        $orderContainer = new Container('order');
        
        $this->checkItemPrices();
        
        $em = $this
                ->getServiceLocator()
                ->get('Doctrine\ORM\EntityManager');
        
        if(isset($orderContainer->order_id)) {
            $order = $em->getRepository("ersEntity\Entity\Order")
                    ->findOneBy(array('id' => $orderContainer->order_id));
            if($order) {
                return $this->redirect()->toRoute(
                        'order', 
                        array(
                            'action' => 'view',
                            'hashkey' => $order->getHashkey(),
                            ));
            }
        }
        
        $form = new Form\Checkout();
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $inputFilter = new InputFilter\Checkout();
            $form->setInputFilter($inputFilter->getInputFilter());
            $form->setData($request->getPost());

            # check if the session cart container has all data to finish this order
            
            $purchaser = $cartContainer->order->getPurchaser();
            $user = $em->getRepository("ersEntity\Entity\User")
                    ->findOneBy(array('email' => $purchaser->getEmail()));
            if($user instanceof Entity\User) {
                $cartContainer->order->setPurchaser($user);
                $cartContainer->order->setPurchaserId($user->getId());
            } else {
                $em->persist($purchaser);
                $cartContainer->order->setPurchaser($purchaser);
                $cartContainer->order->setPurchaserId($purchaser->getId());
            }
            
            $paymenttype = $em->getRepository("ersEntity\Entity\PaymentType")
                    ->findOneBy(array('id' => $cartContainer->order->getPaymentTypeId()));
            $cartContainer->order->setPaymentType($paymenttype);
            $cartContainer->order->setPaymentTypeId($paymenttype->getId());
            
            # get the order_id
            $code = new Entity\Code();
            $code->genCode();
            $codecheck = 1;
            while($codecheck != null) {
                $code->genCode();
                $codecheck = $em->getRepository("ersEntity\Entity\Code")
                    ->findOneBy(array('value' => $code->getValue()));
            }
            $em->persist($code);
            $cartContainer->order->setCode($code);
            $cartContainer->order->setCodeId($code->getId());
            
            $packages = $cartContainer->order->getPackages();
            foreach($packages as $package) {
                if(count($package->getItems()) <= 0) {
                    $cartContainer->order->removePackage($package);
                    continue;
                }
                $package->setOrder($cartContainer->order);
                $package->setOrderId($cartContainer->order->getId());
                
                $participant = $package->getParticipant();
                
                if($participant->getFirstname() == '' || $participant->getSurname() == '') {
                    $participant = $purchaser;
                }
                
                $user = null;
                if($participant->getEmail() == '') {
                    $participant->setEmail(null);
                } else {
                    $user = $em->getRepository("ersEntity\Entity\User")
                        ->findOneBy(array('email' => $participant->getEmail()));
                }
                
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
                
                $country = $em->getRepository("ersEntity\Entity\Country")
                        ->findOneBy(array('id' => $participant->getCountryId()));
                if(!$country) {
                    $participant->setCountry(null);
                } else {
                    $participant->setCountry($country);
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
                    foreach($item->getItemVariants() as $variant) {
                        $variant->setItem($item);
                    }
                    foreach($item->getChildItems() as $subItemPackage) {
                        $subItem = $subItemPackage->getSubItem();
                        $subProduct = $em->getRepository("ersEntity\Entity\Product")
                            ->findOneBy(array('id' => $subItem->getProductId()));
                        error_log('found product id: '.$subItem->getProductId());
                        $subItem->setProduct($subProduct);
                        $subItem->setPackage($package);
                        $code = new Entity\Code();
                        $code->genCode();
                        $codecheck = 1;
                        while($codecheck != null) {
                            $code->genCode();
                            $codecheck = $em->getRepository("ersEntity\Entity\Code")
                                ->findOneBy(array('value' => $code->getValue()));
                        }
                        $subItem->setCode($code);
                        foreach($subItem->getItemVariants() as $variant) {
                            error_log('found variant '.$variant->getName().' with value '.$variant->getValue());
                            $variant->setItem($subItem);
                        }
                    }
                }
                
                $em->persist($package);
            }
            $orderStatus = new Entity\OrderStatus();
            $orderStatus->setOrder($cartContainer->order);
            $orderStatus->setValue('unpaid');
            $em->persist($orderStatus);
            $cartContainer->order->addOrderStatus($orderStatus);
            
            $em->persist($cartContainer->order);
            $em->flush();
        
            $orderContainer->order_id = $cartContainer->order->getId();
            
            $cartContainer->init = 0;
            
            $this->sendConfirmationEmail($cartContainer->order->getId());
            $forrest = new Service\BreadcrumbFactory;
            $forrest->remove('terms');
            switch(strtolower($cartContainer->order->getPaymentType()->getType())) {
                case 'banktransfer':
                    return $this->redirect()->toRoute('payment', 
                            array(
                                'action' => 'banktransfer',
                                'hashkey' => $cartContainer->order->getHashkey(),
                                ));
                case 'cheque':
                    return $this->redirect()->toRoute('payment', 
                            array(
                                'action' => 'cheque',
                                'hashkey' => $cartContainer->order->getHashkey(),
                                ));
                case 'creditcard':
                    return $this->redirect()->toRoute(
                            'payment', 
                            array(
                                'action' => 'creditcard',
                                'hashkey' => $cartContainer->order->getHashkey(),
                                ));
                case 'paypal':
                    break;
                default:
            }
            
        }
        
        $forrest = new Service\BreadcrumbFactory;
        $forrest->set('terms', 'order', array('action' => 'checkout'));
        
        /*
         * Check the purchasers email, if it's not valid, delete purchaser
         */
        $email_validator = new Validator\EmailAddress();
        $purchaser = $cartContainer->order->getPurchaser();
        if($purchaser) {
            $email = $cartContainer->order->getPurchaser()->getEmail();
            if (!$email_validator->isValid($email)) {
                $cartContainer->order->setPurchaser();
            }    
        }

        return new ViewModel(array(
            'form' => $form,
            'order' => $cartContainer->order,
        ));
    }
    
    private function sendConfirmationEmail($order_id) {
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $session_order = new Container('order');
        $order = $em->getRepository("ersEntity\Entity\Order")
                    ->findOneBy(array('id' => $order_id));
        $purchaser = $order->getPurchaser();
        
        $emailService = new Service\EmailFactory();
        $emailService->setFrom('prereg@eja.net');
        
        $emailService->addTo($purchaser);
        #$emailService->setSubject('EJC Registration System: Order Confirmation');
        $subject = "Your registration for EJC 2015 (order ".$order->getCode()->getValue().")";
        $emailService->setSubject($subject);
        
        $viewModel = new ViewModel(array(
            'order' => $order,
        ));
        $viewModel->setTemplate('email/order-confirmation.phtml');
        $viewRender = $this->getServiceLocator()->get('ViewRenderer');
        $html = $viewRender->render($viewModel);
        
        $emailService->setHtmlMessage($html);
        #$emailService->setTextMessage('Testmail');
        
        #$filename = "EJC2015_Terms_and_Services.pdf";
        #$filepath = getcwd().'/tmp/'.$filename;
        $terms1 = getcwd().'/public/Terms-and-Conditions-ERS-EN-v3.pdf';
        $terms2 = getcwd().'/public/Terms-and-Conditions-ORGA-EN-v2.pdf';
        #$emailService->addAttachment($filepath);
        $emailService->addAttachment($terms1);
        $emailService->addAttachment($terms2);
        
        $emailService->send();
        
        $orderStatus = new Entity\OrderStatus();
        $orderStatus->setOrder($order);
        $orderStatus->setValue('confirmation sent');
        $em->persist($orderStatus);
        $em->flush();
        
        return true;
    }
    
    private function genTermsPDF() {
        $pdfView = new ViewModel();
        
        $pdfView->setTemplate('pre-reg/info/terms');
        /*$pdfView->setVariables(array(
            'name' => $name,
            'code' => $code,
            'qrcode' => $base64_qrcode,
            'barcode' => $base64_barcode,
        ));*/
        $pdfRenderer = $this->getServiceLocator()->get('ViewPdfRenderer');
        $html = $pdfRenderer->getHtmlRenderer()->render($pdfView);
        $pdfEngine = $pdfRenderer->getEngine();

        $pdfEngine->load_html($html);
        $pdfEngine->render();
        $pdfContent = $pdfEngine->output();
        
        $filename = "EJC2015_Terms_and_Services.pdf";
        $filepath = getcwd().'/tmp/'.$filename;
        file_put_contents($filepath, $pdfContent);
        
        return $filepath;
    }
    
    /*
     * say thank you after purchaser
     */
    public function thankyouAction() {
        /*$cartContainer = new Container('cart');
        #$cartContainer->getManager()->getStorage()->clear('cart');
        $cartContainer->init = 0;
        return new ViewModel(array(
            'order' => $cartContainer->order,
        ));*/
        
        $hashkey = $this->params()->fromRoute('hashkey', '');
        
        if($hashkey == '') {
            return $this->notFoundAction();
        }
        
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $order = $em->getRepository("ersEntity\Entity\Order")
                ->findOneBy(array('hashkey' => $hashkey));
        
        if($order == null) {
            $logger = $this
                ->getServiceLocator()
                ->get('Logger');
            $logger->info('order for hash key '.$hashkey.' not found');
            return $this->notFoundAction();
        }
        
        $query = $this->params()->fromQuery();
        if(count($query) > 0) {
            $paymentDetail = new Entity\PaymentDetail();
            $paymentDetail->setData(json_encode($query));
            #$paymentDetail->setOrderId($order->getId());
            $paymentDetail->setOrder($order);

            $em->persist($paymentDetail);
            $em->flush();
        }
        
        return new ViewModel(array(
            'order' => $order,
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

    public function ccErrorAction() {
        $hashkey = $this->params()->fromRoute('hashkey', '');
        $params = $this->params()->fromQuery();
        
        if($hashkey == '') {
            return $this->notFoundAction();
        }
        
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $order = $em->getRepository("ersEntity\Entity\Order")
                ->findOneBy(array('hashkey' => $hashkey));
        
        if($order == null) {
            $logger = $this
                ->getServiceLocator()
                ->get('Logger');
            $logger->info('order for hash key '.$hashkey.' not found');
            return $this->notFoundAction();
        }
        
        $query = $this->params()->fromQuery();
        $paymentDetail = new Entity\PaymentDetail();
        $paymentDetail->setData(json_encode($query));
        #$paymentDetail->setOrderId($order->getId());
        $paymentDetail->setOrder($order);
        
        $em->persist($paymentDetail);
        $em->flush();
        
        return new ViewModel(array(
            'order' => $order,
            'params' => $params,
        ));
    }
    
    public function emailAction() {
        $hashkey = $this->params()->fromRoute('hashkey', '');
        
        if($hashkey == '') {
            return $this->notFoundAction();
        }
        
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $order = $em->getRepository("ersEntity\Entity\Order")
                ->findOneBy(array('hashkey' => $hashkey));
        
        if($order == null) {
            $logger = $this
                ->getServiceLocator()
                ->get('Logger');
            $logger->info('order for hash key '.$hashkey.' not found');
            return $this->notFoundAction();
        }
        
        return new ViewModel(array(
            'order' => $order,
        ));
    }
}