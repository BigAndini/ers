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

class ProductPriceController extends AbstractActionController {
    protected $table;
    
    public function getTable($name)
    {
        if (!isset($this->table[$name])) {
            $sm = $this->getServiceLocator();
            $className = "Admin\Model\\".$name."Table";
            $this->table[$name] = $sm->get($className);
        }
        return $this->table[$name];
    }
    public function indexAction()
    {
        return new ViewModel(array(
            'productprices' => $this->getTable('ProductPrice')->fetchAll(),
         ));
    }

    public function addAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/product');
        }
        
        #$product = $this->getTable('Product')->getById($id);
        $productprice = new Entity\ProductPrice();
        $productprice->Product_id = $id;

        #$form = $this->getServiceLocator()->get('Form\ProductPriceForm');
        $form = new Form\ProductPriceForm();
        $form->bind($productprice);
        
        $form->get('submit')->setValue('Add');
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $productprice = new Entity\ProductPrice();
            
            $form->setInputFilter($productprice->getInputFilter());
            $form->setData($request->getPost());
            
            if ($form->isValid()) {
                $productprice->exchangeArray($form->getData());
                $this->getTable('ProductPrice')->save($productprice);

                // Redirect to list of productprices
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

    public function editAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/product-price', array(
                'action' => 'add'
            ));
        }
        $productprice = $this->getTable('ProductPrice')->getById($id);

        #$form = $this->getServiceLocator()->get('Form\ProductPriceForm');
        $form = new Form\ProductPriceForm();
        $form->bind($productprice);
        $form->get('submit')->setAttribute('value', 'Edit');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($productprice->getInputFilter());
            $form->setData($request->getPost());
            error_log('IN POST');

            if ($form->isValid()) {
                foreach($form->getData() as $k => $v) {
                    error_log('      '.$k.' '.$v);
                }
                $this->getTable('ProductPrice')->save($form->getData());

                // Redirect to list of productprices
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

            error_log('In POST');
            if ($del == 'Yes') {
                error_log('delete: yes');
                $id = (int) $request->getPost('id');
                $this->getTable('ProductPrice')->removeById($id);
            }

            // Redirect to list of productprices
            return $this->redirect()->toRoute('admin/product');
        } else {
            error_log('No POST');
        }

        return array(
            'id'    => $id,
            'price' => $this->getTable('ProductPrice')->getById($id),
        );
    }
}