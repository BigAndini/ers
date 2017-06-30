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
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $orders = $em->getRepository('ErsBase\Entity\Order')
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

            $searchString = array(

            );

            $em = $this->getServiceLocator()
                ->get('Doctrine\ORM\EntityManager');

            $result = array();

            /*
             * search code
             */
            $qb = $em->getRepository('ErsBase\Entity\Order')->createQueryBuilder('o');
            $qb->join('o.code', 'oc');
            $qb->join('o.packages', 'p');
            $qb->join('p.code', 'pc');
            $qb->join('o.status', 's WITH s.active = 1');
            $i = 0;
            foreach($searchElements as $element) {
                if($i == 0) {
                    $qb->where('oc.value LIKE :param'.$i);
                } else {
                    $qb->orWhere('oc.value LIKE :param'.$i);
                }
                $qb->setParameter('param'.$i, $element);
                $i++;

                $code = new Entity\Code();
                $code->setValue($element);
                if($code->checkCode()) {
                    $qb->orWhere('oc.value LIKE :param'.$i);
                    $qb->orWhere('pc.value LIKE :param'.$i);
                    $qb->setParameter('param'.$i, $code->getValue());
                    $i++;
                }

            }

            $check_first = $i;
            foreach($excludeElements as $elemnt) {
                if($i == $check_first) {
                    $qb->where('oc.value NOT LIKE :param'.$i);
                } else {
                    $qb->orWhere('oc.value NOT LIKE :param'.$i);
                }
                $qb->orWhere('pc.value NOT LIKE :param'.$i);
                $qb->setParameter('param'.$i, $element);
                $i++;
            }

            $result = array_merge($result, $qb->getQuery()->getResult());

            /*
             * search firstname, surname, email, birthdate
             * of buyer and participant
             */
            $qb = $em->getRepository('ErsBase\Entity\Order')->createQueryBuilder('o');
            $qb->join('o.user', 'b'); # get buyer
            $qb->join('o.packages', 'p');
            $qb->join('o.status', 's WITH s.active = 1');
            $qb->join('p.user', 'u'); # get participant
            $i = 0;
            foreach($searchElements as $element) {
                $b_expr = $qb->expr()->lower($qb->expr()->concat('b.firstname', $qb->expr()->concat($qb->expr()->literal(' '), 'b.surname')));
                $u_expr = $qb->expr()->lower($qb->expr()->concat('u.firstname', $qb->expr()->concat($qb->expr()->literal(' '), 'u.surname')));
                $be_expr = $qb->expr()->lower('b.email');
                $ue_expr = $qb->expr()->lower('u.email');
                if($i == 0) {
                    $qb->where($qb->expr()->like($b_expr, ':param'.$i));
                } else {
                    $qb->orWhere($qb->expr()->like($b_expr, ':param'.$i));
                }
                $qb->orWhere($qb->expr()->like($u_expr, ':param'.$i));
                $qb->orWhere($qb->expr()->like($be_expr, ':param'.$i));
                $qb->orWhere($qb->expr()->like($ue_expr, ':param'.$i));
                $qb->setParameter('param'.$i, '%'.strtolower($element).'%');
                $i++;
            }
            $result = array_merge($result, $qb->getQuery()->getResult());

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
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/order', array());
        }
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $order = $em->getRepository('ErsBase\Entity\Order')
                ->findOneBy(array('id' => $id));
        $paymentDetails = $em->getRepository('ErsBase\Entity\PaymentDetail')
                ->findBy(array('order_id' => $id), array('created' => 'DESC'));
        
        $forrest = new Service\BreadcrumbService();
        $forrest->set('order', 'admin/order', array('action' => 'detail', 'id' => $id));
        $forrest->set('user', 'admin/order', array('action' => 'detail', 'id' => $id));
        $forrest->set('package', 'admin/order', array('action' => 'detail', 'id' => $id));
        $forrest->set('item', 'admin/order', array('action' => 'detail', 'id' => $id));
        $forrest->set('matching', 'admin/order', array('action' => 'detail', 'id' => $id));
        
        return new ViewModel(array(
            'order' => $order,
            'paymentDetails' => $paymentDetails,
            'order_search_form' => new Form\SearchOrder(),
        ));
    }
    public function changePaymentTypeAction() {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/order', array());
        }
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $order = $em->getRepository('ErsBase\Entity\Order')
                ->findOneBy(array('id' => $id));
        
        $paymenttypes = $em->getRepository('ErsBase\Entity\PaymentType')
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
            $inputFilter = new InputFilter\PaymentType();
            #$form->setInputFilter($inputFilter->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $data = $form->getData();
                
                $paymenttype = $em->getRepository('ErsBase\Entity\PaymentType')
                        ->findOneBy(array('id' => $data['paymenttype_id']));
                
                $order->setPaymentType($paymenttype);
                
                $em->persist($order);
                $em->flush();
                
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
        
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/order', array());
        }
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $order = $em->getRepository('ErsBase\Entity\Order')
                ->findOneBy(array('id' => $id));
        
        # prepare currencies
        $currencies = $em->getRepository('ErsBase\Entity\Currency')
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
        $paymenttypes = $em->getRepository('ErsBase\Entity\PaymentType')
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
                
                $currency = $em->getRepository('ErsBase\Entity\Currency')
                        ->findOneBy(array('id' => $data['currency_id']));
                
                $paymenttype = $em->getRepository('ErsBase\Entity\PaymentType')
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
                
                $em->persist($order);
                $em->flush();
                
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
        $logger = $this->getServiceLocator()->get('Logger');
        
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/order', array());
        }
        
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $order = $em->getRepository('ErsBase\Entity\Order')
                ->findOneBy(array('id' => $id));
        
        $forrest = new Service\BreadcrumbService();
        if(!$forrest->exists('order')) {
            $forrest->set('order', 'admin/order');
        }
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $ret = $request->getPost('del', 'No');

            if ($ret == 'Yes') {
                $id = (int) $request->getPost('id');
                
                $order = $em->getRepository('ErsBase\Entity\Order')
                    ->findOneBy(array('id' => $id));
                
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
        
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/order', array());
        }
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $order = $em->getRepository('ErsBase\Entity\Order')
                ->findOneBy(array('id' => $id));
        
        $forrest = new Service\BreadcrumbService();
        if(!$forrest->exists('order')) {
            $forrest->set('order', 'admin/order');
        }
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $ret = $request->getPost('del', 'No');

            if ($ret == 'Yes') {
                $id = (int) $request->getPost('id');
                
                $order = $em->getRepository('ErsBase\Entity\Order')
                    ->findOneBy(array('id' => $id));
                
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
                    $em->persist($package);
                    $em->flush();
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
        
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/order', array());
        }
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $order = $em->getRepository('ErsBase\Entity\Order')
                ->findOneBy(array('id' => $id));
        
        $forrest = new Service\BreadcrumbService();
        if(!$forrest->exists('order')) {
            $forrest->set('order', 'admin/order');
        }
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $ret = $request->getPost('del', 'No');

            if ($ret == 'Yes') {
                $id = (int) $request->getPost('id');
                
                $order = $em->getRepository('ErsBase\Entity\Order')
                    ->findOneBy(array('id' => $id));
                
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
        
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/order', array());
        }
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $order = $em->getRepository('ErsBase\Entity\Order')
                ->findOneBy(array('id' => $id));
        
        $form = new Form\SearchUser();
        
        $results = [];
        
        $q = trim($this->params()->fromQuery('q'));

        if (!empty($q)) {
            $form->get('q')->setValue($q);

            $em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');

            $qb = $em->createQueryBuilder()
                    ->select('u')
                    ->from('ErsBase\Entity\User', 'u')
                    ->orderBy('u.firstname')
                    ->where('1=1');
            
            /*$qb = $em->createQueryBuilder()
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
                $qb->andWhere('u.id = :id');
                $qb->setParameter(':id', (int) $q);
            } else {
                $exprUName = $qb->expr()->concat('u.firstname', $qb->expr()->concat($qb->expr()->literal(' '), 'u.surname'));
                //$exprBName = $qb->expr()->concat('b.firstname', $qb->expr()->concat($qb->expr()->literal(' '), 'b.surname'));

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
                    $qb->andWhere(
                            $qb->expr()->orX(
                                    $qb->expr()->like($exprUName, $param), //
                                    $qb->expr()->like('u.email', $param), //
                                    //$qb->expr()->like($exprBName, $param),
                                    #$qb->expr()->like('pcode.value', $param), //
                                    #$qb->expr()->like('ocode.value', $param), //
                                    ($wordAsDate ? $qb->expr()->eq('u.birthday', $paramDate) : '1=0')
                            )
                    );

                    $qb->setParameter($param, '%' . $word . '%');
                    if($wordAsDate)
                        $qb->setParameter($paramDate, $wordAsDate);

                    $i++;
                }
            }

            $results = $qb->getQuery()->getResult();
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
        
        $em = $this->getServiceLocator()
                ->get('Doctrine\ORM\EntityManager');
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $inputFilter = $this->getServiceLocator()
                    ->get('Admin\InputFilter\AcceptBuyerChange');
            #$inputFilter = new InputFilter\AcceptBuyerChange();
            #$form->setInputFilter($inputFilter->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $data = $form->getData();
                $user = $em->getRepository('ErsBase\Entity\User')
                    ->findOneBy(array('id' => $data['user_id']));
                
                $order = $em->getRepository('ErsBase\Entity\Order')
                    ->findOneBy(array('id' => $data['order_id']));
                
                $log = new Entity\Log();
                $log->setUser($this->zfcUserAuthentication()->getIdentity());
                $log->setData('changed buyer for order '.$order->getCode()->getValue().': '.$data['comment']);
                $em->persist($log);
                $em->flush();
                
                $order->setBuyer($user);
                $em->persist($order);
                $em->flush();
                
                return $this->redirect()->toRoute('admin/order', array(
                    'action' => 'detail', 
                    'id' => $order->getId()
                ));
            } else {
                $logger->warn($form->getMessages());
            }
        }
        
        $user = null;
        if($user_id != 0) {
            $user = $em->getRepository('ErsBase\Entity\User')
                    ->findOneBy(array('id' => $user_id));
        }
        
        $order = null;
        if($order_id != 0) {
            $order = $em->getRepository('ErsBase\Entity\Order')
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
        $id = (int) $this->params()->fromRoute('id', 0);
        
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $package = $em->getRepository('ErsBase\Entity\Package')
                ->findOneBy(array('id' => $id));
        
        return new ViewModel(array(
            'package' => $package,
        ));
    }
    
    public function changeItemAction() {
        $id = (int) $this->params()->fromRoute('id', 0);
        
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $item = $em->getRepository('ErsBase\Entity\Item')
                ->findOneBy(array('id' => $id));
        
        return new ViewModel(array(
            'item' => $item,
        ));
    }
    
    public function cancelAction() {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/order', array());
        }
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $order = $em->getRepository('ErsBase\Entity\Order')
                ->findOneBy(array('id' => $id));
        
        $forrest = new Service\BreadcrumbService();
        if(!$forrest->exists('order')) {
            $forrest->set('order', 'admin/order');
        }
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $ret = $request->getPost('del', 'No');

            if ($ret == 'Yes') {
                $id = (int) $request->getPost('id');
                
                $order = $em->getRepository('ErsBase\Entity\Order')
                    ->findOneBy(array('id' => $id));
                
                /*$status = $em->getRepository('ErsBase\Entity\Status')
                        ->findOneBy(array('value' => 'cancelled'));*/
                $order->setPaymentStatus('cancelled');
                #$order->setStatus($status);
                $em->persist($order);
                
                $statusService = $this->getServiceLocator()
                        ->get('ErsBase\Service\StatusService');
                $statusService->setOrderStatus($order, 'cancelled', false);
                
                /*foreach($order->getItems() as $item) {
                    $item->setStatus($status);
                    $em->persist($item);
                }*/
                
                $em->flush();
                
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
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/order', array());
        }
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $order = $em->getRepository('ErsBase\Entity\Order')
                ->findOneBy(array('id' => $id));
        
        $forrest = new Service\BreadcrumbService();
        if(!$forrest->exists('order')) {
            $forrest->set('order', 'admin/order');
        }
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $ret = $request->getPost('del', 'No');

            if ($ret == 'Yes') {
                $id = (int) $request->getPost('id');
                
                $order = $em->getRepository('ErsBase\Entity\Order')
                    ->findOneBy(array('id' => $id));
                
                $order->setPaymentStatus('paid');
                $em->persist($order);
                
                $statusPaid = $em->getRepository('ErsBase\Entity\Status')
                    ->findOneBy(array('value' => 'paid'));
                
                foreach($order->getItems() as $item) {
                    $item->setStatus($statusPaid);
                    $em->persist($item);
                }
                
                $em->flush();
                
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
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/order', array());
        }
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $order = $em->getRepository('ErsBase\Entity\Order')
                ->findOneBy(array('id' => $id));
        
        $forrest = new Service\BreadcrumbService();
        if(!$forrest->exists('order')) {
            $forrest->set('order', 'admin/order');
        }
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $ret = $request->getPost('del', 'No');

            if ($ret == 'Yes') {
                $id = (int) $request->getPost('id');
                
                $order = $em->getRepository('ErsBase\Entity\Order')
                    ->findOneBy(array('id' => $id));
                
                $status = $em->getRepository('ErsBase\Entity\Status')
                        ->findOneBy(array('value' => 'refund'));
                
                $order->setPaymentStatus('refund');
                $order->setStatus($status);
                $em->persist($order);
                
                foreach($order->getItems() as $item) {
                    $item->setStatus($status);
                    $em->persist($item);
                }
                
                $em->flush();
                
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
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/order', array());
        }
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $order = $em->getRepository('ErsBase\Entity\Order')
                ->findOneBy(array('id' => $id));
        
        $forrest = new Service\BreadcrumbService();
        if(!$forrest->exists('order')) {
            $forrest->set('order', 'admin/order');
        }
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $ret = $request->getPost('del', 'No');

            if ($ret == 'Yes') {
                $id = (int) $request->getPost('id');
                
                $order = $em->getRepository('ErsBase\Entity\Order')
                    ->findOneBy(array('id' => $id));
                
                $status = $em->getRepository('ErsBase\Entity\Status')
                        ->findOneBy(array('value' => 'ordered'));
                
                $order->setPaymentStatus('unpaid');
                $order->setStatus($status);
                $em->persist($order);
                
                foreach($order->getItems() as $item) {
                    $item->setStatus($status);
                    $em->persist($item);
                }
                
                $em->flush();
                
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
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $statusOverpaid = $em->getRepository('ErsBase\Entity\Status')
                ->findOneBy(array('value' => 'overpaid'));
        
        $orders = $em->getRepository('ErsBase\Entity\Order')
                ->findBy(array('status_id' => $statusOverpaid->getId()));
        
        return new ViewModel(array(
            'orders' => $orders,
        ));
    }
    
    public function zeroEuroTicketsAction() {
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $repository = $em->getRepository('ErsBase\Entity\Order');

        $qb = $repository->createQueryBuilder('o')
                ->select('o')
                ->join('o.packages', 'p')
                ->join('p.items', 'i')
                ->where('i.price = 0')
                ->andWhere("i.status != 'zero_ok'")
                ->andWhere('i.Product_id = 1');

        $orders = $qb->getQuery()->getResult();
        
        return new ViewModel(array(
            'orders' => $orders,
        ));
    }
    
    public function overpaidAction() {
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $orders = $em->getRepository('ErsBase\Entity\Order')
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
        
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/order', array());
        }
        
        $form = new Form\ChangeOrderDate();
        
        $em = $this->getServiceLocator()
                ->get('Doctrine\ORM\EntityManager');
        
        $order = $em->getRepository('ErsBase\Entity\Order')
                ->findOneBy(array('id' => $id));
        
        if(!$order) {
            $this->flashMessenger()->addErrorMessage('Unable to find order with id '.$id);
            return $this->redirect()->toRoute('admin');
        }
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $data = $form->getData();
                $deadline = $em->getRepository('ErsBase\Entity\Deadline')
                    ->findOneBy(array('id' => $data['deadline_id']));
                
                return $this->redirect()->toRoute('admin/order', array(
                    'action' => 'detail', 
                    'id' => $order->getId()
                ));
            } else {
                $logger->warn($form->getMessages());
            }
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
        $em = $this->getServiceLocator()
                ->get('Doctrine\ORM\EntityManager');
        
        $qb = $em->getRepository('ErsBase\Entity\Order')->createQueryBuilder('o');
        $qb->andWhere($qb->expr()->gt('o.updated', ':updated'));
        $timeout = new \DateTime;
        $timeout->modify( '-2 hours' );
        $qb->setParameter('updated', $timeout);
        
        $activeOrders = $qb->getQuery()->getResult();
    
        return new ViewModel(array(
            'activeOrders' => $activeOrders,
        ));
    }
    
}