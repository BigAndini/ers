<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Admin\Form;
use ErsBase\Service;
use Admin\InputFilter;
use ErsBase\Entity;

class OrderController extends AbstractActionController {
 
    public function indexAction()
    {
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $orders = $entityManager->getRepository('ErsBase\Entity\Order')
                ->findBy(array(), array('created' => 'DESC'));
        
        return new ViewModel(array(
            'orders' => $orders,
        ));
    }
    
    public function searchAction() {
        $logger = $this->getServiceLocator()->get('Logger');
        
        $form = new Form\SearchOrder();
        
        $result = array();         
        /*
         * - use "" (quotes) to search exakt strings with spaces
         * - use - (minus) to exclude a string
         * - use space as and operator
         * - use (a,b,c) as or operator
         */
        $searchText = \trim($this->params()->fromQuery('q'));
        if(!empty($searchText)) {

            $form->get('q')->setValue($searchText);

            $matches = array();
            preg_match('/"[^"]+"/', $searchText, $matches);
            $searchElements = preg_replace('/"/', '', $matches);

            $searchArray = \preg_split('/\ /', $searchText);
            $exclude = false;

            $excludeElements = array();
            foreach($searchArray as $element) {
                if(preg_match('/^"/', $element)) {
                    $exclude = true;
                }
                if(!$exclude) {
                    if(preg_match('/^-/', $element)) {
                        $excludeElements[] = preg_replace('/^-/','',$element);
                    } else {
                        $searchElements[] = $element;
                    }
                }
                if(preg_match('/"$/', $element)) {
                    $exclude = false;
                }
            }

            $entityManager = $this->getServiceLocator()
                ->get('Doctrine\ORM\EntityManager');

            $result = array();

            /*
             * search code
             */
            $queryBuilder = $entityManager->getRepository('ErsBase\Entity\Order')->createQueryBuilder('o');
            $queryBuilder->join('o.code', 'oc');
            $queryBuilder->join('o.packages', 'p');
            $queryBuilder->join('p.code', 'pc');
            $queryBuilder->join('o.status', 's WITH s.active = 1');
            $i = 0;
            foreach($searchElements as $element) {
                if($i == 0) {
                    $queryBuilder->where('oc.value LIKE :param'.$i);
                } else {
                    $queryBuilder->orWhere('oc.value LIKE :param'.$i);
                }
                $queryBuilder->setParameter('param'.$i, $element);
                $i++;

                $code = new Entity\Code();
                $code->setValue($element);
                if($code->checkCode()) {
                    $queryBuilder->orWhere('oc.value LIKE :param'.$i);
                    $queryBuilder->orWhere('pc.value LIKE :param'.$i);
                    $queryBuilder->setParameter('param'.$i, $code->getValue());
                    $i++;
                }

            }

            $check_first = $i;
            foreach($excludeElements as $elemnt) {
                if($i == $check_first) {
                    $queryBuilder->where('oc.value NOT LIKE :param'.$i);
                } else {
                    $queryBuilder->orWhere('oc.value NOT LIKE :param'.$i);
                }
                $queryBuilder->orWhere('pc.value NOT LIKE :param'.$i);
                $queryBuilder->setParameter('param'.$i, $element);
                $i++;
            }

            $result = array_merge($result, $queryBuilder->getQuery()->getResult());

            /*
             * search firstname, surname, email, birthdate
             * of buyer and participant
             */
            $queryBuilder = $entityManager->getRepository('ErsBase\Entity\Order')->createQueryBuilder('o');
            $queryBuilder->join('o.user', 'b'); # get buyer
            $queryBuilder->join('o.packages', 'p');
            $queryBuilder->join('o.status', 's WITH s.active = 1');
            $queryBuilder->join('p.user', 'u'); # get participant
            $i = 0;
            foreach($searchElements as $element) {
                $b_expr = $queryBuilder->expr()->lower($queryBuilder->expr()->concat('b.firstname', $queryBuilder->expr()->concat($queryBuilder->expr()->literal(' '), 'b.surname')));
                $u_expr = $queryBuilder->expr()->lower($queryBuilder->expr()->concat('u.firstname', $queryBuilder->expr()->concat($queryBuilder->expr()->literal(' '), 'u.surname')));
                $be_expr = $queryBuilder->expr()->lower('b.email');
                $ue_expr = $queryBuilder->expr()->lower('u.email');
                if($i == 0) {
                    $queryBuilder->where($queryBuilder->expr()->like($b_expr, ':param'.$i));
                } else {
                    $queryBuilder->orWhere($queryBuilder->expr()->like($b_expr, ':param'.$i));
                }
                $queryBuilder->orWhere($queryBuilder->expr()->like($u_expr, ':param'.$i));
                $queryBuilder->orWhere($queryBuilder->expr()->like($be_expr, ':param'.$i));
                $queryBuilder->orWhere($queryBuilder->expr()->like($ue_expr, ':param'.$i));
                $queryBuilder->setParameter('param'.$i, '%'.strtolower($element).'%');
                $i++;
            }
            $result = array_merge($result, $queryBuilder->getQuery()->getResult());

        } else {
            $logger->warn($form->getMessages());
        }
        
        return new ViewModel(array(
            'form' => $form,
            'result' => $result,
            'searchText' => $searchText,
        ));
    }
    
