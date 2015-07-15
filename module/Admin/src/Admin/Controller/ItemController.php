<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use ersEntity\Entity;
use Admin\Form;
use Admin\Service;
use Admin\InputFilter;

class ItemController extends AbstractActionController {
    public function indexAction()
    {
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        return new ViewModel(array(
            'agegroups' => $em->getRepository("ersEntity\Entity\Agegroup")
                ->findBy(array(), array('agegroup' => 'ASC')),
        ));
    }
    
    public function detailAction() {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/order', array());
        }
        
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $package = $em->getRepository("ersEntity\Entity\Package")
                ->findOneBy(array('id' => $id));
        
        return new ViewModel(array(
            'package' => $package,
        ));
    }
    
    public function editAction() {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/order', array());
        }
    }
    
    public function orderedAction() {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/order', array());
        }
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $item = $em->getRepository("ersEntity\Entity\Item")
                ->findOneBy(array('id' => $id));
        
        $forrest = new Service\BreadcrumbFactory();
        if(!$forrest->exists('item')) {
            $forrest->set('item', 'admin/order');
        }
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $ret = $request->getPost('del', 'No');

            if ($ret == 'Yes') {
                $id = (int) $request->getPost('id');
                
                $item = $em->getRepository("ersEntity\Entity\Item")
                    ->findOneBy(array('id' => $id));
                
                $item->setStatus('ordered');
                $em->persist($item);
                
                $em->flush();
                
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
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/order', array());
        }
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $item = $em->getRepository("ersEntity\Entity\Item")
                ->findOneBy(array('id' => $id));
        
        $forrest = new Service\BreadcrumbFactory();
        if(!$forrest->exists('item')) {
            $forrest->set('item', 'admin/order');
        }
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $ret = $request->getPost('del', 'No');

            if ($ret == 'Yes') {
                $id = (int) $request->getPost('id');
                
                $item = $em->getRepository("ersEntity\Entity\Item")
                    ->findOneBy(array('id' => $id));
                
                $item->setStatus('cancelled');
                $em->persist($item);
                
                $em->flush();
                
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
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/order', array());
        }
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $item = $em->getRepository("ersEntity\Entity\Item")
                ->findOneBy(array('id' => $id));
        
        $forrest = new Service\BreadcrumbFactory();
        if(!$forrest->exists('item')) {
            $forrest->set('item', 'admin/order');
        }
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $ret = $request->getPost('del', 'No');

            if ($ret == 'Yes') {
                $id = (int) $request->getPost('id');
                
                $item = $em->getRepository("ersEntity\Entity\Item")
                    ->findOneBy(array('id' => $id));
                
                $item->setStatus('ordered');
                $em->persist($item);
                
                $em->flush();
                
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
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/order', array());
        }
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $item = $em->getRepository("ersEntity\Entity\Item")
                ->findOneBy(array('id' => $id));
        
        $forrest = new Service\BreadcrumbFactory();
        if(!$forrest->exists('item')) {
            $forrest->set('item', 'admin/order');
        }
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $ret = $request->getPost('del', 'No');

            if ($ret == 'Yes') {
                $id = (int) $request->getPost('id');
                
                $item = $em->getRepository("ersEntity\Entity\Item")
                    ->findOneBy(array('id' => $id));
                
                $item->setStatus('refund');
                $em->persist($item);
                
                $order = $item->getPackage()->getOrder();
                $order->setPaymentStatus('refund');
                $em->persist($order);
                
                $em->flush();
                
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
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/order', array());
        }
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $item = $em->getRepository("ersEntity\Entity\Item")
                ->findOneBy(array('id' => $id));
        
        $forrest = new Service\BreadcrumbFactory();
        if(!$forrest->exists('item')) {
            $forrest->set('item', 'admin/order');
        }
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $ret = $request->getPost('del', 'No');

            if ($ret == 'Yes') {
                $id = (int) $request->getPost('id');
                
                $item = $em->getRepository("ersEntity\Entity\Item")
                    ->findOneBy(array('id' => $id));
                
                $item->setStatus('ordered');
                $em->persist($item);
                
                $order = $item->getPackage()->getOrder();
                $order->setPaymentStatus('unpaid');
                
                $em->flush();
                
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
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/order', array());
        }
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $item = $em->getRepository("ersEntity\Entity\Item")
                ->findOneBy(array('id' => $id));
        
        $forrest = new Service\BreadcrumbFactory();
        if(!$forrest->exists('item')) {
            $forrest->set('item', 'admin/order');
        }
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $ret = $request->getPost('del', 'No');

            if ($ret == 'Yes') {
                $id = (int) $request->getPost('id');
                
                $item = $em->getRepository("ersEntity\Entity\Item")
                    ->findOneBy(array('id' => $id));
                
                $item->setStatus('zero_ok');
                $em->persist($item);
                
                $em->flush();
                
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
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/order', array());
        }
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $item = $em->getRepository("ersEntity\Entity\Item")
                ->findOneBy(array('id' => $id));
        
        $forrest = new Service\BreadcrumbFactory();
        if(!$forrest->exists('item')) {
            $forrest->set('item', 'admin/order');
        }
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $ret = $request->getPost('del', 'No');

            if ($ret == 'Yes') {
                $id = (int) $request->getPost('id');
                
                $item = $em->getRepository("ersEntity\Entity\Item")
                    ->findOneBy(array('id' => $id));
                
                $item->setStatus('ordered');
                $em->persist($item);
                
                $em->flush();
                
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
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/order', array());
        }
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $item = $em->getRepository("ersEntity\Entity\Item")
                ->findOneBy(array('id' => $id));
        
        $forrest = new Service\BreadcrumbFactory();
        if(!$forrest->exists('item')) {
            $forrest->set('item', 'admin/order');
        }
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $ret = $request->getPost('del', 'No');

            if ($ret == 'Yes') {
                $id = (int) $request->getPost('id');
                
                $item = $em->getRepository("ersEntity\Entity\Item")
                    ->findOneBy(array('id' => $id));
                
                $item->setStatus('paid');
                $em->persist($item);
                
                $em->flush();
                
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
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/order', array('action' => 'search'));
        }
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $item = $em->getRepository("ersEntity\Entity\Item")
                ->findOneBy(array('id' => $id));
        
        $form = new Form\SearchPackage();
        
        $results = [];
        
        $q = trim($this->params()->fromQuery('q'));

        if (!empty($q)) {
            $form->get('q')->setValue($q);

            $em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');

            $qb = $em->createQueryBuilder()
                    ->select('u')
                    ->from('ersEntity\Entity\User', 'u')
                    ->orderBy('u.firstname')
                    ->where('1=1');
            
            /*$qb = $em->createQueryBuilder()
                    ->select('p')
                    ->from('ersEntity\Entity\Package', 'p')
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
            error_log('found '.count($results).' user');
        }
        
        $forrest = new Service\BreadcrumbFactory();
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
        
        $em = $this->getServiceLocator()
                ->get('Doctrine\ORM\EntityManager');
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $inputFilter = $this->getServiceLocator()
                    ->get('Admin\InputFilter\AcceptParticipantChangeItem');
            $form->setInputFilter($inputFilter->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $data = $form->getData();
                $user = $em->getRepository("ersEntity\Entity\User")
                    ->findOneBy(array('id' => $data['user_id']));
                
                $item = $em->getRepository("ersEntity\Entity\Item")
                    ->findOneBy(array('id' => $data['item_id']));
                
                $log = new Entity\Log();
                $log->setUser($this->zfcUserAuthentication()->getIdentity());
                $log->setData('changed participant for item '.$item->getCode()->getValue().': '.$data['comment']);
                $em->persist($log);
                #$em->flush();
                
                $package = $item->getPackage();
                
                # initialize new package
                $newPackage = new Entity\Package();
                $pCode = new Entity\Code();
                $pCode->genCode();
                $newPackage->setCode($pCode);
                
                # set order for package
                $newPackage->setOrder($package->getOrder());
                
                $newItem = clone $item;
                $newPackage->addItem($newItem);
                $item->setStatus('transferred');
                $item->setTransferredItem($newItem);

                $iCode = new Entity\Code();
                $iCode->genCode();
                $newItem->setCode($iCode);

                $em->persist($item);
                $em->persist($newItem);
                
                #$newPackage->setTransferredPackage($package);
                $newPackage->setParticipant($user);
                
                $em->persist($newPackage);
                #$em->persist($package);
                $em->flush();
                
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
            error_log('searching user with id: '.$user_id);
            $user = $em->getRepository("ersEntity\Entity\User")
                    ->findOneBy(array('id' => $user_id));
        }
        
        $item = null;
        if($item_id != 0) {
            $item = $em->getRepository("ersEntity\Entity\Item")
                    ->findOneBy(array('id' => $item_id));
        }
        
        $form->get('item_id')->setValue($item->getId());
        $form->get('user_id')->setValue($user->getId());
        
        $forrest = new Service\BreadcrumbFactory();
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