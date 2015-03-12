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
use PreReg\Service;
use Zend\Session\Container;

class ProductController extends AbstractActionController {
    public function indexAction()
    {
        $forrest = new Service\BreadcrumbFactory();
        $forrest->reset();
        $forrest->set('participant', 'product');
        
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $tmp = $em->getRepository("ersEntity\Entity\Product")
            ->findBy(
                    array(
                        'active' => 1,
                        'visible' => 1,
                        'deleted' => 0,
                    ),
                    array(
                        'ordering' => 'ASC'
                    )
                );
        $products = array();
        foreach($tmp as $product) {
            if($product->getProductPrice()->getCharge() != null) {
                $products[] = $product;
            }
        }
        
        $agegroups = $em->getRepository("ersEntity\Entity\Agegroup")
                    ->findBy(array(), array('agegroup' => 'DESC'));
        $deadlineService = new Service\DeadlineService();
        $deadlines = $em->getRepository("ersEntity\Entity\Deadline")
            ->findAll();
        $deadlineService->setDeadlines($deadlines);
        $deadline = $deadlineService->getDeadline();
        
        $cartContainer = new Container('cart');
        
        return new ViewModel(array(
            'products' => $products,
            'agegroups' => $agegroups,
            'deadline' => $deadline,
            'order' => $cartContainer->order,
        ));
    }
    
    public function addAction() {
        $product_id = (int) $this->params()->fromRoute('product_id', 0);
        $participant_id = (int) $this->params()->fromRoute('participant_id', 0);
        $item_id = (int) $this->params()->fromRoute('item_id', 0);
        if (!$product_id) {
            return $this->redirect()->toRoute('product', array(
                'action' => 'index'
            ));
        }
        
        $forrest = new Service\BreadcrumbFactory();
        if(!$forrest->exists('product')) {
            $forrest->set('product', 'product');
        }
        
        $params = array();
        $params2 = array();
        if(is_numeric($participant_id)) {
            #$params = $forrest->get('product')->params;
            $params['action'] = 'add';
            $params['product_id'] = $product_id;
            $params['participant_id'] = $participant_id;
            $params2 = $params;
            
            if($item_id) {
                $params['action'] = 'edit';
                # When we're in edit mode there may not be a item_id from 
                # returning back from participant (#114)
                $params2 = $params;
                $params2['item_id'] = $item_id;    
            }
            $forrest->set('participant', 'product', $params);
        } else {
            $forrest->set('participant', 'product',
                    array(
                        'action' => 'add',
                        'product_id' => $product_id
                    )
                );
        }
        
        if(!$forrest->exists('cart')) {
            $forrest->set('cart', 'product');
        }
        $forrest->set('cart', 'product', $params2);
        $forrest->set('bc_stay', 'product', $params2);
        
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $product = $em->getRepository("ersEntity\Entity\Product")->findOneBy(array('id' => $product_id));
        
        $form = new Form\ProductView();

        if(isset($participant_id) && is_numeric($participant_id) && $item_id) {
            $url = $this->url()->fromRoute('cart', 
                    array(
                        'action' => 'add', 
                        'participant_id' => $participant_id, 
                        'item_id' => $item_id
                    ));
        } else {
            $url = $this->url()->fromRoute('cart', array('action' => 'add'));
        }
        $form->setAttribute('action', $url);
        
        $variants = $em->getRepository("ersEntity\Entity\ProductVariant")
                ->findBy(array('Product_id' => $product_id));
        foreach($variants as $v) {
            $values = $em->getRepository("ersEntity\Entity\ProductVariantValue")
                    ->findBy(array('ProductVariant_id' => $v->getId()), array('ordering' => 'ASC'));
            foreach($values as $val) {
                $v->addProductVariantValue($val);
            }
        }
        $form->setVariants($variants);
        $form->get('submit')->setAttribute('value', 'Add to Cart');
        if($product->getPersonalized()) {
            $form->get('participant_id')->setOptions(array('label' => 'you need to assign this ticket to a person'));
        } else {
            $form->get('participant_id')->setOptions(array('label' => 'assign this ticket to a person'));
        }
        
        $cartContainer = new Container('cart');
        $participant = null;
        $item = '';
        if(is_numeric($participant_id)) { 
            $participant = $cartContainer->order->getParticipantBySessionId($participant_id);
            if($item_id) {
                $item = $cartContainer->order->getItem($participant_id, $item_id);
                $cartContainer->editItem = $item;
                #error_log('saved editItem '.$item->getSessionId());
            }
        }
        
        $options = array();
        if(!$product->getPersonalized()) {
            $options[0] = 'do not assign this product';
        }
        foreach($cartContainer->order->getParticipants() as $k => $v) {
            $disabled = false;
            if($v->getFirstname() == '') {
                $disabled = true;
            }
            if($v->getSurname() == '') {
                $disabled = true;
            }
            if($v->getBirthday() == null) {
                $disabled = true;
            }
            $selected = false;
            if($k == $participant_id) {
                $selected = true;
            }
            $options[] = array(
                'value' => $k,
                'label' => $v->getFirstname().' '.$v->getSurname(),
                'selected' => $selected,
                'disabled' => $disabled,
            );
        }
        
        if(count($options) <= 0 && $product->getPersonalized()) {
            $form->get('submit')->setAttribute('disabled', 'disabled');
        }
        
        $form->get('participant_id')->setAttribute('options', $options);

        $breadcrumb = $forrest->get('product');
        
        $chooser = $cartContainer->chooser;
        $cartContainer->chooser = false;

        $agegroups = $em->getRepository("ersEntity\Entity\Agegroup")
                    ->findBy(array(), array('agegroup' => 'DESC'));
        
        $agegroupService = new Service\AgegroupService();
        $agegroupService->setAgegroups($agegroups);
        $agegroup = $agegroupService->getAgegroupByUser($participant);
        
        $deadlineService = new Service\DeadlineService();
        $deadlines = $em->getRepository("ersEntity\Entity\Deadline")
                    ->findAll();
        $deadlineService->setDeadlines($deadlines);
        $deadline = $deadlineService->getDeadline();
        
        return new ViewModel(array(
            #'participants' => $options,
            'product' => $product,
            'participant' => $participant,
            'item' => $item,
            'form' => $form,
            'breadcrumb' => $breadcrumb,
            'bc_stay' => $forrest->get('bc_stay'),
            'chooser' => $chooser,
            'agegroups' => $agegroups,
            'deadline' => $deadline,
            'agegroup' => $agegroup,
        ));
    }
    
