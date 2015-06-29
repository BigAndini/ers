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
use Admin\Service;
use Admin\InputFilter;
use ersEntity\Entity;
use ersEntity\Service as ersService;

class OrderController extends AbstractActionController {
 
    public function indexAction()
    {
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $orders = $em->getRepository("ersEntity\Entity\Order")
                ->findBy(array(), array('created' => 'DESC'));
        
        return new ViewModel(array(
            'orders' => $orders,
        ));
    }
    
    public function searchAction() {
        $logger = $this->getServiceLocator()->get('Logger');
        
        $form = new Form\SearchOrder();
        
        $result = array();
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $inputFilter = new InputFilter\SearchOrder();
            $form->setInputFilter($inputFilter->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $data = $form->getData();
                
                /*
                 * - use "" (quotes) to search exakt strings with spaces
                 * - use - (minus) to exclude a string
                 * - use space as and operator
                 * - use (a,b,c) as or operator
                 */
                $searchText = $data['q'];
                
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
                
                #error_log('searchText: '.$searchText);
                
                $em = $this->getServiceLocator()
                    ->get('Doctrine\ORM\EntityManager');
                
                $result = array();
                
                /*
                 * search code
                 */
                $qb = $em->getRepository("ersEntity\Entity\Order")->createQueryBuilder('o');
                $qb->join('o.code', 'c');
                $i = 0;
                foreach($searchElements as $element) {
                    if($i == 0) {
                        $qb->where('c.value LIKE :param'.$i);
                    } else {
                        $qb->orWhere('c.value LIKE :param'.$i);
                    }
                    error_log('element: '.$element);
                    $qb->setParameter('param'.$i, $element);
                    $i++;
                    
                    $code = new Entity\Code();
                    $code->setValue($element);
                    if($code->checkCode()) {
                        $qb->orWhere('c.value LIKE :param'.$i);
                        $qb->setParameter('param'.$i, $code->getValue());
                        $i++;
                    }
                    
                }
                
                $check_first = $i;
                foreach($excludeElements as $elemnt) {
                    if($i == $check_first) {
                        $qb->where('c.value NOT LIKE :param'.$i);
                    } else {
                        $qb->orWhere('c.value LIKE :param'.$i);
                    }
                    $qb->setParameter('param'.$i, $element);
                    $i++;
                }
                
                $result = array_merge($result, $qb->getQuery()->getResult());
                
                /*
                 * search firstname, surname, email, birthdate
                 * of buyer and participant
                 */
                $qb = $em->getRepository("ersEntity\Entity\Order")->createQueryBuilder('o');
                $qb->join('o.buyer', 'b');
                $qb->join('o.packages', 'p');
                $qb->join('p.participant', 'u');
                $i = 0;
                foreach($searchElements as $element) {
                    $b_expr = $qb->expr()->lower($qb->expr()->concat('b.firstname', $qb->expr()->concat($qb->expr()->literal(' '), 'b.surname')));
                    $u_expr = $qb->expr()->lower($qb->expr()->concat('u.firstname', $qb->expr()->concat($qb->expr()->literal(' '), 'u.surname')));
                    if($i == 0) {
                        /*$qb->add('where', $qb->expr()->orX(
                            $qb->expr()->eq('u.id', '?1'),
                            $qb->expr()->like('u.nickname', '?2')
                        ));*/
                        #$qb->setParameter(1, 100);
                        $qb->where($qb->expr()->like($b_expr, ':param'.$i));
                        #$qb->where('LOWER(CONCAT(b.firstname, " ",b.surname)) LIKE :param'.$i);
                        #$qb->orWhere('LOWER(CONCAT(u.firstname, " ",u.surname)) LIKE :param'.$i);
                    } else {
                        $qb->orWhere($qb->expr()->like($b_expr, ':param'.$i));
                        
                        #$qb->orWhere('LOWER(CONCAT(b.firstname, " ",b.surname)) LIKE :param'.$i);
                        #$qb->orWhere('LOWER(CONCAT(u.firstname, " ",u.surname)) LIKE :param'.$i);
                    }
                    $qb->orWhere($qb->expr()->like($u_expr, ':param'.$i));
                    $qb->setParameter('param'.$i, '%'.strtolower($element).'%');
                    $i++;
                }
                #error_log($qb->getQuery()->getSql());
                $result = array_merge($result, $qb->getQuery()->getResult());
                
            } else {
                $logger->warn($form->getMessages());
            }
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
        $order = $em->getRepository("ersEntity\Entity\Order")
                ->findOneBy(array('id' => $id));
        $paymentDetails = $em->getRepository("ersEntity\Entity\PaymentDetail")
                ->findBy(array('Order_id' => $id), array('created' => 'DESC'));
        
        $forrest = new Service\BreadcrumbFactory();
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
        $order = $em->getRepository("ersEntity\Entity\Order")
                ->findOneBy(array('id' => $id));
        
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
        
        $form = new Form\ChangePaymentType();
        $form->get('paymenttype_id')->setValueOptions($types);
        
        $forrest = new Service\BreadcrumbFactory();
        if(!$forrest->exists('order')) {
            $forrest->set('order', 'admin/order');
        }
        
        $logger = $this->getServiceLocator()->get('Logger');
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $inputFilter = new InputFilter\PaymentType();
            $form->setInputFilter($inputFilter->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $data = $form->getData();
                
                $paymenttype = $em->getRepository("ersEntity\Entity\PaymentType")
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
        $order = $em->getRepository("ersEntity\Entity\Order")
                ->findOneBy(array('id' => $id));
        
        $forrest = new Service\BreadcrumbFactory();
        if(!$forrest->exists('order')) {
            $forrest->set('order', 'admin/order');
        }
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $ret = $request->getPost('del', 'No');

            if ($ret == 'Yes') {
                $id = (int) $request->getPost('id');
                
                $order = $em->getRepository("ersEntity\Entity\Order")
                    ->findOneBy(array('id' => $id));
                
                $emailService = $this->getServiceLocator()
                        ->get('ersEntity\Service\EmailService');
                
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
        $order = $em->getRepository("ersEntity\Entity\Order")
                ->findOneBy(array('id' => $id));
        
        $forrest = new Service\BreadcrumbFactory();
        if(!$forrest->exists('order')) {
            $forrest->set('order', 'admin/order');
        }
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $ret = $request->getPost('del', 'No');

            if ($ret == 'Yes') {
                $id = (int) $request->getPost('id');
                
                $order = $em->getRepository("ersEntity\Entity\Order")
                    ->findOneBy(array('id' => $id));
                
                if($order->getPaymentStatus() != 'paid') {
                    return $this->redirect()->toRoute('admin/order', array('action' => 'send-eticket'));
                }
                
                $eticketService = $this->getServiceLocator()->get('PreReg\Service\ETicketService');
                foreach($order->getPackages() as $package) {
                    if($package->getStatus() != 'paid') {
                        continue;
                    }

                    # prepare email (participant, buyer)
                    $emailService = new ersService\EmailService();
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
                    $subject = "[EJC 2015] E-Ticket for ".$participant->getFirstname()." ".$participant->getSurname()." (order ".$order->getCode()->getValue().")";
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
                        ->get('PreReg\Service\ETicketService');

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
    
    public function changePackageAction() {
        $id = (int) $this->params()->fromRoute('id', 0);
        
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $package = $em->getRepository("ersEntity\Entity\Package")
                ->findOneBy(array('id' => $id));
        
        return new ViewModel(array(
            'package' => $package,
        ));
    }
    
    public function changeItemAction() {
        $id = (int) $this->params()->fromRoute('id', 0);
        
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $item = $em->getRepository("ersEntity\Entity\Item")
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
        $order = $em->getRepository("ersEntity\Entity\Order")
                ->findOneBy(array('id' => $id));
        
        $forrest = new Service\BreadcrumbFactory();
        if(!$forrest->exists('order')) {
            $forrest->set('order', 'admin/order');
        }
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $ret = $request->getPost('del', 'No');

            if ($ret == 'Yes') {
                $id = (int) $request->getPost('id');
                
                $order = $em->getRepository("ersEntity\Entity\Order")
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
        $order = $em->getRepository("ersEntity\Entity\Order")
                ->findOneBy(array('id' => $id));
        
        $forrest = new Service\BreadcrumbFactory();
        if(!$forrest->exists('order')) {
            $forrest->set('order', 'admin/order');
        }
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $ret = $request->getPost('del', 'No');

            if ($ret == 'Yes') {
                $id = (int) $request->getPost('id');
                
                $order = $em->getRepository("ersEntity\Entity\Order")
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
        $order = $em->getRepository("ersEntity\Entity\Order")
                ->findOneBy(array('id' => $id));
        
        $forrest = new Service\BreadcrumbFactory();
        if(!$forrest->exists('order')) {
            $forrest->set('order', 'admin/order');
        }
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $ret = $request->getPost('del', 'No');

            if ($ret == 'Yes') {
                $id = (int) $request->getPost('id');
                
                $order = $em->getRepository("ersEntity\Entity\Order")
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
        $order = $em->getRepository("ersEntity\Entity\Order")
                ->findOneBy(array('id' => $id));
        
        $forrest = new Service\BreadcrumbFactory();
        if(!$forrest->exists('order')) {
            $forrest->set('order', 'admin/order');
        }
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $ret = $request->getPost('del', 'No');

            if ($ret == 'Yes') {
                $id = (int) $request->getPost('id');
                
                $order = $em->getRepository("ersEntity\Entity\Order")
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
        
        $repository = $em->getRepository("ersEntity\Entity\Order");

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