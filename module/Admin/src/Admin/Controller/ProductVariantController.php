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
use Admin\Form;
use Zend\Form\Element;

class ProductVariantController extends AbstractActionController 
{    
    public function indexAction()
    {
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        return new ViewModel(array(
            'productvariants' => $em->getRepository("ersEntity\Entity\ProductVariant")->findAll(),
        ));
    }

    public function viewAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/product');
        }
        
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        return new ViewModel(array(
            'productvariant' => $em->getRepository("ersEntity\Entity\ProductVariant")->findOneBy(array('id' => $id)),
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
                #$productvariant->populate($form->getData());
                #$this->getTable('ProductVariant')->save($productvariant);

                $em = $this
                    ->getServiceLocator()
                    ->get('Doctrine\ORM\EntityManager');

                $productvariant->populate($form->getData());
                $product = $em->getRepository("ersEntity\Entity\Product")->findOneBy(array('id' => $productvariant->getProductId()));
                $productvariant->setProduct($product);
                
                $em->persist($productvariant);
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
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $productvariant = $em->getRepository("ersEntity\Entity\ProductVariant")->findOneBy(array('id' => $id));
        #$productvariant = $this->getTable('ProductVariant')->getById($id);

        #$form  = new Form\ProductVariantForm();
        $form = $this->getServiceLocator()->get('Admin\Form\ProductVariantForm');
        $form->bind($productvariant);
        $form->get('submit')->setAttribute('value', 'Edit');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($productvariant->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $productvariant = $form->getData();
                
                $em->persist($productvariant);
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
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('del', 'No');

            if ($del == 'Yes') {
                
                $id = (int) $request->getPost('id');
                $productvariant = $em->getRepository("ersEntity\Entity\ProductVariant")
                        ->findOneBy(array('id' => $id));
                $em->remove($productvariant);
                $em->flush();
            }

            $context = new Container('context');
            if(isset($context->route)) {
                return $this->redirect()->toRoute($context->route, $context->params, $context->options);
            } else {
                return $this->redirect()->toRoute('admin/product');
            }
        }
        
        return array(
            'id'    => $id,
            'productvariant' => $em->getRepository("ersEntity\Entity\ProductVariant")
                        ->findOneBy(array('id' => $id)),
        );
    }
}