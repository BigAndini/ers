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
use Admin\Form;
use ErsBase\Service;
use Admin\InputFilter;

class ItemController extends AbstractActionController {
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
        $itemId = (int) $this->params()->fromRoute('id', 0);
        if (!$itemId) {
            return $this->redirect()->toRoute('admin/order', array());
        }
        
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $package = $entityManager->getRepository('ErsBase\Entity\Package')
                ->findOneBy(array('id' => $itemId));
        
        return new ViewModel(array(
            'package' => $package,
        ));
    }
    
    public function editAction() {
        $itemId = (int) $this->params()->fromRoute('id', 0);
        if (!$itemId) {
            return $this->redirect()->toRoute('admin/order', array());
        }
    }
    
    public function orderedAction() {
        $itemId = (int) $this->params()->fromRoute('id', 0);
        if (!$itemId) {
            return $this->redirect()->toRoute('admin/order', array());
        }
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $item = $entityManager->getRepository('ErsBase\Entity\Item')
                ->findOneBy(array('id' => $itemId));
        
        $forrest = new Service\BreadcrumbService();
        if(!$forrest->exists('item')) {
            $forrest->set('item', 'admin/order');
        }
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $ret = $request->getPost('del', 'No');

            if ($ret == 'Yes') {
                $itemId = (int) $request->getPost('id');
                
                $item = $entityManager->getRepository('ErsBase\Entity\Item')
                    ->findOneBy(array('id' => $itemId));
                
                $statusOrdered = $entityManager->getRepository('ErsBase\Entity\Status')
                    ->findOneBy(array('value' => 'ordered'));
                $item->setStatus($statusOrdered);
                $entityManager->persist($item);
                
                $entityManager->flush();
                
                $breadcrumb = $forrest->get('item');
                return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
            }
        }
        
        return new ViewModel(array(
            'item' => $item,
            'breadcrumb' => $forrest->get('item'),
        ));
    }
    
    public function cancelAction() {
        $itemId = (int) $this->params()->fromRoute('id', 0);
        if (!$itemId) {
            return $this->redirect()->toRoute('admin/order', array());
        }
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $item = $entityManager->getRepository('ErsBase\Entity\Item')
                ->findOneBy(array('id' => $itemId));
        
        $forrest = new Service\BreadcrumbService();
        if(!$forrest->exists('item')) {
            $forrest->set('item', 'admin/order');
        }
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $ret = $request->getPost('del', 'No');

            if ($ret == 'Yes') {
                $itemId = (int) $request->getPost('id');
                
                $item = $entityManager->getRepository('ErsBase\Entity\Item')
                    ->findOneBy(array('id' => $itemId));
                
                $statusCancelled = $entityManager->getRepository('ErsBase\Entity\Status')
                    ->findOneBy(array('value' => 'cancelled'));
                
                $item->setStatus($statusCancelled);
                foreach($item->getSubItems() as $subItem) {
                    $subItem->setStatus($statusCancelled);
                    $entityManager->persist($subItem);
                }
                $entityManager->persist($item);
                
                $entityManager->flush();
                
                $breadcrumb = $forrest->get('item');
                return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
            }
        }
        
        return new ViewModel(array(
            'item' => $item,
            'breadcrumb' => $forrest->get('item'),
        ));
    }
    
    public function uncancelAction() {
        $itemId = (int) $this->params()->fromRoute('id', 0);
        if (!$itemId) {
            return $this->redirect()->toRoute('admin/order', array());
        }
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $item = $entityManager->getRepository('ErsBase\Entity\Item')
                ->findOneBy(array('id' => $itemId));
        
        $forrest = new Service\BreadcrumbService();
        if(!$forrest->exists('item')) {
            $forrest->set('item', 'admin/order');
        }
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $ret = $request->getPost('del', 'No');

            if ($ret == 'Yes') {
                $itemId = (int) $request->getPost('id');
                
                $item = $entityManager->getRepository('ErsBase\Entity\Item')
                    ->findOneBy(array('id' => $itemId));
                
                $statusOrdered = $entityManager->getRepository('ErsBase\Entity\Status')
                    ->findOneBy(array('value' => 'ordered'));
                
                $item->setStatus($statusOrdered);
                $entityManager->persist($item);
                
                $entityManager->flush();
                
                $breadcrumb = $forrest->get('item');
                return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
            }
        }
        
        return new ViewModel(array(
            'item' => $item,
            'breadcrumb' => $forrest->get('item'),
        ));
    }
    
    public function refundAction() {
        $itemId = (int) $this->params()->fromRoute('id', 0);
        if (!$itemId) {
            return $this->redirect()->toRoute('admin/order', array());
        }
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $item = $entityManager->getRepository('ErsBase\Entity\Item')
                ->findOneBy(array('id' => $itemId));
        
        $forrest = new Service\BreadcrumbService();
        if(!$forrest->exists('item')) {
            $forrest->set('item', 'admin/order');
        }
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $ret = $request->getPost('del', 'No');

            if ($ret == 'Yes') {
                $itemId = (int) $request->getPost('id');
                
                $item = $entityManager->getRepository('ErsBase\Entity\Item')
                    ->findOneBy(array('id' => $itemId));
                
                $statusRefund = $entityManager->getRepository('ErsBase\Entity\Status')
                    ->findOneBy(array('value' => 'refund'));
                
                $item->setStatus($statusRefund);
                $entityManager->persist($item);
                
                $order = $item->getPackage()->getOrder();
                $order->setPaymentStatus('refund');
                $entityManager->persist($order);
                
                $entityManager->flush();
                
                $breadcrumb = $forrest->get('item');
                return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
            }
        }
        
        return new ViewModel(array(
            'item' => $item,
            'breadcrumb' => $forrest->get('item'),
        ));
    }
    
    public function undoRefundAction() {
        $itemId = (int) $this->params()->fromRoute('id', 0);
        if (!$itemId) {
            return $this->redirect()->toRoute('admin/order', array());
        }
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $item = $entityManager->getRepository('ErsBase\Entity\Item')
                ->findOneBy(array('id' => $itemId));
        
        $forrest = new Service\BreadcrumbService();
        if(!$forrest->exists('item')) {
            $forrest->set('item', 'admin/order');
        }
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $ret = $request->getPost('del', 'No');

            if ($ret == 'Yes') {
                $itemId = (int) $request->getPost('id');
                
                $item = $entityManager->getRepository('ErsBase\Entity\Item')
                    ->findOneBy(array('id' => $itemId));
                
                $statusOrdered = $entityManager->getRepository('ErsBase\Entity\Status')
                    ->findOneBy(array('value' => 'ordered'));
                
                $item->setStatus($statusOrdered);
                $entityManager->persist($item);
                
                $order = $item->getPackage()->getOrder();
                $order->setPaymentStatus('unpaid');
                
                $entityManager->flush();
                
                $breadcrumb = $forrest->get('item');
                return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
            }
        }
        
        return new ViewModel(array(
            'item' => $item,
            'breadcrumb' => $forrest->get('item'),
        ));
    }
    
    public function zeroOkAction() {
        $itemId = (int) $this->params()->fromRoute('id', 0);
        if (!$itemId) {
            return $this->redirect()->toRoute('admin/order', array());
        }
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $item = $entityManager->getRepository('ErsBase\Entity\Item')
                ->findOneBy(array('id' => $itemId));
        
        $forrest = new Service\BreadcrumbService();
        if(!$forrest->exists('item')) {
            $forrest->set('item', 'admin/order');
        }
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $ret = $request->getPost('del', 'No');

            if ($ret == 'Yes') {
                $itemId = (int) $request->getPost('id');
                
                $item = $entityManager->getRepository('ErsBase\Entity\Item')
                    ->findOneBy(array('id' => $itemId));
                
                $statusZeroOk = $entityManager->getRepository('ErsBase\Entity\Status')
                    ->findOneBy(array('value' => 'zero_ok'));
                
                $item->setStatus($statusZeroOk);
                $entityManager->persist($item);
                
                $entityManager->flush();
                
                $breadcrumb = $forrest->get('item');
                return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
            }
        }
        
        return new ViewModel(array(
            'item' => $item,
            'breadcrumb' => $forrest->get('item'),
        ));
    }
    
    public function zeroNotOkAction() {
        $itemId = (int) $this->params()->fromRoute('id', 0);
        if (!$itemId) {
            return $this->redirect()->toRoute('admin/order', array());
        }
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $item = $entityManager->getRepository('ErsBase\Entity\Item')
                ->findOneBy(array('id' => $itemId));
        
        $forrest = new Service\BreadcrumbService();
        if(!$forrest->exists('item')) {
            $forrest->set('item', 'admin/order');
        }
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $ret = $request->getPost('del', 'No');

            if ($ret == 'Yes') {
                $itemId = (int) $request->getPost('id');
                
                $item = $entityManager->getRepository('ErsBase\Entity\Item')
                    ->findOneBy(array('id' => $itemId));
                
                $statusOrdered = $entityManager->getRepository('ErsBase\Entity\Status')
                    ->findOneBy(array('value' => 'ordered'));
                
                $item->setStatus($statusOrdered);
                $entityManager->persist($item);
                
                $entityManager->flush();
                
                $breadcrumb = $forrest->get('item');
                return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
            }
        }
        
        return new ViewModel(array(
            'item' => $item,
            'breadcrumb' => $forrest->get('item'),
        ));
    }
    
    public function paidAction() {
        $itemId = (int) $this->params()->fromRoute('id', 0);
        if (!$itemId) {
            return $this->redirect()->toRoute('admin/order', array());
        }
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $item = $entityManager->getRepository('ErsBase\Entity\Item')
                ->findOneBy(array('id' => $itemId));
        
        $forrest = new Service\BreadcrumbService();
        if(!$forrest->exists('item')) {
            $forrest->set('item', 'admin/order');
        }
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $ret = $request->getPost('del', 'No');

            if ($ret == 'Yes') {
                $itemId = (int) $request->getPost('id');
                
                $item = $entityManager->getRepository('ErsBase\Entity\Item')
                    ->findOneBy(array('id' => $itemId));
                
                $statusPaid = $entityManager->getRepository('ErsBase\Entity\Status')
                    ->findOneBy(array('value' => 'paid'));
                
                $item->setStatus($statusPaid);
                $entityManager->persist($item);
                
                $entityManager->flush();
                
                $breadcrumb = $forrest->get('item');
                return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
            }
        }
        
        return new ViewModel(array(
            'item' => $item,
            'breadcrumb' => $forrest->get('item'),
        ));
    }
    
    public function changeParticipantAction() {    
        $itemId = (int) $this->params()->fromRoute('id', 0);
        if (!$itemId) {
            return $this->redirect()->toRoute('admin/order', array('action' => 'search'));
        }
        
        
        
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $item = $entityManager->getRepository('ErsBase\Entity\Item')
                ->findOneBy(array('id' => $itemId));
        
        $form = new Form\SearchPackage();
        
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
        $forrest->set('item', 'admin/item', 
                array(
                    'action' => 'change-participant',
                    'id' => $item->getId()
                ), 
                array(
                    'query' => $query,
                    #'fragment' => $fragment,
                )
            );
        $breadcrumb = $forrest->get('item');
        $forrest->set('user', $breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
        
        return new ViewModel(array(
            'form' => $form,
            'item' => $item,
            'results' => $results,
        ));
    }
    public function acceptParticipantChangeAction() {
        $logger = $this->getServiceLocator()->get('Logger');
        
        $user_id = (int) $this->params()->fromQuery('user_id', 0);
        $item_id = (int) $this->params()->fromQuery('item_id', 0);
        
        $form = new Form\AcceptParticipantChangeItem();
        
        $entityManager = $this->getServiceLocator()
                ->get('Doctrine\ORM\EntityManager');
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $inputFilter = $this->getServiceLocator()
                    ->get('Admin\InputFilter\AcceptParticipantChangeItem');
            #$form->setInputFilter($inputFilter->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $data = $form->getData();
                $user = $entityManager->getRepository('ErsBase\Entity\User')
                    ->findOneBy(array('id' => $data['user_id']));
                
                $item = $entityManager->getRepository('ErsBase\Entity\Item')
                    ->findOneBy(array('id' => $data['item_id']));
                
                $log = new Entity\Log();
                $log->setUser($this->zfcUserAuthentication()->getIdentity());
                $log->setData('changed participant for item '.$item->getCode()->getValue().': '.$data['comment']);
                $entityManager->persist($log);
                #$entityManager->flush();
                
                $package = $item->getPackage();
                
                # initialize new package
                $newPackage = new Entity\Package();
                $code = new Entity\Code();
                $code->genCode();
                $codecheck = 1;
                while($codecheck != null) {
                    $code->genCode();
                    $codecheck = $entityManager->getRepository('ErsBase\Entity\Code')
                        ->findOneBy(array('value' => $code->getValue()));
                }
                $newPackage->setCode($code);
                
                # set order for package
                $newPackage->setOrder($package->getOrder());
                $newPackage->setStatus($package->getStatus());
                
                $cloneService = $this->getServiceLocator()
                    ->get('ErsBase\Service\CloneService');
                $cloneService->setTransfer(true);
                $newItem = $cloneService->cloneItem($item);
                
                $statusTransferred = $entityManager->getRepository('ErsBase\Entity\Status')
                    ->findOneBy(array('value' => 'transferred'));
                
                $newPackage->addItem($newItem);
                $item->setStatus($statusTransferred);
                $item->setStatusId($statusTransferred->getId());

                $entityManager->persist($item);
                
                $code = new Entity\Code();
                $code->genCode();
                $codecheck = 1;
                while($codecheck != null) {
                    $code->genCode();
                    $codecheck = $entityManager->getRepository('ErsBase\Entity\Code')
                        ->findOneBy(array('value' => $code->getValue()));
                }
                $newItem->setCode($code);

                $entityManager->persist($newItem);
                
                $newPackage->setParticipant($user);
                
                $entityManager->persist($newPackage);
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
        
        $item = null;
        if($item_id != 0) {
            $item = $entityManager->getRepository('ErsBase\Entity\Item')
                    ->findOneBy(array('id' => $item_id));
        }
        
        $form->get('item_id')->setValue($item->getId());
        $form->get('user_id')->setValue($user->getId());
        
        $forrest = new Service\BreadcrumbService();
        if(!$forrest->exists('item')) {
            $forrest->set('item', 'admin/order', 
                    array('action' => 'search')
                );
        }
        
        return new ViewModel(array(
            'form' => $form,
            'user' => $user,
            'item' => $item,
            'breadcrumb' => $forrest->get('package'),
        ));
    }
}