    public function editAction() {
        $forrest = new Service\BreadcrumbFactory();
        if(!$forrest->exists('product')) {
            $forrest->set('product', 'product', array('action' => 'edit'));
        }
        $forrest->set('participant', 'product', array('action' => 'edit'));
        
        $viewModel = $this->addAction();
        $viewModel->setTemplate('pre-reg/product/edit');
        
        return $viewModel;
    }
    
    public function deleteAction() {
        $forrest = new Service\BreadcrumbFactory();
        
        if($forrest->exists('product')) {
            $forrest->set('product', 'order');
        }
        
        $product_id = (int) $this->params()->fromRoute('product_id', 0);
        $participant_id = (int) $this->params()->fromRoute('participant_id', 0);
        $item_id = (int) $this->params()->fromRoute('item_id', 0);
        if (!is_numeric($product_id) || !is_numeric($participant_id) || !is_numeric($item_id)) {
            $breadcrumb = $forrest->get('product');
            return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
        }
        
        $cartContainer = new Container('cart');
        $participant = $cartContainer->order->getParticipantBySessionId($participant_id);
        $item = $cartContainer->order->getItem($participant_id, $item_id);
        
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $product = $em->getRepository("ersEntity\Entity\Product")->findOneBy(array('id' => $product_id));
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('del', 'No');

            if ($del == 'Yes') {
                $participant_id = (int) $request->getPost('participant_id');
                $item_id = (int) $request->getPost('item_id');
                
                $package = $cartContainer->order->getPackageByParticipantSessionId($participant_id);
                
                $cartContainer->order->removeItem($package->getSessionId(), $item_id);
            }

            $breadcrumb = $forrest->get('product');
            return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
        }
        
        return new ViewModel(array(
            'id'    => $product_id,
            'participant' => $participant,
            'item' => $item,
            'product' => $product,
            'breadcrumb' => $forrest->get('product'),
        ));
    }
}