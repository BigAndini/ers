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
        #$orderContainer = new Container('order');
        #$orderContainer->getManager()->getStorage()->clear('order');
        
        $forrest = new Service\BreadcrumbService();
        $forrest->reset();
        $forrest->set('product', 'order');
        $forrest->set('participant', 'order');
        $forrest->set('cart', 'order');
        
        #$cartContainer = new Container('ers');
        
        $orderService = $this->getServiceLocator()
                ->get('ErsBase\Service\OrderService');
        $order = $orderService->getOrder();
        
        #$this->checkItemPrices();
        
        $logger = $this->getServiceLocator()->get('Logger');
        /*$logger->info('=== shopping cart start ===');
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
        $logger->info('=== shopping cart end ===');*/
        
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
        
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $deadlineService = $this->getServiceLocator()
                ->get('ErsBase\Service\DeadlineService:price');
        $deadline = $deadlineService->getDeadline();
        
        $agegroupService = $this->getServiceLocator()
                ->get('ErsBase\Service\AgegroupService');
        $agegroups = $entityManager->getRepository('ErsBase\Entity\Agegroup')
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
                $product = $entityManager->getRepository('ErsBase\Entity\Product')
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
        
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $order = $entityManager->getRepository('ErsBase\Entity\Order')
                ->findOneBy(array('hashkey' => $hashkey));
        
        if($order == null) {
            $logger = $this->getServiceLocator()->get('Logger');
            $logger->warn('order for hash key '.$hashkey.' not found');
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
        $container = new Container('ers');
        if(!is_array($container->checkout)) {
            $container->checkout = array();
        }
        $container->checkout['/order/overview'] = 1;
        
        $form = new Form\Register();
        
        $orderService = $this->getServiceLocator()
                ->get('ErsBase\Service\OrderService');
        $order = $orderService->getOrder();
        
        /*if(is_object($order->getBuyer())) {
            $buyer = $order->getBuyer();
        }*/
        
        # even if it's not displayed, this is needed to recognize the possible 
        # values.
        $login_email = '';
        if(count($order->getPackages()) > 0) {
            $users = $order->getParticipants();
            $buyer = array();
            $disabled = false;
            if($this->zfcUserAuthentication()->hasIdentity()) {
                $login_email = $this->zfcUserAuthentication()->getIdentity()->getEmail();
                $disabled = true;
            }
            foreach($users as $participant) {
                $selected = false;
                if($participant->getEmail() == $login_email) {
                    $selected = true;
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
            $entityManager = $this->getServiceLocator()
                ->get('Doctrine\ORM\EntityManager');
            $inputFilter->setEntityManager($entityManager);
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
                
                $entityManager = $this->getServiceLocator()
                    ->get('Doctrine\ORM\EntityManager');
                $entityManager->persist($order);
                $entityManager->flush();
                
                $container->checkout['/order/buyer'] = 1;
                
                return $this->redirect()->toRoute('order', array('action' => 'payment'));
            }
            $logger = $this->getServiceLocator()->get('Logger');
            $logger->warn($form->getMessages());
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
        
        $orderService = $this->getServiceLocator()
                ->get('ErsBase\Service\OrderService');
        $order = $orderService->getOrder();
        
        $form = new Form\PaymentType();
        
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $paymenttypes = $entityManager->getRepository('ErsBase\Entity\PaymentType')
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
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $inputFilter = $this->getServiceLocator()
                    ->get('PreReg\InputFilter\PaymentType');
            $form->setInputFilter($inputFilter->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $data = $form->getData();
                
                $paymenttype = $entityManager->getRepository('ErsBase\Entity\PaymentType')
                        ->findOneBy(array('id' => $data['paymenttype_id']));
                
                if($paymenttype->getCurrency()->getShort() != $order->getCurrency()->getShort()) {
                    throw new \Exception('Unable to set this payment type for this order. Please choose another payment type.');
                }
                
                $order->setPaymentType($paymenttype);
                
                $entityManager->persist($order);
                $entityManager->flush();
                
                $container = new Container('ers');
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
        $logger = $this->getServiceLocator()->get('Logger');
        
        $container = new Container('ers');
        if(!is_array($container->checkout)) {
            $container->checkout = array();
        }
        $container->checkout['/order/checkout'] = 1;
        
        $orderService = $this->getServiceLocator()
                ->get('ErsBase\Service\OrderService');
        $order = $orderService->getOrder();
        
        $entityManager = $this->getServiceLocator()
                ->get('Doctrine\ORM\EntityManager');
        
        $paymenttype = $entityManager->getRepository('ErsBase\Entity\PaymentType')
                        ->findOneBy(array('id' => $order->getPaymentTypeId()));
        $order->setPaymentType($paymenttype);
        
        $form = new Form\Checkout();
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $inputFilter = new InputFilter\Checkout();
            $form->setInputFilter($inputFilter->getInputFilter());
            $form->setData($request->getPost());
            
            $buyer = $order->getBuyer();
            $buyer_role = $entityManager->getRepository('ErsBase\Entity\Role')
                        ->findOneBy(array('roleId' => 'buyer'));
            if(!$buyer_role) {
                throw new \Exception('The role "buyer" is missing in the database, please add a role named buyer.');
            }
            if(!$buyer->hasRole($buyer_role)) {
                $buyer->addRole($buyer_role);
                $entityManager->persist($buyer);
            }
            
            $participant_role = $entityManager->getRepository('ErsBase\Entity\Role')
                        ->findOneBy(array('roleId' => 'participant'));
            
            foreach($order->getPackages() as $package) {
                if(count($package->getItems()) <= 0) {
                    $order->removePackage($package);
                    $entityManager->remove($package);
                    continue;
                }
                
                $participant = $package->getParticipant();
                $participant->setActive(true);
                
                if($participant->getFirstname() == '' || $participant->getSurname() == '') {
                    $participant = $buyer;
                }
                
                $user = null;
                $participant->setEmail(null);
                if($participant->getEmail() != '') {
                    $user = $entityManager->getRepository('ErsBase\Entity\User')
                        ->findOneBy(array('email' => $participant->getEmail()));
                }
                
                if($user instanceof Entity\User) {
                    $package->setParticipant($user);
                    if(!$user->hasRole($participant_role)) {
                        $user->addRole($participant_role);
                        $entityManager->persist($user);
                    }
                } else {
                    $entityManager->persist($participant);
                    $package->setParticipant($participant);
                }
                
                $country = $entityManager->getRepository('ErsBase\Entity\Country')
                        ->findOneBy(array('id' => $participant->getCountryId()));
                
                $participant->setCountry(null);
                if($country) {
                    $participant->setCountry($country);
                }
                
            }
            
            $statusService = $this->getServiceLocator()
                    ->get('ErsBase\Service\StatusService');
            $statusService->setOrderStatus($order, 'ordered', null);
            
            
            /*$orderedStatus = $entityManager->getRepository('ErsBase\Entity\Status')
                    ->findOneBy(array('value' => 'ordered'));
            $order->setStatus($orderedStatus);
            
            foreach($order->getPackages() as $package) {
                $package->setStatus($orderedStatus);
                foreach($package->getItems() as $item) {
                    $item->setStatus($orderedStatus);
                }
            }*/
         
            $order->setTotalSum($order->getTotalSum());
            $order->setOrderSum($order->getOrderSum());
            
            $entityManager->persist($order);
            
            # add log entry
            $log = new Entity\Log();
            $log->setOrder($order);
            $log->setUser($order->getBuyer());
            $log->setData($order->getCode()->getValue().' ordered');
            $entityManager->persist($log);
            
            $logger->info($order->getCode()->getValue().' ordered');
            
            $entityManager->flush();
        
            $container = new Container('ers');
            $container->checkout = array();
            unset($container->order_id);
            $container->init = 0;
            
            $emailService = $this->getServiceLocator()
                ->get('ErsBase\Service\EmailService');
            $emailService->sendConfirmationEmail($order->getId());
            
            $forrest = new Service\BreadcrumbService();
            $forrest->remove('terms');
            switch(strtolower($order->getPaymentType()->getType())) {
                case 'sepa':
                    return $this->redirect()->toRoute('payment', 
                            array(
                                'action' => 'banktransfer',
                                'hashkey' => $order->getHashkey(),
                                ));
                    break;
                case 'ukbt':
                    return $this->redirect()->toRoute('payment', 
                            array(
                                'action' => 'banktransfer',
                                'hashkey' => $order->getHashkey(),
                                ));
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
                    throw new \Exception('We were unable to handle your chosen payment type: '.strtolower($order->getPaymentType()->getType()));
                    break;
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
    
    /*
     * say thank you after buyer
     */
    public function thankyouAction() {
        /*$cartContainer = new Container('ers');
        #$cartContainer->getManager()->getStorage()->clear('ers');
        $cartContainer->init = 0;
        return new ViewModel(array(
            'order' => $order,
        ));*/
        
        $hashkey = $this->params()->fromRoute('hashkey', '');
        
        if($hashkey == '') {
            return $this->notFoundAction();
        }
        
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $order = $entityManager->getRepository('ErsBase\Entity\Order')
                ->findOneBy(array('hashkey' => $hashkey));
        
        if($order == null) {
            $logger = $this->getServiceLocator()->get('Logger');
            $logger->warn('order for hash key '.$hashkey.' not found');
            return $this->notFoundAction();
        }
        
        $query = $this->params()->fromQuery();
        if(count($query) > 0) {
            $paymentDetail = new Entity\PaymentDetail();
            $paymentDetail->setData(json_encode($query));
            #$paymentDetail->setOrderId($order->getId());
            $paymentDetail->setOrder($order);

            $entityManager->persist($paymentDetail);
            $entityManager->flush();
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
        
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $order = $entityManager->getRepository('ErsBase\Entity\Order')
                ->findOneBy(array('hashkey' => $hashkey));
        
        if($order == null) {
            $logger = $this->getServiceLocator()->get('Logger');
            $logger->warn('order for hash key '.$hashkey.' not found');
            return $this->notFoundAction();
        }
        
        $query = $this->params()->fromQuery();
        $paymentDetail = new Entity\PaymentDetail();
        $paymentDetail->setData(json_encode($query));
        #$paymentDetail->setOrderId($order->getId());
        $paymentDetail->setOrder($order);
        
        $entityManager->persist($paymentDetail);
        $entityManager->flush();
        
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
        
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $order = $entityManager->getRepository('ErsBase\Entity\Order')
                ->findOneBy(array('hashkey' => $hashkey));
        
        if($order == null) {
            $logger = $this->getServiceLocator()->get('Logger');
            $logger->warn('order for hash key '.$hashkey.' not found');
            return $this->notFoundAction();
        }
        
        return new ViewModel(array(
            'order' => $order,
        ));
    }
    
    public function checkEticketAction() {
        $entityManager = $this->getServiceLocator()
                    ->get('Doctrine\ORM\EntityManager');
        
        $form = new Form\CheckEticket($entityManager);
        $form->get('submit')->setValue('Check');

        $package = null;
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $data = $form->getData();
                
                $code = strtoupper($data['code']);

                $queryBuilder = $entityManager->getRepository('ErsBase\Entity\Package')->createQueryBuilder('p');
                $queryBuilder->join('p.code', 'c');
                $queryBuilder->where($queryBuilder->expr()->eq('c.value', ':code'));
                $queryBuilder->setParameter('code', $code);
                
                $packages = $queryBuilder->getQuery()->getResult();
                if(count($packages) == 1) {
                    $package = $packages[0];
                } else {
                    $queryBuilder1 = $entityManager->getRepository('ErsBase\Entity\Order')->createQueryBuilder('o');
                    $queryBuilder1->join('o.code', 'c');
                    $queryBuilder1->where($queryBuilder1->expr()->eq('c.value', ':code'));
                    $queryBuilder1->setParameter('code', $code);
                    
                    $orders = $queryBuilder1->getQuery()->getResult();
                    if(count($orders) == 1) {
                        $this->flashMessenger()->addErrorMessage($code . ' is your order code, please provide the code from your e-ticket to check it.');
                    } else {
                        $this->flashMessenger()->addErrorMessage('No e-ticket was found for code ' . $code . '. Please double check that you did not provide your order code.');
                    }
                }
            }
        }
        return new ViewModel(array(
            'form' => $form,
            'package' => $package,
        ));
    }
}