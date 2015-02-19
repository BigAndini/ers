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

class ProductVariantController extends AbstractActionController 
{    
    /*public function indexAction()
    {
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        return new ViewModel(array(
            'productvariants' => $em->getRepository("ersEntity\Entity\ProductVariant")->findAll(),
        ));
    }*/

    public function addAction()
    {
        $product_id = (int) $this->params()->fromRoute('id', 0);
        if (!$product_id) {
            return $this->redirect()->toRoute('admin/product');
        }
        
        $form = new Form\ProductVariant();
        $form->get('submit')->setValue('Add');
        $form->get('Product_id')->setValue($product_id);
        $options['text'] = 'Text';
        $options['select'] = 'Select';
        $options['date'] = 'Date';
        $options['datetime'] = 'Datetime';
        $form->get('type')->setAttribute('options', $options);
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $productvariant = new Entity\ProductVariant();
            $form->setInputFilter($productvariant->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
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
                error_log(var_export($form->getMessages(), true));
            }
        }
        
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $product = $em->getRepository("ersEntity\Entity\Product")->findOneBy(array('id' => $product_id));
        
        $context = new Container('context');
        $context->route = 'admin/product';
        $context->params = array();
        $context->options = array();
        
        return array(
            'context' => $context,
            'product' => $product,
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

        #$form  = new Form\ProductVariant();
        $form = $this->getServiceLocator()->get('Admin\Form\ProductVariant');
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
        
        $product = $em->getRepository("ersEntity\Entity\Product")->findOneBy(array('id' => $productvariant->getProductId()));

        return array(
            'id' => $id,
            'product' => $product,
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
                $values = $em->getRepository("ersEntity\Entity\ProductVariantValue")
                        ->findBy(array('ProductVariant_id' => $productvariant->getId()));
                foreach($values as $value) {
                    $em->remove($value);
                }
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
        
        $productvariant = $em->getRepository("ersEntity\Entity\ProductVariant")
                        ->findOneBy(array('id' => $id));
        $product = $em->getRepository("ersEntity\Entity\Product")
                ->findOneBy(array('id' => $productvariant->getProductId()));
        
        return array(
            'id'    => $id,
            'product' => $product,
            'productvariant' => $productvariant,
        );
    }
}