<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Catalogue\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Catalogue\Form;

class ProductController extends AbstractActionController {
    protected $table;
    
    public function getTable($name)
    {
        if (!isset($this->table[$name])) {
            $sm = $this->getServiceLocator();
            $className = "Catalogue\Model\\".$name."Table";
            $this->table[$name] = $sm->get($className);
            $this->table[$name]->setServiceLocator($sm);
        }
        return $this->table[$name];
    }
    public function indexAction()
    {
        return new ViewModel(array(
            'products' => $this->getTable('Product')->fetchAll('order ASC'),
        ));
    }
    
    public function viewAction() {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('product', array(
                'action' => 'index'
            ));
        }
        
        $product = $this->getTable('Product')->getById($id);
        
        $item = new \Catalogue\Model\Entity\Item();
        
        #$form = $this->getServiceLocator()->get('Form\ProductForm');
        $form = new Form\ProductViewForm();
        $url = $this->url()->fromRoute('cart', array('action' => 'add'));
        $form->setAttribute('action', $url);
        $variants = $this->getTable('ProductVariant')->getByField('Product_id', $id, 'order ASC');
        foreach($variants as $v) {
            $values = $this->getTable('ProductVariantValue')->getByField('ProductVariant_id', $v->id, 'order ASC');
            error_log('found '.count($values).' values for variant '.$v->name);
            $v->setValues($values);
        }
        $form->setVariants($variants);
        #$form->bind($item);
        $form->bind($product);
        $form->get('submit')->setAttribute('value', 'Add to Cart');
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($item->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $this->getTable('Item')->save($form->getData());

                // Redirect to question if the customer will go on shopping or 
                // head over to the checkout.
                return $this->redirect()->toRoute('cart', array(
                    'action' => 'question',
                ));
            }
        }
        
        return new ViewModel(array(
            'product' => $product,
            'form' => $form,
        ));
    }

}