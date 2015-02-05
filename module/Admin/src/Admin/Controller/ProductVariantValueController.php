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

class ProductVariantValueController extends AbstractActionController 
{    
    public function indexAction()
    {
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        return new ViewModel(array(
            'productvariants' => $em->getRepository("ersEntity\Entity\ProductVariantValue")->findAll(),
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
            'productvariant' => $em->getRepository("ersEntity\Entity\ProductVariantValue")->findOneBy(array('id' => $id)),
        ));
    }
    
    public function addAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/product-variant');
        }
        
        $form = new Form\ProductVariantValueForm;
        #$form = $this->getServiceLocator()->get('Admin\Form\ProductVariantValueForm');
        $form->get('submit')->setValue('Add');
        $form->get('ProductVariant_id')->setValue($id);
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $value = new Entity\ProductVariantValue();
            $form->setInputFilter($value->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $em = $this
                    ->getServiceLocator()
                    ->get('Doctrine\ORM\EntityManager');

                $value->populate($form->getData());
                $productvariant = $em->getRepository("ersEntity\Entity\Product")->findOneBy(array('id' => $value->getProductVariantId()));
                $value->setProductVariant($productvariant);
                
                $em->persist($value);
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
            'productvariant_id' => $id,
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
        
        $value = $em->getRepository("ersEntity\Entity\ProductVariantValue")->findOneBy(array('id' => $id));
        #$value = $this->getTable('ProductVariantValue')->getById($id);

        #$form  = new Form\ProductVariantValueForm();
        $form = $this->getServiceLocator()->get('Admin\Form\ProductVariantValueForm');
        $form->bind($value);
        $form->get('submit')->setAttribute('value', 'Edit');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($value->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                #$this->getTable('ProductVariantValue')->save($form->getData());       

                $value->populate($form->getData());
                
                $em->persist($value);
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
                $value = $em->getRepository("ersEntity\Entity\ProductVariantValue")
                        ->findOneBy(array('id' => $id));
                $em->remove($value);
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
            'productvariant' => $em->getRepository("ersEntity\Entity\ProductVariantValue")
                        ->findOneBy(array('id' => $id)),
        );
    }
}