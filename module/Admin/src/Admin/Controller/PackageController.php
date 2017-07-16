<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use ErsBase\Entity;
use ErsBase\Service as ersService;
use Admin\Form;
use ErsBase\Service;
use Admin\InputFilter;

class PackageController extends AbstractActionController {
    public function indexAction()
    {
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        return new ViewModel(array(
            'agegroups' => $entityManager->getRepository('ErsBase\Entity\Agegroup')
                ->findBy(array(), array('agegroup' => 'ASC')),
        ));
    }
    
    public function detailAction() {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/order', array());
        }
        
        $forrest = new Service\BreadcrumbService();
        if(!$forrest->exists('package')) {
            $forrest->set('package', 'admin/package', array('action' => 'detail', 'id' => $id));
        }
        $forrest->set('item', 'admin/package', array('action' => 'detail', 'id' => $id));
        
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $package = $entityManager->getRepository('ErsBase\Entity\Package')
                ->findOneBy(array('id' => $id));
        
        return new ViewModel(array(
            'package' => $package,
        ));
    }
    
    public function unpaidAction() {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/order', array());
        }
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $package = $entityManager->getRepository('ErsBase\Entity\Package')
                ->findOneBy(array('id' => $id));
        
        $forrest = new Service\BreadcrumbService();
        if(!$forrest->exists('package')) {
            $forrest->set('package', 'admin/order');
        }
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $ret = $request->getPost('del', 'No');

            if ($ret == 'Yes') {
                $id = (int) $request->getPost('id');
                
                $package = $entityManager->getRepository('ErsBase\Entity\Package')
                    ->findOneBy(array('id' => $id));
                
                $statusOrdered = $entityManager->getRepository('ErsBase\Entity\Status')
                    ->findOneBy(array('value' => 'ordered'));
                
                foreach($package->getItems() as $item) {
                    $item->setStatus($statusOrdered);
                    $entityManager->persist($item);
                }
                
                $entityManager->flush();
                
                $breadcrumb = $forrest->get('package');
                return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
            }
        }
        
        return new ViewModel(array(
            'package' => $package,
            'breadcrumb' => $forrest->get('package'),
        ));
    }
    
    public function paidAction() {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/order', array());
        }
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $package = $entityManager->getRepository('ErsBase\Entity\Package')
                ->findOneBy(array('id' => $id));
        
        $forrest = new Service\BreadcrumbService();
        if(!$forrest->exists('package')) {
            $forrest->set('package', 'admin/order');
        }
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $ret = $request->getPost('del', 'No');

            if ($ret == 'Yes') {
                $id = (int) $request->getPost('id');
                
                $package = $entityManager->getRepository('ErsBase\Entity\Package')
                    ->findOneBy(array('id' => $id));
                
                $statusPaid = $entityManager->getRepository('ErsBase\Entity\Status')
                    ->findOneBy(array('value' => 'paid'));
                
                foreach($package->getItems() as $item) {
                    $item->setStatus($statusPaid);
                    $entityManager->persist($item);
                }
                
                $entityManager->flush();
                
                $breadcrumb = $forrest->get('package');
                return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
            }
        }
        
        return new ViewModel(array(
            'package' => $package,
            'breadcrumb' => $forrest->get('package'),
        ));
    }
    
    public function refundAction() {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/order', array());
        }
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $package = $entityManager->getRepository('ErsBase\Entity\Package')
                ->findOneBy(array('id' => $id));
        
        $forrest = new Service\BreadcrumbService();
        if(!$forrest->exists('package')) {
            $forrest->set('package', 'admin/order');
        }
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $ret = $request->getPost('del', 'No');

            if ($ret == 'Yes') {
                $id = (int) $request->getPost('id');
                
                $package = $entityManager->getRepository('ErsBase\Entity\Package')
                    ->findOneBy(array('id' => $id));
                
                $statusRefund = $entityManager->getRepository('ErsBase\Entity\Status')
                    ->findOneBy(array('value' => 'refund'));
                
                foreach($package->getItems() as $item) {
                    $item->setStatus($statusRefund);
                    $entityManager->persist($item);
                }
                
                $entityManager->flush();
                
                $breadcrumb = $forrest->get('package');
                return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
            }
        }
        
        return new ViewModel(array(
            'package' => $package,
            'breadcrumb' => $forrest->get('package'),
        ));
    }
    
    public function cancelAction() {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/order', array());
        }
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $package = $entityManager->getRepository('ErsBase\Entity\Package')
                ->findOneBy(array('id' => $id));
        
        $forrest = new Service\BreadcrumbService();
        if(!$forrest->exists('package')) {
            $forrest->set('package', 'admin/order');
        }
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $ret = $request->getPost('del', 'No');

            if ($ret == 'Yes') {
                $id = (int) $request->getPost('id');
                
                $package = $entityManager->getRepository('ErsBase\Entity\Package')
                    ->findOneBy(array('id' => $id));
                
                $statusCancelled = $entityManager->getRepository('ErsBase\Entity\Status')
                    ->findOneBy(array('value' => 'cancelled'));
                
                foreach($package->getItems() as $item) {
                    $item->setStatus($statusCancelled);
                    $entityManager->persist($item);
                }
                
                $entityManager->flush();
                
                $breadcrumb = $forrest->get('package');
                return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
            }
        }
        
        return new ViewModel(array(
            'package' => $package,
            'breadcrumb' => $forrest->get('package'),
        ));
    }
    
    public function recalculateAction() {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/order', array());
        }
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $package = $entityManager->getRepository('ErsBase\Entity\Package')
                ->findOneBy(array('id' => $id));
        
        $forrest = new Service\BreadcrumbService();
        if(!$forrest->exists('package')) {
            $forrest->set('package', 'admin/order');
        }
        
        /*
         * get participant
         */
        $participant = $package->getParticipant();
        
        /*
         * get agegroup
         */
        $agegroupService = $this->getServiceLocator()
            ->get('ErsBase\Service\AgegroupService:price');
        $agegroup = $agegroupService->getAgegroupByDate($participant->getBirthday());
        
        /*
         * get orders deadline
         */
        $order = $package->getOrder();
        
        $deadlineService = $this->getServiceLocator()
                ->get('ErsBase\Service\DeadlineService:price');
        /*$deadlineService = new \ErsBase\Service\DeadlineService();
        $deadlines = $entityManager->getRepository('ErsBase\Entity\Deadline')
                    ->findBy(array('price_change' => '1'));
        $deadlineService->setDeadlines($deadlines);*/

        $deadlineService->setCompareDate($order->getCreated());
        $deadline = $deadlineService->getDeadline();
        
        $orderService = $this->getServiceLocator()
                ->get('ErsBase\Service\OrderService');
        #$itemArray = $this->recalcPackage($package, $agegroup, $deadline);
        $itemArray = $orderService->recalcPackage($package, $agegroup, $deadline);
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $ret = $request->getPost('del', 'No');

            if ($ret == 'Yes') {
                $id = (int) $request->getPost('id');
                $package = $entityManager->getRepository('ErsBase\Entity\Package')
                    ->findOneBy(array('id' => $id));
                
                $statusCancelled = $entityManager->getRepository('ErsBase\Entity\Status')
                                ->findOneBy(array('value' => 'cancelled'));
                
                #$itemArray = $this->recalcPackage($package, $agegroup, $deadline);
                $itemArray = $orderService->recalcPackage($package, $agegroup, $deadline);
                foreach($itemArray as $items) {
                    if(isset($items['after'])) {
                        $itemAfter = $items['after'];
                        $itemBefore = $items['before'];
                        
                        #$itemAfter->setStatus($itemBefore->getStatus());
                        
                        $entityManager->persist($itemAfter);
                        
                        $order = $itemAfter->getPackage()->getOrder();
                        if($order->getPaymentStatus() == 'paid') {
                            $order->setPaymentStatus('unpaid');
                        }
                        $order->setOrderSum($order->getPrice());
                        $order->setTotalSum($order->getSum());
                        $entityManager->persist($order);
        
                        $itemBefore->setStatus($statusCancelled);
                        $entityManager->persist($itemBefore);

                        $entityManager->flush();
                    }
                }
                
                $breadcrumb = $forrest->get('package');
                return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
            }
        }
        
        return new ViewModel(array(
            'itemArray' => $itemArray,
            'package' => $package,
            'breadcrumb' => $forrest->get('package'),
        ));
    }
    
    public function downloadEticketAction() {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/order', array());
        }
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $package = $entityManager->getRepository('ErsBase\Entity\Package')
                ->findOneBy(array('id' => $id));
        
        /*$languages = array(
            array(
                'label' => 'English',
                'value' => 'en',
            ),
            array(
                'label' => 'German',
                'value' => 'de',
            ),
            array(
                'label' => 'Italian',
                'value' => 'it',
            ),
            array(
                'label' => 'Spanish',
                'value' => 'es',
            ),
            array(
                'label' => 'French',
                'value' => 'fr',
            ),
        );*/
        
        $form = new Form\DownloadEticket();
        /*$form->get('language')->setValueOptions($languages);*/
        $form->get('submit')->setValue('Download');
        $form->get('id')->setValue($package->getId());
        
        $forrest = new Service\BreadcrumbService();
        if(!$forrest->exists('package')) {
            $forrest->set('package', 'admin/order');
        }
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            #$form->setInputFilter($agegroup->getInputFilter());
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $id = (int) $request->getPost('id');
                
                $package = $entityManager->getRepository('ErsBase\Entity\Package')
                    ->findOneBy(array('id' => $id));
                
                $eticketService = $this->getServiceLocator()
                    ->get('ErsBase\Service\ETicketService');
                
                #$eticketService->setLanguage($request->getPost('language'));
                $eticketService->setPackage($package);
                $file = $eticketService->generatePdf();

                $response = new \Zend\Http\Response\Stream();
                $response->setStream(fopen($file, 'r'));
                $response->setStatusCode(200);
                $response->setStreamName(basename($file));
                $headers = new \Zend\Http\Headers();
                $headers->addHeaders(array(
                    'Content-Disposition' => 'attachment; filename="' . basename($file) .'"',
                    'Content-Type' => 'application/octet-stream',
                    'Content-Length' => filesize($file),
                    'Expires' => '@0', // @0, because zf2 parses date as string to \DateTime() object
                    'Cache-Control' => 'must-revalidate',
                    'Pragma' => 'public'
                ));
                $response->setHeaders($headers);
                return $response;
            } else {
                $logger = $this->getServiceLocator()->get('Logger');
                $logger->warn($form->getMessages());
            }
        }
        
        return new ViewModel(array(
            'form' => $form,
            'package' => $package,
            'breadcrumb' => $forrest->get('package'),
        ));
    }
    
    public function sendEticketAction() {
        $forrest = new Service\BreadcrumbService();
        if(!$forrest->exists('package')) {
            $forrest->set('package', 'admin/order');
        }
        
        $logger = $this->getServiceLocator()->get('Logger');
        
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            $breadcrumb = $forrest->get('package');
            return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
        }
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $package = $entityManager->getRepository('ErsBase\Entity\Package')
                ->findOneBy(array('id' => $id));
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $ret = $request->getPost('del', 'No');

            if ($ret == 'Yes') {
                $id = (int) $request->getPost('id');
                
                $package = $entityManager->getRepository('ErsBase\Entity\Package')
                    ->findOneBy(array('id' => $id));
                
                if($package->getStatus() != 'paid') {
                    return $this->redirect()->toRoute('admin/package', array('action' => 'send-eticket'));
                }
                
                # prepare email (participant, buyer)
                #$emailService = new ersService\EmailService();
                $emailService = $this->getServiceLocator()
                        ->get('ErsBase\Service\EmailService');
                $config = $this->getServiceLocator()
                        ->get('config');
                $emailService->setFrom($config['ERS']['info_mail']);

                $order = $package->getOrder();
                $participant = $package->getParticipant();
                
                
                $buyer = $order->getBuyer();
                if($participant->getEmail() == '') {
                    $emailService->addTo($buyer);
                } elseif($participant->getEmail() == $buyer->getEmail()) {
                    $emailService->addTo($buyer);
                } else {
                    $emailService->addTo($participant);
                    $emailService->addCc($buyer);
                }

                $bcc = new Entity\User();
                $bcc->setEmail($config['ERS']['info_mail']);
                $emailService->addBcc($bcc);

                $subject = "[".$config['ERS']['name_short']."] "._('E-Ticket for')." ".$participant->getFirstname()." ".$participant->getSurname()." (order ".$order->getCode()->getValue().")";
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
                
                $breadcrumb = $forrest->get('order');
                return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
            }
        }
        
        return new ViewModel(array(
            'package' => $package,
            'breadcrumb' => $forrest->get('package'),
        ));
    }
    
    public function changeParticipantAction() {
        $logger = $this->getServiceLocator()->get('Logger');
        
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/order', array());
        }
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $package = $entityManager->getRepository('ErsBase\Entity\Package')
                ->findOneBy(array('id' => $id));
        
        $form = new Form\SearchPackage();
        
        $results = [];
        
        $q = trim($this->params()->fromQuery('q'));

        if (!empty($q)) {
            $form->get('q')->setValue($q);

            $entityManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');

            $qb = $entityManager->createQueryBuilder()
                    ->select('u')
                    ->from('ErsBase\Entity\User', 'u')
                    ->orderBy('u.firstname')
                    ->where('1=1');
            
            /*$qb = $entityManager->createQueryBuilder()
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
        $forrest->set('package', 'admin/package', 
                array(
                    'action' => 'change-participant',
                    'id' => $package->getId()
                ), 
                array(
                    'query' => $query,
                    #'fragment' => $fragment,
                )
            );
        
        return new ViewModel(array(
            'form' => $form,
            'package' => $package,
            'results' => $results,
        ));
    }
    public function acceptParticipantChangeAction() {
        $logger = $this->getServiceLocator()->get('Logger');
        
        $user_id = (int) $this->params()->fromQuery('user_id', 0);
        $package_id = (int) $this->params()->fromQuery('package_id', 0);
        
        $form = new Form\AcceptParticipantChangePackage();
        
        $entityManager = $this->getServiceLocator()
                ->get('Doctrine\ORM\EntityManager');
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $inputFilter = $this->getServiceLocator()
                    ->get('Admin\InputFilter\AcceptParticipantChangePackage');
            #$form->setInputFilter($inputFilter->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $data = $form->getData();
                $user = $entityManager->getRepository('ErsBase\Entity\User')
                    ->findOneBy(array('id' => $data['user_id']));
                
                $package = $entityManager->getRepository('ErsBase\Entity\Package')
                    ->findOneBy(array('id' => $data['package_id']));
                
                $log = new Entity\Log();
                $log->setUser($this->zfcUserAuthentication()->getIdentity());
                $log->setData('changed participant for package '.$package->getCode()->getValue().': '.$data['comment']);
                $entityManager->persist($log);
                
                # initialize new package
                $cloneService = $this->getServiceLocator()
                    ->get('ErsBase\Service\CloneService');
                $cloneService->setTransfer(true);
                $newPackage = $cloneService->clonePackage($package);
                
                # set order for package
                $newPackage->setOrder($package->getOrder());
                
                #$package->setTransferredPackage($newPackage);
                $newPackage->setParticipant($user);
                
                
                $entityManager->persist($newPackage);
                #$entityManager->persist($package);
                $entityManager->flush();
                
                $order = $package->getOrder();
                
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
            $user = $entityManager->getRepository('ErsBase\Entity\User')
                    ->findOneBy(array('id' => $user_id));
        }
        
        $package = null;
        if($package_id != 0) {
            $package = $entityManager->getRepository('ErsBase\Entity\Package')
                    ->findOneBy(array('id' => $package_id));
        }
        
        $form->get('package_id')->setValue($package->getId());
        $form->get('user_id')->setValue($user->getId());
        
        $forrest = new Service\BreadcrumbService();
        if(!$forrest->exists('package')) {
            $forrest->set('package', 'admin/order', 
                    array('action' => 'search')
                );
        }
        
        return new ViewModel(array(
            'form' => $form,
            'user' => $user,
            'package' => $package,
            'breadcrumb' => $forrest->get('package'),
        ));
    }
    
    public function moveAction() {
        $logger = $this->getServiceLocator()->get('Logger');
        
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/order', array());
        }
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $package = $entityManager->getRepository('ErsBase\Entity\Package')
                ->findOneBy(array('id' => $id));
        
        $form = new Form\SearchOrder();
        
        $results = [];
        
        $q = trim($this->params()->fromQuery('q'));

        if (!empty($q)) {
            $form->get('q')->setValue($q);

            $entityManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');

            $qb = $entityManager->createQueryBuilder()
                    ->select('u')
                    ->from('ErsBase\Entity\User', 'u')
                    ->orderBy('u.firstname')
                    ->where('1=1');
            
            /*$qb = $entityManager->createQueryBuilder()
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
        
        return new ViewModel(array(
            'form' => $form,
            'package' => $package,
            'results' => $results,
        ));
    }
    
    public function acceptMoveAction() {
        $logger = $this->getServiceLocator()->get('Logger');
        
        $order_id = (int) $this->params()->fromQuery('order_id', 0);
        $package_id = (int) $this->params()->fromQuery('package_id', 0);
        
        $form = new Form\AcceptMovePackage();
        
        $entityManager = $this->getServiceLocator()
                ->get('Doctrine\ORM\EntityManager');
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $inputFilter = $this->getServiceLocator()
                    ->get('Admin\InputFilter\AcceptMovePackage');
            #$form->setInputFilter($inputFilter->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $data = $form->getData();
                
                $package = $entityManager->getRepository('ErsBase\Entity\Package')
                    ->findOneBy(array('id' => $data['package_id']));
                
                if($data['order_id'] == '') {
                    $order = new Entity\Order();
                    
                    $code = new Entity\Code();
                    $code->genCode();
                    $codecheck = 1;
                    while($codecheck != null) {
                        $code->genCode();
                        $codecheck = $entityManager->getRepository('ErsBase\Entity\Code')
                            ->findOneBy(array('value' => $code->getValue()));
                    }
                    $order->setCode($code);
                    
                    $buyer = $package->getParticipant();
                    $order->setBuyer($buyer);
                } else {
                    $order = $entityManager->getRepository('ErsBase\Entity\Order')
                        ->findOneBy(array('id' => $data['order_id']));
                    
                    
                    
                }
                
                $oldOrder = $package->getOrder();
                $log = new Entity\Log();
                $log->setUser($this->zfcUserAuthentication()->getIdentity());
                $log->setData('moved package '.$package->getCode()->getValue().' from order '.$oldOrder->getCode()->getValue().' to order '.$order->getCode()->getValue().': '.$data['comment']);
                
                $entityManager->persist($log);
                
                # initialize new package
                $newPackage = new Entity\Package();
                $code = new Entity\Code();
                $code->genCode();
                $newPackage->setCode($code);
                
                # set order for package
                $newPackage->setOrder($package->getOrder());
                
                foreach($package->getItems() as $item) {
                    if($item->hasParentItems()) {
                        continue;
                    }
                    $newItem = clone $item;
                    $newPackage->addItem($newItem);
                    
                    $statusTransferred = $entityManager->getRepository('ErsBase\Entity\Status')
                        ->findOneBy(array('value' => 'transferred'));
                    
                    $item->setStatus($statusTransferred);
                    $item->setTransferredItem($newItem);
                    
                    $code = new Entity\Code();
                    $code->genCode();
                    $newItem->setCode($code);
                    
                    $entityManager->persist($item);
                    $entityManager->persist($newItem);
                }
                $newPackage->setTransferredPackage($package);
                $newPackage->setOrder($order);
                $order->addPackage($newPackage);
                
                $entityManager->persist($newPackage);
                $entityManager->persist($order);
                #$entityManager->persist($package);
                $entityManager->flush();
                
                return $this->redirect()->toRoute('admin/order', array(
                    'action' => 'detail', 
                    'id' => $oldOrder->getId()
                ));
            } else {
                $logger->warn($form->getMessages());
            }
        }
        
        $order = null;
        if($order_id != 0) {
            $order = $entityManager->getRepository('ErsBase\Entity\Order')
                    ->findOneBy(array('id' => $order_id));
            $form->get('order_id')->setValue($order->getId());
        }
        
        $package = null;
        if($package_id != 0) {
            $package = $entityManager->getRepository('ErsBase\Entity\Package')
                    ->findOneBy(array('id' => $package_id));
            $form->get('package_id')->setValue($package->getId());
        }
        
        $forrest = new Service\BreadcrumbService();
        if(!$forrest->exists('package')) {
            $forrest->set('package', 'admin/order', 
                    array('action' => 'search')
                );
        }
        
        return new ViewModel(array(
            'form' => $form,
            'order' => $order,
            'package' => $package,
            'breadcrumb' => $forrest->get('package'),
        ));
    }
}