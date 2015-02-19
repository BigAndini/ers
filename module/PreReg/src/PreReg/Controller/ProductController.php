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
use Zend\Session\Container;

class ProductController extends AbstractActionController {
    public function indexAction()
    {
        $clearance = new Container('forrest');
        $clearance->getManager()->getStorage()->clear('forrest');
        $forrest = new Container('forrest');
        $forrest->trace = new \ArrayObject();
        
        $breadcrumb = new \ArrayObject();
        $breadcrumb->route = 'product';
        $breadcrumb->params = array();
        $breadcrumb->options = array();
        $forrest->trace->participant = $breadcrumb;
        
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $tmp = $em->getRepository("ersEntity\Entity\Product")
                ->findBy(
                        array(
                            'active' => 1,
                            'deleted' => 0,
                        )
                    );
        $products = array();
        foreach($tmp as $product) {
            if($product->getPrice()->getCharge() != null) {
                $products[] = $product;
            }
        }
        
        return new ViewModel(array(
            'products' => $products,
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
        
        $bc_participant = new \ArrayObject();
        $bc_participant->route = 'product';
        if($participant_id) {
            $bc_participant->params = array(
                'action' => 'edit',
                'product_id' => $product_id,
                'participant_id' => $participant_id
            );
            if($item_id) {
                $bc_participant->params['item_id'] = $item_id;    
            }
        } else {
            $bc_participant->params = array(
                'action' => 'add',
                'product_id' => $product_id,
            );
        }
        $bc_participant->options = array();
        $forrest = new Container('forrest');
        $forrest->trace->participant = $bc_participant;
        
        $bc_cart = new \ArrayObject();
        $bc_cart->route = 'product';
        $bc_cart->params = array();
        $bc_cart->options = array();
        $forrest->trace->cart = $bc_cart;
        
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $product = $em->getRepository("ersEntity\Entity\Product")->findOneBy(array('id' => $product_id));
        
        $form = new Form\ProductView();

        if($participant_id && $item_id) {
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
        
        $variants = $em->getRepository("ersEntity\Entity\ProductVariant")->findBy(array('Product_id' => $product_id));
        foreach($variants as $v) {
            $values = $em->getRepository("ersEntity\Entity\ProductVariantValue")->findBy(array('ProductVariant_id' => $v->getId()), array('ordering' => 'ASC'));
            foreach($values as $val) {
                $v->addProductVariantValue($val);
            }
        }
        $form->setVariants($variants);
        $form->get('submit')->setAttribute('value', 'Add to Cart');
        
        
        $question = 0;
        
        $session_cart = new Container('cart');
        $participant = '';
        $item = '';
        if($participant_id) { 
            $participant = $session_cart->order->getParticipantBySessionId($participant_id);
            if($item_id) {
                $item = $session_cart->order->getItem($participant_id, $item_id);
            }
        }
        
        $options = array();
        if(!$product->getPersonalized()) {
            $options[0] = 'do not assign this product';
        }
        foreach($session_cart->order->getParticipants() as $k => $v) {
            $selected = false;
            if($k == $participant_id) {
                $selected = true;
            }
            $options[] = array(
                'value' => $k,
                'label' => $v->getPrename().' '.$v->getSurname(),
                'selected' => $selected,
            );
        }
        
        if(count($options) <= 0 && $product->getPersonalized()) {
            $form->get('submit')->setAttribute('disabled', 'disabled');
        }
        
        $form->get('participant_id')->setAttribute('options', $options);
        
        return new ViewModel(array(
            'question' => $question,
            'participants' => $options,
            'product' => $product,
            'participant' => $participant,
            'item' => $item,
            'form' => $form,
        ));
    }
    
    public function editAction() {
        $viewModel = $this->addAction();
        $viewModel->setTemplate('pre-reg/product/edit');
        
        return $viewModel;
    }
    
    public function deleteAction() {
        $product_id = (int) $this->params()->fromRoute('product_id', 0);
        $participant_id = (int) $this->params()->fromRoute('participant_id', 0);
        $item_id = (int) $this->params()->fromRoute('item_id', 0);
        if (!$product_id || !$participant_id || !$item_id) {
            return $this->redirect()->toRoute('order');
        }
        
        $forrest = new Container('forrest');
        
        if($forrest->count() === 0) {
            $bc_product = new \ArrayObject();
            $bc_product->route = 'order';
            $bc_product->params = array();
            $bc_product->options = array();
            $forrest->trace->product = $bc_product;
        }

        $session_cart = new Container('cart');
        $participant = $session_cart->order->getParticipantBySessionId($participant_id);
        $item = $session_cart->order->getItem($participant_id, $item_id);
        
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
                
                $session_cart->order->removeItem($participant_id, $item_id);
            }

            $breadcrumb = $forrest->trace->product;
            return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
        }
        
        return array(
            'id'    => $product_id,
            'participant' => $participant,
            'item' => $item,
            'product' => $product,
            'forrest' => $forrest,
        );
    }
}