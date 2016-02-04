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
use ErsBase\Service;
use Zend\Session\Container;
use ErsBase\Entity;
use Zend\Validator;

class OrderController extends AbstractActionController {
 
    /*
     * overview of this order
     */
    public function indexAction() {
        $orderContainer = new Container('order');
        $orderContainer->getManager()->getStorage()->clear('order');
        
        $forrest = new Service\BreadcrumbService();
        $forrest->reset();
        $forrest->set('product', 'order');
        $forrest->set('participant', 'order');
        $forrest->set('cart', 'order');
        
        #$cartContainer = new Container('cart');
        
        $orderService = $this->getServiceLocator()
                ->get('ErsBase\Service\OrderService');
        $order = $orderService->getOrder();
        
        #$this->checkItemPrices();
        
        $logger = $this->getServiceLocator()->get('Logger');
        $logger->info('=== shopping cart start ===');
        foreach($order->getPackages() as $package) {
            $participant = $package->getParticipant();
            $logger->info('participant: '.$participant->getFirstname().' '.$participant->getSurname());
            $logger->info('has '.count($package->getItems()).' items:');
            foreach($package->getItems() as $item) {
                $logger->info(' - '.$item->getName().' '.$item->getPrice());
                foreach($item->getItemVariants() as $variant) {
                    $logger->info('   - '.$variant->getName().': '.$variant->getValue());
                }
                $logger->info('  has '.count($item->getChildItems()).' sub items:');
                foreach($item->getChildItems() as $subItem) {
                    $logger->info('   - '.$subItem->getName());
                    foreach($subItem->getItemVariants() as $subVariant) {
                        $logger->info('     - '.$subVariant->getName().': '.$subVariant->getValue());
                    }
                }
            }
        }
        $logger->info('=== shopping cart end ===');
        
        $agegroupService = $this->getServiceLocator()
                ->get('ErsBase\Service\AgegroupService');
        
        $order->logInfo();
        return new ViewModel(array(
            'order' => $order,
            'agegroupService' => $agegroupService,
        ));
    }
    public function overviewAction() {
        $view = $this->indexAction();
        $view->setTemplate('pre-reg/order/index.phtml');
        return $view;
    }
    
    /*private function checkItemPrices() {
        $orderService = $this->getServiceLocator()
                ->get('ErsBase\Service\OrderService');
        $order = $orderService->getOrder();
        
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $deadlineService = $this->getServiceLocator()
                ->get('ErsBase\Service\DeadlineService:price');
        $deadline = $deadlineService->getDeadline();
        
        $agegroupService = $this->getServiceLocator()
                ->get('ErsBase\Service\AgegroupService');
        $agegroups = $em->getRepository('ErsBase\Entity\Agegroup')
                    ->findBy(array('price_change' => '1'));
        $agegroupService->setAgegroups($agegroups);
        foreach($order->getPackages() as $package) {
            $participant = $package->getParticipant();
            if($participant == null) {
                continue;
            }
            if($participant->getId() == 0) {
                continue;
            }
            $agegroup = $agegroupService->getAgegroupByUser($participant);
            foreach($package->getItems() as $item) {
                $product = $em->getRepository('ErsBase\Entity\Product')
                    ->findOneBy(array('id' => $item->getProductId()));
                if($product != null) {
                    $productPrice = $product->getProductPrice($agegroup, $deadline);
                    if($item->getPrice() != $productPrice->getCharge()) {
                        $item->setPrice($productPrice->getCharge());
                    }
                }
                
            }
        }
    }*/
    
    /**
     * Action that allows viewing an order by the hash key
     */
    public function viewAction() {
        $hashkey = $this->params()->fromRoute('hashkey', '');
        
        if($hashkey == '') {
            return $this->notFoundAction();
        }
        
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $order = $em->getRepository('ErsBase\Entity\Order')
                ->findOneBy(array('hashkey' => $hashkey));
        
        if($order == null) {
            $logger = $this->getServiceLocator()->get('Logger');
            $logger->info('order for hash key '.$hashkey.' not found');
            return $this->notFoundAction();
        }
        
        return new ViewModel(array(
            'order' => $order,
        ));
    }
    
