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

class ProductVariantController extends AbstractActionController {
    protected $table;
    
    public function getTable($name)
    {
        if (!isset($this->table[$name])) {
            $sm = $this->getServiceLocator();
            $className = "Admin\Model".$name."Table";
            $this->table[$name] = $sm->get($className);
        }
        return $this->table[$name];
    }
    public function indexAction()
    {
        return new ViewModel(array(
            'productvariants' => $this->getTable('ProductVariant')->fetchAll(),
         ));
    }

    public function addAction()
    {
        $product_id = (int) $this->params()->fromRoute('id', 0);
        if (!$product_id) {
            return $this->redirect()->toRoute('admin/product');
        }
        
        #$form = new Form\ProductVariantForm();
        $form = $this->getServiceLocator()->get('Admin\Form\ProductVariantForm');
        $form->get('submit')->setValue('Add');
        $form->get('Product_id')->setValue($product_id);

        #$productvariant = new Entity\ProductVariant();
        #$form->bind($productvariant);
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $productvariant = new Entity\ProductVariant();
            $form->setInputFilter($productvariant->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $productvariant->exchangeArray($form->getData());
                $this->getTable('ProductVariant')->save($productvariant);

                // Redirect to list of productvariants
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
            'product_id' => $product_id,
            'form' => $form,                
        );
    }

    public function editAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/product-variant', array(
                'action' => 'add'
            ));
        }
        $productvariant = $this->getTable('ProductVariant')->getById($id);

        #$form  = new Form\ProductVariantForm();
        $form = $this->getServiceLocator()->get('Admin\Form\ProductVariantForm');
        $form->bind($productvariant);
        $form->get('submit')->setAttribute('value', 'Edit');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($productvariant->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $this->getTable('ProductVariant')->save($form->getData());

                // Redirect to list of productvariants
                return $this->redirect()->toRoute('admin/product');
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
                $this->getTable('ProductVariant')->removeById($id);
            }

            // Redirect to list of productvariants
            return $this->redirect()->toRoute('admin/product');
        }

        return array(
            'id'    => $id,
            'productvariant' => $this->getTable('ProductVariant')->getById($id),
        );
    }
}