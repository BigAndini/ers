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
        
        $orders = $em->getRepository("ErsBase\Entity\Order")
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
        $searchText = $this->params()->fromQuery('q');
        if(!empty($searchText)) {

            $form->get('q')->setValue($searchText);

            $matches = array();
            preg_match('/"[^"]+"/', $searchText, $matches);
            $searchElements = preg_replace('/"/', '', $matches);

            $logger->info('found matches:');
            $logger->info($matches);
            $searchArray = split(' ', $searchText);
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

            $logger->info('search elements:');
            $logger->info($searchElements);

            $logger->info('exclude elements:');
            $logger->info($excludeElements);

            $searchString = array(

            );

            $em = $this->getServiceLocator()
                ->get('Doctrine\ORM\EntityManager');

            $result = array();

            /*
             * search code
             */
            $qb = $em->getRepository("ErsBase\Entity\Order")->createQueryBuilder('o');
            $qb->join('o.code', 'oc');
            $qb->join('o.packages', 'p');
            $qb->join('p.code', 'pc');
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
            $qb = $em->getRepository("ErsBase\Entity\Order")->createQueryBuilder('o');
            $qb->join('o.buyer', 'b');
            $qb->join('o.packages', 'p');
            $qb->join('p.participant', 'u');
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
        $order = $em->getRepository("ErsBase\Entity\Order")
                ->findOneBy(array('id' => $id));
        $paymentDetails = $em->getRepository("ErsBase\Entity\PaymentDetail")
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
        ));
    }
    public function changePaymentTypeAction() {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/order', array());
        }
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $order = $em->getRepository("ErsBase\Entity\Order")
                ->findOneBy(array('id' => $id));
        
        $paymenttypes = $em->getRepository("ErsBase\Entity\PaymentType")
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
        
        $form = new Form\ChangePaymentType();
        $form->get('paymenttype_id')->setValueOptions($types);
        
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
                
                $paymenttype = $em->getRepository("ErsBase\Entity\PaymentType")
                        ->findOneBy(array('id' => $data['paymenttype_id']));
                
                $logger->info($paymenttype->getName());
                
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
    public function resendConfirmationAction() {
        $logger = $this->getServiceLocator()->get('Logger');
        
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            $logger->info('there is no id');
            return $this->redirect()->toRoute('admin/order', array());
        }
        
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $order = $em->getRepository("ErsBase\Entity\Order")
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
                
                $order = $em->getRepository("ErsBase\Entity\Order")
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
            $logger->info('there is no id');
            return $this->redirect()->toRoute('admin/order', array());
        }
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $order = $em->getRepository("ErsBase\Entity\Order")
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
                
                $order = $em->getRepository("ErsBase\Entity\Order")
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
                    $emailService = new Service\EmailService();
                    $emailService->setFrom('prereg@eja.net');

                    $order = $package->getOrder();
                    $participant = $package->getParticipant();

                    $buyer = $order->getBuyer();
                    $emailService->addTo($buyer);

                    if($participant->getEmail() != '') {
                        $emailService->addTo($participant);
                    }

                    $bcc = new Entity\User();
                    $bcc->setEmail('prereg@eja.net');
                    $emailService->addBcc($bcc);

                    $subject = "Your registration for EJC 2015 (order ".$order->getCode()->getValue().")";
                    $subject = "[EJC 2015] e-Ticket for ".$participant->getFirstname()." ".$participant->getSurname()." (order ".$order->getCode()->getValue().")";
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

                    #$terms1 = getcwd().'/public/Terms-and-Conditions-ERS-EN-v4.pdf';
                    #$terms2 = getcwd().'/public/Terms-and-Conditions-ORGA-EN-v2.pdf';
                    #$emailService->addAttachment($terms1);
                    #$emailService->addAttachment($terms2);

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
            $logger->info('there is no id');
            return $this->redirect()->toRoute('admin/order', array());
        }
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $order = $em->getRepository("ErsBase\Entity\Order")
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
                
                $order = $em->getRepository("ErsBase\Entity\Order")
                    ->findOneBy(array('id' => $id));
                
                # prepare email (participant, buyer)
                $emailService = new Service\EmailService();
                $emailService->setFrom('prereg@eja.net');

                $buyer = $order->getBuyer();
                $emailService->addTo($buyer);

                $bcc = new Entity\User();
                $bcc->setEmail('prereg@eja.net');
                $emailService->addBcc($bcc);

                $subject = "[EJC 2015] Payment reminder for your order: ".$order->getCode()->getValue();
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
        $order = $em->getRepository("ErsBase\Entity\Order")
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
                $user = $em->getRepository("ErsBase\Entity\User")
                    ->findOneBy(array('id' => $data['user_id']));
                
                $order = $em->getRepository("ErsBase\Entity\Order")
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
            $user = $em->getRepository("ErsBase\Entity\User")
                    ->findOneBy(array('id' => $user_id));
        }
        
        $order = null;
        if($order_id != 0) {
            $order = $em->getRepository("ErsBase\Entity\Order")
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
        $package = $em->getRepository("ErsBase\Entity\Package")
                ->findOneBy(array('id' => $id));
        
        return new ViewModel(array(
            'package' => $package,
        ));
    }
    
    public function changeItemAction() {
        $id = (int) $this->params()->fromRoute('id', 0);
        
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $item = $em->getRepository("ErsBase\Entity\Item")
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
        $order = $em->getRepository("ErsBase\Entity\Order")
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
                
                $order = $em->getRepository("ErsBase\Entity\Order")
                    ->findOneBy(array('id' => $id));
                
                $order->setPaymentStatus('cancelled');
                $em->persist($order);
                
                foreach($order->getItems() as $item) {
                    $item->setStatus('cancelled');
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
    
    public function paidAction() {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/order', array());
        }
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $order = $em->getRepository("ErsBase\Entity\Order")
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
                
                $order = $em->getRepository("ErsBase\Entity\Order")
                    ->findOneBy(array('id' => $id));
                
                $order->setPaymentStatus('paid');
                $em->persist($order);
                
                foreach($order->getItems() as $item) {
                    $item->setStatus('paid');
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
        $order = $em->getRepository("ErsBase\Entity\Order")
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
                
                $order = $em->getRepository("ErsBase\Entity\Order")
                    ->findOneBy(array('id' => $id));
                
                $order->setPaymentStatus('refund');
                $em->persist($order);
                
                foreach($order->getItems() as $item) {
                    $item->setStatus('refund');
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
        $order = $em->getRepository("ErsBase\Entity\Order")
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
                
                $order = $em->getRepository("ErsBase\Entity\Order")
                    ->findOneBy(array('id' => $id));
                
                $order->setPaymentStatus('unpaid');
                $em->persist($order);
                
                foreach($order->getItems() as $item) {
                    $item->setStatus('ordered');
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
    
    public function zeroEuroTicketsAction() {
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $repository = $em->getRepository("ErsBase\Entity\Order");

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
}