    /*
     * collect data for the buyer
     */
    public function buyerAction() {
        $container = new Container('initialized');
        if(!is_array($container->checkout)) {
            $container->checkout = array();
        }
        $container->checkout['/order/overview'] = 1;
        
        $orderContainer = new Container('order');
        $orderContainer->getManager()->getStorage()->clear('order');
        
        $form = new Form\Register();
        
        $orderService = $this->getServiceLocator()
                ->get('ErsBase\Service\OrderService');
        $order = $orderService->getOrder();
        
        if(is_object($order->getBuyer())) {
            $buyer = $order->getBuyer();
        }
        
        # even if it's not displayed, this is needed to recognize the possible 
        # values.
        if(count($order->getPackages()) > 0) {
            $users = $order->getParticipants();
            $buyer = array();
            if($this->zfcUserAuthentication()->hasIdentity()) {
                $login_email = $this->zfcUserAuthentication()->getIdentity()->getEmail();
                $disabled = true;
            } else {
                $login_email = '';
                $disabled = false;
            }
            foreach($users as $participant) {
                if($participant->getEmail() == $login_email) {
                    $selected = true;
                } else {
                    $selected = false;
                }
                $buyer[] = array(
                    'value' => $participant->getId(),
                    'label' => $participant->getFirstname().' '.$participant->getSurname().' ('.$participant->getEmail().')',
                    'selected' => $selected,
                );
            }
            $buyer[] = array(
                'value' => 0,
                'label' => 'Add buyer',
            );
            $form->get('buyer_id')->setValueOptions($buyer);
        }
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $inputFilter = $this->getServiceLocator()
                    ->get('PreReg\InputFilter\Register');
            $em = $this->getServiceLocator()
                ->get('Doctrine\ORM\EntityManager');
            $inputFilter->setEntityManager($em);
            if($this->zfcUserAuthentication()->hasIdentity()) {
                $inputFilter->setLoginEmail($this->zfcUserAuthentication()->getIdentity()->getEmail());
            }
            $form->setInputFilter($inputFilter->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $data = $form->getData();
                
                
                $buyer = $order->getParticipantById($data['buyer_id']);
                        
                # add purchser to order
                $order->setBuyer($buyer);
                
                $em = $this->getServiceLocator()
                    ->get('Doctrine\ORM\EntityManager');
                $em->persist($order);
                $em->flush();
                
                $container->checkout['/order/buyer'] = 1;
                
                return $this->redirect()->toRoute('order', array('action' => 'payment'));
            } else {
                $logger = $this->getServiceLocator()->get('Logger');
                $logger->warn($form->getMessages());
            }
        }
       
        $forrest = new Service\BreadcrumbService();
        $forrest->set('participant', 'order', array('action' => 'buyer'));
        $forrest->set('buyer', 'order', array('action' => 'buyer'));
        
        $participants = $order->getParticipants();
        
