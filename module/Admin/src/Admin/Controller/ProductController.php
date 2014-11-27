<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Admin\Model\Entity;
#use RegistrationSystem\Form\UserForm;
use Admin\Form;
use Zend\Form\Element;

class ProductController extends AbstractActionController {
    protected $table;
    
    public function getTable($name)
    {
        if (!isset($this->table[$name])) {
            $sm = $this->getServiceLocator();
            $className = "Admin\Model\\".$name."Table";
            $this->table[$name] = $sm->get($className);
            $this->table[$name]->setServiceLocator($sm);
        }
        return $this->table[$name];
    }
    public function indexAction()
    {
        return new ViewModel(array(
            'products' => $this->getTable('Product')->fetchAll('order ASC'),
            'prices' => $this->getTable('ProductPrice')->fetchAll(),
            'variants' => $this->getTable('ProductVariant')->fetchAll('order ASC'),
         ));
    }

    public function addAction()
    {
        $form = $this->getServiceLocator()->get('Admin\Form\ProductForm');
        #$form = new Form\ProductForm();
        $form->get('submit')->setValue('Add');
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $product = new Entity\Product();
            $form->setInputFilter($product->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $product->exchangeArray($form->getData());
                $this->getTable('Product')->save($product);

                // Redirect to list of products
                return $this->redirect()->toRoute('admin/product');
            } else {
                $messages = $form->getMessages();
                error_log('got '.count($messages).' messages.');
                foreach($messages as $m) {
                    error_log($m);
                }
            }
        }
        
        return array(
            'form' => $form,                
        );
    }

    public function editAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/product', array(
                'action' => 'add'
            ));
        }
        $product = $this->getTable('Product')->getById($id);

        $form = $this->getServiceLocator()->get('Admin\Form\ProductForm');
        error_log('personalized: '.$product->personalized);
        $form->bind($product);
        $form->get('submit')->setAttribute('value', 'Edit');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($product->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $this->getTable('Product')->save($form->getData());

                // Redirect to list of products
                return $this->redirect()->toRoute('admin/product');
            }
        }

        return array(
            'id' => $id,
            'form' => $form,
        );
    }
    
    public function copyAction()
    {   
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/product', array(
                'action' => 'add'
            ));
        }
        $product = $this->getTable('Product')->getById($id);

        $form = $this->getServiceLocator()->get('Admin\Form\ProductForm');
        $form->bind($product);
        $form->get('submit')->setAttribute('value', 'Copy');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($product->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $new_id = $this->getTable('Product')->save($form->getData());
                error_log('saved product: '.$id);
                
                # foreach ProductPrices where Product_id = copied Product_id do:
                # copy ProductVariant to new Product_id
                $ProductPrices = $this->getTable('ProductPrice')->getByField('Product_id', $id);
                foreach($ProductPrices as $price) {
                    $price->id = 0;
                    $price->Product_id = $new_id;
                    $this->getTable('ProductPrice')->save($price);
                }
                
                # foreach ProductVariants where Product_id = copied Product_id 
                # do: copy ProductVariant to new Product_id (with ProductVariantValues)
                $ProductVariants = $this->getTable('ProductVariant')->getByField('Product_id', $id);
                foreach($ProductVariants as $variant) {
                    $variant->id = 0;
                    $variant->Product_id = $new_id;
                    $new_v_id = $this->getTable('ProductVariant')->save($variant);
                    $ProductVariantValues = $this->getTable('ProductVariantValue')->getByField('ProductVariant_id', $variant->id);
                    foreach($ProductVariantValues as $value) {
                        $value->id = 0;
                        $value->ProductVariant_id = $new_v_id;
                        $this->getTable('ProductVariantValue')->save($value);
                    }
                }

                // Redirect to list of products
                return $this->redirect()->toRoute('admin/product');
            } else {
                $messages = $form->getMessages();
                error_log('got '.count($messages).' messages.');
                foreach($messages as $message) {
                    foreach($message as $m) {
                        error_log($m);
                    }
                }
            }
        }

        return array(
            'id' => $id,
            'form' => $form,
        );
    }

    public function deleteAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/product');
        }

        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('del', 'No');

            if ($del == 'Yes') {
                $id = (int) $request->getPost('id');
                $ProductPrices = $this->getTable('ProductPrice')->getByField('Product_id', $id);
                foreach($ProductPrices as $price) {
                    $this->getTable('ProductPrice')->removeById($price->id);
                }
                $ProductVariants = $this->getTable('ProductVariant')->getByField('Product_id', $id);
                foreach($ProductVariants as $variant) {
                    $ProductVariantValues = $this->getTable('ProductVariantValue')->getByField('ProductVariant_id', $variant->id);
                    foreach($ProductVariantValues as $value) {
                        $this->getTable('ProductVariantValue')->removeById($value->id);
                    }
                    $this->getTable('ProductVariant')->removeById($variant->id);
                }
                $this->getTable('Product')->removeById($id);
            }

            // Redirect to list of products
            return $this->redirect()->toRoute('admin/product');
        }

        return array(
            'id'    => $id,
            'product' => $this->getTable('Product')->getById($id),
        );
    }
}