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

class OrderController extends AbstractActionController {
 
    public function indexAction()
    {
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $orders = $em->getRepository("ersEntity\Entity\Order")
                ->findBy(array(), array('created' => 'DESC'));
        
        return new ViewModel(array(
            'orders' => $orders,
        ));
    }
    
    public function detailAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/order', array());
        }
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $order = $em->getRepository("ersEntity\Entity\Order")
                ->findOneBy(array('id' => $id));
        $paymentDetails = $em->getRepository("ersEntity\Entity\PaymentDetail")
                ->findBy(array('Order_id' => $id), array('created' => 'DESC'));
        
        $forrest = new Service\BreadcrumbFactory();
        $forrest->set('order', 'admin/order', array('action' => 'detail', 'id' => $id));
        
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
        $em = $this
            ->getServiceLocator()
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
        
        $logger = $this
            ->getServiceLocator()
            ->get('Logger');
        
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
}