        return new ViewModel(array(
            'form' => $form,
            'order' => $order,
            'participants' => $participants,
            'login_email' => $login_email,
        ));
    }
    
    /*
     * collect payment data and complete purchase
     */
    public function paymentAction() {
        $forrest = new Service\BreadcrumbService();
        $forrest->set('paymenttype', 'order', array('action' => 'payment'));
        
        $orderContainer = new Container('order');
        $orderContainer->getManager()->getStorage()->clear('order');
        
        $orderService = $this->getServiceLocator()
                ->get('ErsBase\Service\OrderService');
        $order = $orderService->getOrder();
        
        $form = new Form\PaymentType();
        
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $paymenttypes = $em->getRepository('ErsBase\Entity\PaymentType')
                ->findBy(array(), array('position' => 'ASC'));
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
        
        #$cartContainer = new Container('cart');
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $inputFilter = new InputFilter\PaymentType();
            $inputFilter = $this->getServiceLocator()
                    ->get('PreReg\InputFilter\PaymentType');
            $form->setInputFilter($inputFilter->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $data = $form->getData();
                
                $paymenttype = $em->getRepository('ErsBase\Entity\PaymentType')
                        ->findOneBy(array('id' => $data['paymenttype_id']));
                
                $order->setPaymentType($paymenttype);
                
                $em->persist($order);
                $em->flush();
                
                $container = new Container('initialized');
                if(!is_array($container->checkout)) {
                    $container->checkout = array();
                }
                $container->checkout['/order/payment'] = 1;
                
                return $this->redirect()->toRoute('order', array('action' => 'checkout'));
            } else {
                $logger = $this->getServiceLocator()->get('Logger');
                $logger->warn($form->getMessages());
            }
        }
        
        return new ViewModel(array(
            'form' => $form,
            'order' => $order,
            'paymenttypes' => $paymenttypes,
        ));
    }
    
    /*
     * last check and checkout
     */
    public function checkoutAction() {
        $container = new Container('initialized');
        if(!is_array($container->checkout)) {
            $container->checkout = array();
        }
        $container->checkout['/order/checkout'] = 1;
                
        $cartContainer = new Container('cart');
        $orderContainer = new Container('order');
        
        $orderService = $this->getServiceLocator()
                ->get('ErsBase\Service\OrderService');
        $order = $orderService->getOrder();
        
        #$this->checkItemPrices();
        
        $em = $this->getServiceLocator()
                ->get('Doctrine\ORM\EntityManager');
        
        $paymenttype = $em->getRepository('ErsBase\Entity\PaymentType')
                        ->findOneBy(array('id' => $order->getPaymentTypeId()));
        $order->setPaymentType($paymenttype);
        
        if(isset($orderContainer->order_id)) {
            $order = $em->getRepository('ErsBase\Entity\Order')
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
            
            foreach($order->getPackages() as $package) {
                if(count($package->getItems()) <= 0) {
                    $order->removePackage($package);
                    $em->remove($package);
                    continue;
                }
                
                $participant = $package->getParticipant();
                
                if($participant->getFirstname() == '' || $participant->getSurname() == '') {
                    $participant = $buyer;
                }
                
                $user = null;
                if($participant->getEmail() == '') {
                    $participant->setEmail(null);
                } else {
                    $user = $em->getRepository('ErsBase\Entity\User')
                        ->findOneBy(array('email' => $participant->getEmail()));
                }
                
                $role = $em->getRepository('ErsBase\Entity\Role')
                        ->findOneBy(array('roleId' => 'participant'));
                if($user instanceof Entity\User) {
                    $package->setParticipant($user);
                    if(!$user->hasRole($role)) {
                        $user->addRole($role);
                        $em->persist($user);
                    }
                    #$package->setParticipantId($user->getId());
                    
                } else {
                    $em->persist($participant);
                    $package->setParticipant($participant);
                    #$package->setParticipantId($participant->getId());
                }
                
                $country = $em->getRepository('ErsBase\Entity\Country')
                        ->findOneBy(array('id' => $participant->getCountryId()));
                if(!$country) {
                    $participant->setCountry(null);
                } else {
                    $participant->setCountry($country);
                }
                
            }
            
            $status = $em->getRepository('ErsBase\Entity\Status')
                    ->findOneBy(array('value' => 'ordered'));
            $order->setStatus($status);
            
            $em->persist($order);
            
            # add log entry
            $log = new Entity\Log();
            $log->setOrder($order);
            $log->setUser($order->getBuyer());
            $log->setData($order->getCode()->getValue().' ordered');
            $em->persist($log);
            
            $em->flush();
        
            $orderContainer->order_id = $order->getId();
            
            $cartContainer->init = 0;
            
            $container = new Container('initialized');
            $container->checkout = array();
            
            $emailService = $this->getServiceLocator()
                ->get('ErsBase\Service\EmailService');
            $emailService->sendConfirmationEmail($order->getId());
            
            $forrest = new Service\BreadcrumbService();
            $forrest->remove('terms');
            switch(strtolower($order->getPaymentType()->getType())) {
                case 'banktransfer':
                    return $this->redirect()->toRoute('payment', 
                            array(
                                'action' => 'banktransfer',
                                'hashkey' => $order->getHashkey(),
                                ));
                case 'cheque':
                    return $this->redirect()->toRoute('payment', 
                            array(
                                'action' => 'cheque',
                                'hashkey' => $order->getHashkey(),
                                ));
                case 'creditcard':
                    return $this->redirect()->toRoute(
                            'payment', 
                            array(
                                'action' => 'creditcard',
                                'hashkey' => $order->getHashkey(),
                                ));
                case 'ipayment':
                    return $this->redirect()->toRoute(
                            'payment', 
                            array(
                                'action' => 'ipayment',
                                'hashkey' => $order->getHashkey(),
                                ));
                case 'paypal':
                    return $this->redirect()->toRoute(
                            'payment', 
                            array(
                                'action' => 'paypal',
                                'hashkey' => $order->getHashkey(),
                                ));
                    break;
                default:
            }
            
        }
        
        $forrest = new Service\BreadcrumbService();
        $forrest->set('terms', 'order', array('action' => 'checkout'));
        
        /*
         * Check the buyers email, if it's not valid, delete buyer
         */
        $email_validator = new Validator\EmailAddress();
        $buyer = $order->getBuyer();
        if($buyer) {
            $email = $order->getBuyer()->getEmail();
            if (!$email_validator->isValid($email)) {
                $order->setBuyer();
            }    
        }

        return new ViewModel(array(
            'form' => $form,
            'order' => $order,
        ));
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
     * say thank you after buyer
     */
    public function thankyouAction() {
        /*$cartContainer = new Container('cart');
        #$cartContainer->getManager()->getStorage()->clear('cart');
        $cartContainer->init = 0;
        return new ViewModel(array(
            'order' => $order,
        ));*/
        
        $hashkey = $this->params()->fromRoute('hashkey', '');
        
        if($hashkey == '') {
            return $this->notFoundAction();
        }
        
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $order = $em->getRepository('ErsBase\Entity\Order')
                ->findOneBy(array('hashkey' => $hashkey));
        
        if($order == null) {
            $logger = $this->getServiceLocator()->get('Logger');
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
        
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $order = $em->getRepository('ErsBase\Entity\Order')
                ->findOneBy(array('hashkey' => $hashkey));
        
        if($order == null) {
            $logger = $this->getServiceLocator()->get('Logger');
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
        
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $order = $em->getRepository('ErsBase\Entity\Order')
                ->findOneBy(array('hashkey' => $hashkey));
        
        if($order == null) {
            $logger = $this->getServiceLocator()->get('Logger');
            $logger->info('order for hash key '.$hashkey.' not found');
            return $this->notFoundAction();
        }
        
        return new ViewModel(array(
            'order' => $order,
        ));
    }
}