    public function detailAction()
    {
        $orderId = (int) $this->params()->fromRoute('id', 0);
        if (!$orderId) {
            return $this->redirect()->toRoute('admin/order', array());
        }
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $order = $entityManager->getRepository('ErsBase\Entity\Order')
                ->findOneBy(array('id' => $orderId));
        $paymentDetails = $entityManager->getRepository('ErsBase\Entity\PaymentDetail')
                ->findBy(array('order_id' => $orderId), array('created' => 'DESC'));
        
        $forrest = new Service\BreadcrumbService();
        $forrest->set('order', 'admin/order', array('action' => 'detail', 'id' => $orderId));
        $forrest->set('user', 'admin/order', array('action' => 'detail', 'id' => $orderId));
        $forrest->set('package', 'admin/order', array('action' => 'detail', 'id' => $orderId));
        $forrest->set('item', 'admin/order', array('action' => 'detail', 'id' => $orderId));
        $forrest->set('matching', 'admin/order', array('action' => 'detail', 'id' => $orderId));
        
        return new ViewModel(array(
            'order' => $order,
            'paymentDetails' => $paymentDetails,
            'order_search_form' => new Form\SearchOrder(),
        ));
    }
    public function changePaymentTypeAction() {
        $orderId = (int) $this->params()->fromRoute('id', 0);
        if (!$orderId) {
            return $this->redirect()->toRoute('admin/order', array());
        }
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $order = $entityManager->getRepository('ErsBase\Entity\Order')
                ->findOneBy(array('id' => $orderId));
        
        $paymenttypes = $entityManager->getRepository('ErsBase\Entity\PaymentType')
                ->findBy(array(), array('position' => 'ASC'));
        $currencyOptions = array();
        #$now = new \DateTime();
        
        #$pts = array();
        /*foreach($paymenttypes as $paymenttype) {
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
        }*/
        #$pts = $paymenttypes;
        
        foreach($paymenttypes as $paymenttype) {
            $currencyOptions[] = array(
                'value' => $paymenttype->getId(),
                'label' => $paymenttype->getName(),
            );
        }
        
        $form = new Form\ChangePaymentType();
        $form->get('paymenttype_id')->setValueOptions($currencyOptions);
        
        $forrest = new Service\BreadcrumbService();
        if(!$forrest->exists('order')) {
            $forrest->set('order', 'admin/order');
        }
        
        $logger = $this->getServiceLocator()->get('Logger');
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            #$inputFilter = new InputFilter\PaymentType();
            #$form->setInputFilter($inputFilter->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $data = $form->getData();
                
                $paymenttype = $entityManager->getRepository('ErsBase\Entity\PaymentType')
                        ->findOneBy(array('id' => $data['paymenttype_id']));
                
                $order->setPaymentType($paymenttype);
                
                $entityManager->persist($order);
                $entityManager->flush();
                
                $breadcrumb = $forrest->get('order');
                return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
            } else {
                $logger->warn($form->getMessages());
            }
        }
        
        return new ViewModel(array(
            'form' => $form,
            'order' => $order,
            'paymenttypes' => $paymenttypes,
            'breadcrumb' => $forrest->get('order'),
        ));
    }
    
    public function changeCurrencyAction() {
        $forrest = new Service\BreadcrumbService();
        if(!$forrest->exists('order')) {
            $forrest->set('order', 'admin/order');
        }
        
        $logger = $this->getServiceLocator()->get('Logger');
        
        $orderId = (int) $this->params()->fromRoute('id', 0);
        if (!$orderId) {
            return $this->redirect()->toRoute('admin/order', array());
        }
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $order = $entityManager->getRepository('ErsBase\Entity\Order')
                ->findOneBy(array('id' => $orderId));
        
        # prepare currencies
        $currencies = $entityManager->getRepository('ErsBase\Entity\Currency')
                ->findBy(array(), array('position' => 'ASC'));
        
        $currencyOptions = [];
        foreach($currencies as $currency) {
            $selected = false;
            if($currency->getId() == $order->getCurrency()->getId()) {
                $selected = true;
            }
            $currencyOptions[] = array(
                'value' => $currency->getId(),
                'label' => $currency->getName().' ('.$currency->getShort().' / '.$currency->getSymbol().')',
                'selected' => $selected,
            );
        }
        
        # prepare payment types
        $paymenttypes = $entityManager->getRepository('ErsBase\Entity\PaymentType')
                ->findBy(array(), array('position' => 'ASC'));
        
        $paymenttypeOptions = [];
        foreach($paymenttypes as $paymenttype) {
            $selected = false;
            if($paymenttype->getId() == $order->getPaymenttype()->getId()) {
                $selected = true;
            }
            $disabled = true;
            if($paymenttype->getCurrency()->getShort() == $order->getCurrency()->getShort()) {
                $disabled = false;
            }
            $paymenttypeOptions[] = array(
                'value' => $paymenttype->getId(),
                'label' => $paymenttype->getName(),
                'selected' => $selected,
                'disabled' => $disabled,
            );
        }
        
        $form = new Form\ChangeCurrency();
        $form->get('currency_id')->setValueOptions($currencyOptions);
        $form->get('paymenttype_id')->setValueOptions($paymenttypeOptions);
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $data = $form->getData();
                
                $currency = $entityManager->getRepository('ErsBase\Entity\Currency')
                        ->findOneBy(array('id' => $data['currency_id']));
                
                $paymenttype = $entityManager->getRepository('ErsBase\Entity\PaymentType')
                        ->findOneBy(array('id' => $data['paymenttype_id']));
                
                $deadlineService = $this->getServiceLocator()
                    ->get('ErsBase\Service\DeadlineService:price');
                $deadlineService->setCompareDate($order->getCreated());
                $deadline = $deadlineService->getDeadline();
                
                $orderService = $this->getServiceLocator()
                        ->get('ErsBase\Service\OrderService');
                $orderService->setOrder($order);
                $orderService->changeCurrency($currency);
                #$order->setCurrency($currency);
                $order->setPaymentType($paymenttype);
                
                $agegroupService = $this->getServiceLocator()
                        ->get('ErsBase\Service\AgegroupService:price');
                foreach($order->getPackages() as $package) {
                    $participant = $package->getParticipant();
                    $agegroup = $agegroupService->getAgegroupByDate($participant->getBirthday());
                    
                    $orderService->saveRecalcPackage($package, $agegroup, $deadline);
                }
                
                $entityManager->persist($order);
                $entityManager->flush();
                
                $breadcrumb = $forrest->get('order');
                return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
            } else {
                $logger->warn($form->getMessages());
            }
        }
        
        return new ViewModel(array(
            'form' => $form,
            'order' => $order,
            'currencies' => $currencies,
            'breadcrumb' => $forrest->get('order'),
        ));
    }
    
    public function resendConfirmationAction() {
        #$logger = $this->getServiceLocator()->get('Logger');
        
        $orderId = (int) $this->params()->fromRoute('id', 0);
        if (!$orderId) {
            return $this->redirect()->toRoute('admin/order', array());
        }
        
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $order = $entityManager->getRepository('ErsBase\Entity\Order')
                ->findOneBy(array('id' => $orderId));
        
        $forrest = new Service\BreadcrumbService();
        if(!$forrest->exists('order')) {
            $forrest->set('order', 'admin/order');
        }
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $ret = $request->getPost('del', 'No');

            if ($ret == 'Yes') {
                $orderId = (int) $request->getPost('id');
                
                $order = $entityManager->getRepository('ErsBase\Entity\Order')
                    ->findOneBy(array('id' => $orderId));
                
                $emailService = $this->getServiceLocator()
                        ->get('ErsBase\Service\EmailService');
                
                $emailService->sendConfirmationEmail($order->getId());
                
                $breadcrumb = $forrest->get('order');
                return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
            }
        }
        
        return new ViewModel(array(
            'order' => $order,
            'breadcrumb' => $forrest->get('order'),
        ));
    }
    
    public function sendEticketsAction() {
        $logger = $this->getServiceLocator()->get('Logger');
        
        $orderId = (int) $this->params()->fromRoute('id', 0);
        if (!$orderId) {
            return $this->redirect()->toRoute('admin/order', array());
        }
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $order = $entityManager->getRepository('ErsBase\Entity\Order')
                ->findOneBy(array('id' => $orderId));
        
        $forrest = new Service\BreadcrumbService();
        if(!$forrest->exists('order')) {
            $forrest->set('order', 'admin/order');
        }
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $ret = $request->getPost('del', 'No');

            if ($ret == 'Yes') {
                $orderId = (int) $request->getPost('id');
                
                $order = $entityManager->getRepository('ErsBase\Entity\Order')
                    ->findOneBy(array('id' => $orderId));
                
                if($order->getPaymentStatus() != 'paid') {
                    return $this->redirect()->toRoute('admin/order', array('action' => 'send-eticket'));
                }
                
                $eticketService = $this->getServiceLocator()->get('ErsBase\Service\ETicketService');
                foreach($order->getPackages() as $package) {
                    if($package->getStatus() != 'paid') {
                        continue;
                    }

                    # prepare email (participant, buyer)
                    #$emailService = new Service\EmailService();
                    $emailService = $this->getServiceLocator()
                        ->get('ErsBase\Service\EmailService');
                    $config = $this->getServiceLocator()
                        ->get('config');
                    $emailService->setFrom($config['ERS']['info_mail']);

                    $order = $package->getOrder();
                    $participant = $package->getParticipant();

                    $buyer = $order->getBuyer();
                    $emailService->addTo($buyer);

                    if($participant->getEmail() != '') {
                        $emailService->addTo($participant);
                    }

                    $bcc = new Entity\User();
                    $bcc->setEmail($config['ERS']['info_mail']);
                    $emailService->addBcc($bcc);

                    #$subject = "Your registration for ".$config['ERS']['name_short']." (order ".$order->getCode()->getValue().")";
                    $subject = "[".$config['ERS']['name_short']."] E-Ticket for ".$participant->getFirstname()." ".$participant->getSurname()." (order ".$order->getCode()->getValue().")";
                    $emailService->setSubject($subject);

                    $viewModel = new ViewModel(array(
                        'package' => $package,
                    ));
                    $viewModel->setTemplate('email/eticket-participant.phtml');
                    $viewRender = $this->getServiceLocator()->get('ViewRenderer');
                    $html = $viewRender->render($viewModel);

                    $emailService->setHtmlMessage($html);

                    # generate e-ticket pdf
                    $eticketService = $this->getServiceLocator()
                        ->get('ErsBase\Service\ETicketService');

                    $eticketService->setLanguage('en');
                    $eticketService->setPackage($package);
                    $eticketFile = $eticketService->generatePdf();

                    # send out email
                    $emailService->addAttachment($eticketFile);

                    $emailService->send();
                    $package->setTicketStatus('send_out');
                    $entityManager->persist($package);
                    $entityManager->flush();
                }
                
                
                $breadcrumb = $forrest->get('order');
                return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
            }
        }
        
        return new ViewModel(array(
            'order' => $order,
            'breadcrumb' => $forrest->get('order'),
        ));
    }
    
    public function sendPaymentReminderAction() {
        $logger = $this->getServiceLocator()->get('Logger');
        
        $orderId = (int) $this->params()->fromRoute('id', 0);
        if (!$orderId) {
            return $this->redirect()->toRoute('admin/order', array());
        }
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $order = $entityManager->getRepository('ErsBase\Entity\Order')
                ->findOneBy(array('id' => $orderId));
        
        $forrest = new Service\BreadcrumbService();
        if(!$forrest->exists('order')) {
            $forrest->set('order', 'admin/order');
        }
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $ret = $request->getPost('del', 'No');

            if ($ret == 'Yes') {
                $orderId = (int) $request->getPost('id');
                
                $order = $entityManager->getRepository('ErsBase\Entity\Order')
                    ->findOneBy(array('id' => $orderId));
                
                # prepare email (participant, buyer)
                #$emailService = new Service\EmailService();
                $emailService = $this->getServiceLocator()
                        ->get('ErsBase\Service\EmailService');
                $config = $this->getServiceLocator()
                        ->get('config');
                $emailService->setFrom($config['ERS']['info_mail']);

                $buyer = $order->getBuyer();
                $emailService->addTo($buyer);

                $bcc = new Entity\User();
                $bcc->setEmail($config['ERS']['info_mail']);
                $emailService->addBcc($bcc);

                $subject = "[".$config['ERS']['name_short']."] Payment reminder for your order: ".$order->getCode()->getValue();
                $emailService->setSubject($subject);

                $viewModel = new ViewModel(array(
                    'order' => $order,
                ));
                $viewModel->setTemplate('email/payment-reminder.phtml');
                $viewRender = $this->getServiceLocator()->get('ViewRenderer');
                $html = $viewRender->render($viewModel);

                $emailService->setHtmlMessage($html);

                $emailService->send();
                
                $breadcrumb = $forrest->get('order');
                return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
            }
        }
        
        return new ViewModel(array(
            'order' => $order,
            'breadcrumb' => $forrest->get('order'),
        ));
    }
    
    public function changeBuyerAction() {
        $logger = $this->getServiceLocator()->get('Logger');
        
        $orderId = (int) $this->params()->fromRoute('id', 0);
        if (!$orderId) {
            return $this->redirect()->toRoute('admin/order', array());
        }
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $order = $entityManager->getRepository('ErsBase\Entity\Order')
                ->findOneBy(array('id' => $orderId));
        
        $form = new Form\SearchUser();
        
        $results = [];
        
        $q = trim($this->params()->fromQuery('q'));

        if (!empty($q)) {
            $form->get('q')->setValue($q);

            $entityManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');

            $queryBuilder = $entityManager->createQueryBuilder()
                    ->select('u')
                    ->from('ErsBase\Entity\User', 'u')
                    ->orderBy('u.firstname')
                    ->where('1=1');
            
            /*$queryBuilder = $entityManager->createQueryBuilder()
                    ->select('p')
                    ->from('ErsBase\Entity\Package', 'p')
                    ->join('p.participant', 'u')
                    ->join('p.code', 'pcode')
                    ->join('p.order', 'o')
                    ->join('o.code', 'ocode')
                    ->join('o.buyer', 'b')
                    ->orderBy('u.firstname')
                    ->where('1=1');*/

            if (preg_match('~^\d+$~', $q)) {
                // if the entire query consists of nothing but a number, treat it as a user ID
                $queryBuilder->andWhere('u.id = :id');
                $queryBuilder->setParameter(':id', (int) $q);
            } else {
                $exprUName = $queryBuilder->expr()->concat('u.firstname', $queryBuilder->expr()->concat($queryBuilder->expr()->literal(' '), 'u.surname'));
                //$exprBName = $queryBuilder->expr()->concat('b.firstname', $queryBuilder->expr()->concat($queryBuilder->expr()->literal(' '), 'b.surname'));

                $words = preg_split('~\s+~', $q);
                $i = 0;
                foreach ($words as $word) {
                    try {
                        $wordAsDate = new \DateTime($word);
                    } catch (\Exception $ex) {
                        $wordAsDate = NULL;
                    }

                    $param = ':p' . $i;
                    $paramDate = ':pd' . $i;
                    $queryBuilder->andWhere(
                            $queryBuilder->expr()->orX(
                                    $queryBuilder->expr()->like($exprUName, $param), //
                                    $queryBuilder->expr()->like('u.email', $param), //
                                    //$queryBuilder->expr()->like($exprBName, $param),
                                    #$queryBuilder->expr()->like('pcode.value', $param), //
                                    #$queryBuilder->expr()->like('ocode.value', $param), //
                                    ($wordAsDate ? $queryBuilder->expr()->eq('u.birthday', $paramDate) : '1=0')
                            )
                    );

                    $queryBuilder->setParameter($param, '%' . $word . '%');
                    if($wordAsDate)
                        $queryBuilder->setParameter($paramDate, $wordAsDate);

                    $i++;
                }
            }

            $results = $queryBuilder->getQuery()->getResult();
        }
        
        $forrest = new Service\BreadcrumbService();
        $query = array('q' => $q);
        $forrest->set('order', 'admin/order', 
                array(
                    'action' => 'change-buyer',
                    'id' => $order->getId()
                ), 
                array(
                    'query' => $query,
                    #'fragment' => $fragment,
                )
            );
        
        return new ViewModel(array(
            'form' => $form,
            'order' => $order,
            'results' => $results,
        ));
    }
    
    public function acceptBuyerChangeAction() {
        $logger = $this->getServiceLocator()->get('Logger');
        
        $user_id = (int) $this->params()->fromQuery('user_id', 0);
        $order_id = (int) $this->params()->fromQuery('order_id', 0);
        
        $form = new Form\AcceptBuyerChange();
        
        $entityManager = $this->getServiceLocator()
                ->get('Doctrine\ORM\EntityManager');
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            /*$inputFilter = $this->getServiceLocator()
                    ->get('Admin\InputFilter\AcceptBuyerChange');*/
            #$inputFilter = new InputFilter\AcceptBuyerChange();
            #$form->setInputFilter($inputFilter->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $data = $form->getData();
                $user = $entityManager->getRepository('ErsBase\Entity\User')
                    ->findOneBy(array('id' => $data['user_id']));
                
                $order = $entityManager->getRepository('ErsBase\Entity\Order')
                    ->findOneBy(array('id' => $data['order_id']));
                
                $log = new Entity\Log();
                $log->setUser($this->zfcUserAuthentication()->getIdentity());
                $log->setData('changed buyer for order '.$order->getCode()->getValue().': '.$data['comment']);
                $entityManager->persist($log);
                $entityManager->flush();
                
                $order->setBuyer($user);
                $entityManager->persist($order);
                $entityManager->flush();
                
                return $this->redirect()->toRoute('admin/order', array(
                    'action' => 'detail', 
                    'id' => $order->getId()
                ));
            }
            $logger->warn($form->getMessages());
        }
        
        $user = null;
        if($user_id != 0) {
            $user = $entityManager->getRepository('ErsBase\Entity\User')
                    ->findOneBy(array('id' => $user_id));
        }
        
        $order = null;
        if($order_id != 0) {
            $order = $entityManager->getRepository('ErsBase\Entity\Order')
                    ->findOneBy(array('id' => $order_id));
        }
        
        $form->get('order_id')->setValue($order->getId());
        $form->get('user_id')->setValue($user->getId());
        
        $forrest = new Service\BreadcrumbService();
        if(!$forrest->exists('order')) {
            $forrest->set('order', 'admin/order', 
                    array('action' => 'search')
                );
        }
        
        return new ViewModel(array(
            'form' => $form,
            'user' => $user,
            'order' => $order,
            'breadcrumb' => $forrest->get('order'),
        ));
    }
    
    public function changePackageAction() {
        $orderId = (int) $this->params()->fromRoute('id', 0);
        
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $package = $entityManager->getRepository('ErsBase\Entity\Package')
                ->findOneBy(array('id' => $orderId));
        
        return new ViewModel(array(
            'package' => $package,
        ));
    }
    
    public function changeItemAction() {
        $orderId = (int) $this->params()->fromRoute('id', 0);
        
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $item = $entityManager->getRepository('ErsBase\Entity\Item')
                ->findOneBy(array('id' => $orderId));
        
        return new ViewModel(array(
            'item' => $item,
        ));
    }
    
    public function cancelAction() {
        $orderId = (int) $this->params()->fromRoute('id', 0);
        if (!$orderId) {
            return $this->redirect()->toRoute('admin/order', array());
        }
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $order = $entityManager->getRepository('ErsBase\Entity\Order')
                ->findOneBy(array('id' => $orderId));
        
        $forrest = new Service\BreadcrumbService();
        if(!$forrest->exists('order')) {
            $forrest->set('order', 'admin/order');
        }
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $ret = $request->getPost('del', 'No');

            if ($ret == 'Yes') {
                $orderId = (int) $request->getPost('id');
                
                $order = $entityManager->getRepository('ErsBase\Entity\Order')
                    ->findOneBy(array('id' => $orderId));
                
                /*$status = $entityManager->getRepository('ErsBase\Entity\Status')
                        ->findOneBy(array('value' => 'cancelled'));*/
                $order->setPaymentStatus('cancelled');
                #$order->setStatus($status);
                $entityManager->persist($order);
                
                $statusService = $this->getServiceLocator()
                        ->get('ErsBase\Service\StatusService');
                $statusService->setOrderStatus($order, 'cancelled', false);
                
                /*foreach($order->getItems() as $item) {
                    $item->setStatus($status);
                    $entityManager->persist($item);
                }*/
                
                $entityManager->flush();
                
                $breadcrumb = $forrest->get('order');
                return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
            }
        }
        
        return new ViewModel(array(
            'order' => $order,
            'breadcrumb' => $forrest->get('order'),
        ));
    }
    
    public function paidAction() {
        $orderId = (int) $this->params()->fromRoute('id', 0);
        if (!$orderId) {
            return $this->redirect()->toRoute('admin/order', array());
        }
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $order = $entityManager->getRepository('ErsBase\Entity\Order')
                ->findOneBy(array('id' => $orderId));
        
        $forrest = new Service\BreadcrumbService();
        if(!$forrest->exists('order')) {
            $forrest->set('order', 'admin/order');
        }
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $ret = $request->getPost('del', 'No');

            if ($ret == 'Yes') {
                $orderId = (int) $request->getPost('id');
                
                $order = $entityManager->getRepository('ErsBase\Entity\Order')
                    ->findOneBy(array('id' => $orderId));
                
                $order->setPaymentStatus('paid');
                $entityManager->persist($order);
                
                $statusPaid = $entityManager->getRepository('ErsBase\Entity\Status')
                    ->findOneBy(array('value' => 'paid'));
                
                foreach($order->getItems() as $item) {
                    $item->setStatus($statusPaid);
                    $entityManager->persist($item);
                }
                
                $entityManager->flush();
                
                $breadcrumb = $forrest->get('order');
                return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
            }
        }
        
        return new ViewModel(array(
            'order' => $order,
            'breadcrumb' => $forrest->get('order'),
        ));
    }
    
    public function refundAction() {
        $orderId = (int) $this->params()->fromRoute('id', 0);
        if (!$orderId) {
            return $this->redirect()->toRoute('admin/order', array());
        }
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $order = $entityManager->getRepository('ErsBase\Entity\Order')
                ->findOneBy(array('id' => $orderId));
        
        $forrest = new Service\BreadcrumbService();
        if(!$forrest->exists('order')) {
            $forrest->set('order', 'admin/order');
        }
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $ret = $request->getPost('del', 'No');

            if ($ret == 'Yes') {
                $orderId = (int) $request->getPost('id');
                
                $order = $entityManager->getRepository('ErsBase\Entity\Order')
                    ->findOneBy(array('id' => $orderId));
                
                $status = $entityManager->getRepository('ErsBase\Entity\Status')
                        ->findOneBy(array('value' => 'refund'));
                
                $order->setPaymentStatus('refund');
                $order->setStatus($status);
                $entityManager->persist($order);
                
                foreach($order->getItems() as $item) {
                    $item->setStatus($status);
                    $entityManager->persist($item);
                }
                
                $entityManager->flush();
                
                $breadcrumb = $forrest->get('order');
                return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
            }
        }
        
        return new ViewModel(array(
            'order' => $order,
            'breadcrumb' => $forrest->get('order'),
        ));
    }
    
    public function unpaidAction() {
        $orderId = (int) $this->params()->fromRoute('id', 0);
        if (!$orderId) {
            return $this->redirect()->toRoute('admin/order', array());
        }
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $order = $entityManager->getRepository('ErsBase\Entity\Order')
                ->findOneBy(array('id' => $orderId));
        
        $forrest = new Service\BreadcrumbService();
        if(!$forrest->exists('order')) {
            $forrest->set('order', 'admin/order');
        }
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $ret = $request->getPost('del', 'No');

            if ($ret == 'Yes') {
                $orderId = (int) $request->getPost('id');
                
                $order = $entityManager->getRepository('ErsBase\Entity\Order')
                    ->findOneBy(array('id' => $orderId));
                
                $status = $entityManager->getRepository('ErsBase\Entity\Status')
                        ->findOneBy(array('value' => 'ordered'));
                
                $order->setPaymentStatus('unpaid');
                $order->setStatus($status);
                $entityManager->persist($order);
                
                foreach($order->getItems() as $item) {
                    $item->setStatus($status);
                    $entityManager->persist($item);
                }
                
                $entityManager->flush();
                
                $breadcrumb = $forrest->get('order');
                return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
            }
        }
        
        return new ViewModel(array(
            'order' => $order,
            'breadcrumb' => $forrest->get('order'),
        ));
    }
    
    public function overpaidOrdersAction() {
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $statusOverpaid = $entityManager->getRepository('ErsBase\Entity\Status')
                ->findOneBy(array('value' => 'overpaid'));
        
        $orders = $entityManager->getRepository('ErsBase\Entity\Order')
                ->findBy(array('status_id' => $statusOverpaid->getId()));
        
        return new ViewModel(array(
            'orders' => $orders,
        ));
    }
    
    public function zeroEuroTicketsAction() {
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $repository = $entityManager->getRepository('ErsBase\Entity\Order');

        $queryBuilder = $repository->createQueryBuilder('o')
                ->select('o')
                ->join('o.packages', 'p')
                ->join('p.items', 'i')
                ->where('i.price = 0')
                ->andWhere("i.status != 'zero_ok'")
                ->andWhere('i.Product_id = 1');

        $orders = $queryBuilder->getQuery()->getResult();
        
        return new ViewModel(array(
            'orders' => $orders,
        ));
    }
    
    public function overpaidAction() {
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $orders = $entityManager->getRepository('ErsBase\Entity\Order')
                ->findAll();
        
        $overpaid = [];
        foreach($orders as $order) {
            if($order->getSum() < $order->getStatementAmount()) {
                $overpaid[] = $order;
            }
        }
        
        return new ViewModel(array(
            'orders' => $overpaid,
        ));
    }
    
    /* NOT READY, YET! */
    public function changeOrderDateAction() {
        $logger = $this->getServiceLocator()->get('Logger');
        
        $orderId = (int) $this->params()->fromRoute('id', 0);
        if (!$orderId) {
            return $this->redirect()->toRoute('admin/order', array());
        }
        
        $form = new Form\ChangeOrderDate();
        
        $entityManager = $this->getServiceLocator()
                ->get('Doctrine\ORM\EntityManager');
        
        $order = $entityManager->getRepository('ErsBase\Entity\Order')
                ->findOneBy(array('id' => $orderId));
        
        if(!$order) {
            $this->flashMessenger()->addErrorMessage('Unable to find order with id '.$orderId);
            return $this->redirect()->toRoute('admin');
        }
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $data = $form->getData();
                $deadline = $entityManager->getRepository('ErsBase\Entity\Deadline')
                    ->findOneBy(array('id' => $data['deadline_id']));
                
                return $this->redirect()->toRoute('admin/order', array(
                    'action' => 'detail', 
                    'id' => $order->getId()
                ));
            }
            $logger->warn($form->getMessages());
        }
        
        $forrest = new Service\BreadcrumbService();
        if(!$forrest->exists('order')) {
            $forrest->set('order', 'admin/order', 
                    array('action' => 'search')
                );
        }
        
        return new ViewModel(array(
            'form' => $form,
            'order' => $order,
            'breadcrumb' => $forrest->get('order'),
        ));
    }
    
    public function nowActiveAction() {
        $entityManager = $this->getServiceLocator()
                ->get('Doctrine\ORM\EntityManager');
        
        $queryBuilder = $entityManager->getRepository('ErsBase\Entity\Order')->createQueryBuilder('o');
        $queryBuilder->andWhere($queryBuilder->expr()->gt('o.updated', ':updated'));
        $timeout = new \DateTime;
        $timeout->modify( '-2 hours' );
        $queryBuilder->setParameter('updated', $timeout);
        
        $activeOrders = $queryBuilder->getQuery()->getResult();
    
        return new ViewModel(array(
            'activeOrders' => $activeOrders,
        ));
    }
    
}