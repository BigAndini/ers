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
use Zend\Session\Container;
#use RegistrationSystem\Form\UserForm;
use Admin\Form;
use Zend\Form\Element;

class ProductPriceController extends AbstractActionController {
    /*protected $table;
    
    public function getTable($name)
    {
        if (!isset($this->table[$name])) {
            $sm = $this->getServiceLocator();
            $className = "Admin\Model\\".$name."Table";
            $this->table[$name] = $sm->get($className);
        }
        return $this->table[$name];
    }*/
    public function indexAction()
    {
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        return new ViewModel(array(
            'productprices' => $em->getRepository("ersEntity\Entity\ProductPrice")->findAll(),
         ));
    }

    public function addAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/product');
        }
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        #$product = $this->getTable('Product')->getById($id);
        $productprice = new Entity\ProductPrice();
        $productprice->setProductId($id);

        #$form = $this->getServiceLocator()->get('Form\ProductPriceForm');
        $form = new Form\ProductPriceForm();
        
        $deadlines = $em->getRepository("ersEntity\Entity\Deadline")
                ->findAll();
        $options = array();
        foreach($deadlines as $deadline) {
            
            $options[] = array(
                'value' => $deadline->getId(),
                'label' => 'Deadline: '.$deadline->getDeadline()->format('Y-m-d H:i:s'),
                'selected' => false,
            );
        }
        $form->get('Deadline_id')->setAttribute('options', $options);
        
        $form->bind($productprice);
        
        $form->get('submit')->setValue('Add');
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $productprice = new Entity\ProductPrice();
            
            $form->setInputFilter($productprice->getInputFilter());
            $form->setData($request->getPost());
            
            if ($form->isValid()) {
                $productprice = $form->getData();
                
                $deadline = $em->getRepository("ersEntity\Entity\Deadline")
                    ->findOneBy(array('id' => $productprice->getDeadlineId()));
                $productprice->setDeadline($deadline);
                
                $product = $em->getRepository("ersEntity\Entity\Product")
                    ->findOneBy(array('id' => $productprice->getProductId()));
                $productprice->setProduct($product);
                
                
                $em->persist($productprice);
                $em->flush();
                
                $context = new Container('context');
                if(isset($context->route)) {
                    return $this->redirect()->toRoute($context->route, $context->params, $context->options);
                } else {
                    return $this->redirect()->toRoute('admin/product');
                }
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
        
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $productprice = $em->getRepository("ersEntity\Entity\ProductPrice")
                ->findOneBy(array('id' => $id));

        $form = new Form\ProductPriceForm();
        
        $form->bind($productprice);
        
        $deadlines = $em->getRepository("ersEntity\Entity\Deadline")
                ->findAll();
        $options = array();
        foreach($deadlines as $deadline) {
            $options[] = array(
                'value' => $deadline->getId(),
                'label' => 'Deadline: '.$deadline->getDeadline()->format('Y-m-d H:i:s'),
                'selected' => false,
            );
        }
        $form->get('Deadline_id')->setAttribute('options', $options);
        
        
        $form->get('submit')->setAttribute('value', 'Edit');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($productprice->getInputFilter());
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $em->persist($form->getData());
                $em->flush();

                $context = new Container('context');
                if(isset($context->route)) {
                    return $this->redirect()->toRoute($context->route, $context->params, $context->options);
                } else {
                    return $this->redirect()->toRoute('admin/product');
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
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('del', 'No');

            error_log('In POST');
            if ($del == 'Yes') {
                
                $id = (int) $request->getPost('id');
                $productprice = $em->getRepository("ersEntity\Entity\ProductPrice")
                        ->findOneBy(array('id' => $id));
                $em->remove($productprice);
                $em->flush();
            }

            $context = new Container('context');
            if(isset($context->route)) {
                return $this->redirect()->toRoute($context->route, $context->params, $context->options);
            } else {
                return $this->redirect()->toRoute('admin/product');
            }
        }

        $productprice = $em->getRepository("ersEntity\Entity\ProductPrice")
                        ->findOneBy(array('id' => $id));
        
        error_log(var_export($productprice, true));
        
        return array(
            'id'    => $id,
            'price' => $productprice,
        );
    }
}