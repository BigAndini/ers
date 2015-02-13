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
    /*protected $table;
    
    public function getTable($name)
    {
        if (!isset($this->table[$name])) {
            $sm = $this->getServiceLocator();
            $className = "PreReg\Model\\".$name."Table";
            $this->table[$name] = $sm->get($className);
            $this->table[$name]->setServiceLocator($sm);
        }
        return $this->table[$name];
    }*/
    public function indexAction()
    {
        # TODO: only fetch the ones that are active.
        #$products = $this->getTable('Product')->fetchAll('order ASC');
        
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
    
    public function viewAction() {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('product', array(
                'action' => 'index'
            ));
        }
        
        $context = new Container('context');
        $context->route = 'product';
        $context->params = array(
            'action' => 'view',
            'id' => $id,
        );
        $context->options = array();
        
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $product = $em->getRepository("ersEntity\Entity\Product")->findOneBy(array('id' => $id));
        #$product = $this->getTable('Product')->getById($id);
        
        #$form = $this->getServiceLocator()->get('Form\ProductForm');
        $form = new Form\ProductViewForm();
        $url = $this->url()->fromRoute('cart', array('action' => 'add'));
        $form->setAttribute('action', $url);
        
        #$variants = $this->getTable('ProductVariant')->getByField('Product_id', $id, 'order ASC');
        $variants = $em->getRepository("ersEntity\Entity\ProductVariant")->findBy(array('Product_id' => $id));
        foreach($variants as $v) {
            #$values = $this->getTable('ProductVariantValue')->getByField('ProductVariant_id', $v->getId(), 'order ASC');
            $values = $em->getRepository("ersEntity\Entity\ProductVariantValue")->findBy(array('ProductVariant_id' => $v->getId()), array('ordering' => 'ASC'));
            foreach($values as $val) {
                $v->addProductVariantValue($val);
            }
        }
        $form->setVariants($variants);
        $form->get('submit')->setAttribute('value', 'Add to Cart');
        
        
        $question = 0;
        
        $session_cart = new Container('cart');
        
        $options = array();
        if(!$product->getPersonalized()) {
            $options[0] = 'do not assign this product';
        }
        foreach($session_cart->order->getParticipants() as $k => $v) {
            $options[$k] = $v->getPrename().' '.$v->getSurname();
        }
        
        if(count($options) <= 0 && $product->getPersonalized()) {
            $form->get('submit')->setAttribute('disabled', 'disabled');
        }
        
        $form->get('participant_id')->setAttribute('options', $options);
        
        return new ViewModel(array(
            'question' => $question,
            'participants' => $options,
            'product' => $product,
            'form' => $form,
        ));
    }
}