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

class PriceLimitController extends AbstractActionController {
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
            'limits' => $this->getTable('PriceLimit')->fetchAll(),
         ));
    }

    public function addAction()
    {
        $form = $this->getServiceLocator()->get('Form\PriceLimitForm');
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
                return $this->redirect()->toRoute('admin/price-limit');
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
            return $this->redirect()->toRoute('admin/price-limit', array(
                'action' => 'add'
            ));
        }
        $product = $this->getTable('Product')->getById($id);

        $form = $this->getServiceLocator()->get('Form\PriceLimitForm');
        $form->bind($product);
        $form->get('submit')->setAttribute('value', 'Edit');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($product->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $this->getTable('Product')->save($form->getData());

                // Redirect to list of products
                return $this->redirect()->toRoute('admin/price-limit');
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
            return $this->redirect()->toRoute('admin/price-limit', array(
                'action' => 'add'
            ));
        }
        $product = $this->getTable('Product')->getById($id);

        $form = $this->getServiceLocator()->get('Form\PriceLimitForm');
        $form->bind($product);
        $form->get('submit')->setAttribute('value', 'Copy');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($product->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $new_id = $this->getTable('PriceLimit')->save($form->getData());
                error_log('saved price limit: '.$id);

                // Redirect to list of products
                return $this->redirect()->toRoute('admin/price-limit');
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
            return $this->redirect()->toRoute('admin/price-limit');
        }

        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('del', 'No');

            if ($del == 'Yes') {
                $id = (int) $request->getPost('id');
                $this->getTable('PriceLimit')->removeById($id);
            }

            // Redirect to list of products
            return $this->redirect()->toRoute('admin/price-limit');
        }

        return array(
            'id'    => $id,
            'pricelimit' => $this->getTable('PriceLimit')->getById($id),
        );
    }
}