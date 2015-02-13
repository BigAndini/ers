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

class ProductController extends AbstractActionController {
    public function indexAction()
    {
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $products = $em->getRepository("ersEntity\Entity\Product")->findBy(array(), array('ordering' => 'ASC'));
        
        $context = new Container('context');
        $context->route = 'admin/product';
        $context->params = array();
        $context->options = array();
        
        return new ViewModel(array(
            'products' => $products,
            ));
    }

    public function addAction()
    {
        $form = $this->getServiceLocator()->get('Admin\Form\ProductForm');
        #$form = new Form\ProductForm();
        $form->get('submit')->setValue('Add');
        
        $product = new Entity\Product();
        #$form->bind($product);
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($product->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {                
                $product->populate($form->getData());
                
                $em = $this
                    ->getServiceLocator()
                    ->get('Doctrine\ORM\EntityManager');
                
                $tax = $em->getRepository("ersEntity\Entity\Tax")->findOneBy(array('id' => $product->getTaxId()));
                $product->setTax($tax);
                
                $em->persist($product);
                $em->flush();

                return $this->redirect()->toRoute('admin/product');
            } else {
                error_log(var_export($form->getMessages()));
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
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $product = $em->getRepository("ersEntity\Entity\Product")->findOneBy(array('id' => $id));

        $form = $this->getServiceLocator()->get('Admin\Form\ProductForm');
        $form->bind($product);
        $form->get('submit')->setAttribute('value', 'Edit');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($product->getInputFilter());
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

    public function viewAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/product', array(
                'action' => 'add'
            ));
        }
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $product = $em->getRepository("ersEntity\Entity\Product")
                ->findOneBy(array('id' => $id));
        
        $context = new Container('context');
        $context->route = 'admin/product';
        $context->params = array('action' => 'view', 'id' => $id);
        $context->options = array();
        
        return array(
            'product' => $product,
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
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $old_product = $em->getRepository("ersEntity\Entity\Product")->findOneBy(array('id' => $id));
        
        $product = clone $old_product;

        $form = $this->getServiceLocator()->get('Admin\Form\ProductForm');
        $form->bind($product);
        $form->get('submit')->setAttribute('value', 'Copy');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($product->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $em->persist($product);
                $em->flush();
                $new_id = $product->getId();
                
                #$this->copyProductPrices($id, $new_id);   
                #$this->copyProductVariants($id, $new_id);

                $context = new Container('context');
                if(isset($context->route)) {
                    return $this->redirect()->toRoute($context->route, $context->params, $context->options);
                } else {
                    return $this->redirect()->toRoute('admin/product');
                }
            } else {
                error_log(var_export($form->getMessages()));
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
        $Product = $em->getRepository("ersEntity\Entity\Product")
                ->findOneBy(array('id' => $id));

        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('del', 'No');

            if ($del == 'Yes') {
                $id = (int) $request->getPost('id');
                $Product = $em->getRepository("ersEntity\Entity\Product")
                    ->findOneBy(array('id' => $id));
                
                $this->removeProductPrices($Product);
                $this->removeProductVariants($Product);
                
                $em->remove($Product);
                $em->flush();
            }

            return $this->redirect()->toRoute('admin/product');
        }

        return array(
            'id'    => $id,
            'product' => $Product,
        );
    }
    private function removeProductPrices(Entity\Product $Product) {
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $ProductPrices = $em->getRepository("ersEntity\Entity\ProductPrice")
                ->findBy(array('Product_id' => $Product->getId()));
        foreach($ProductPrices as $price) {
            $em->remove($price);
        }
    }
    private function removeProductVariants(Entity\Product $Product) {
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $ProductVariants = $em->getRepository("ersEntity\Entity\ProductVariant")
                ->findBy(array('Product_id' => $Product->getId()));
        foreach($ProductVariants as $variant) {
            $ProductVariantValues = $em->getRepository("ersEntity\Entity\ProductVariantValue")
                    ->findBy(array('ProductVariant_id' => $variant->getId()));
            foreach($ProductVariantValues as $value) {
                $em->remove($value);
            }
            $em->remove($variant);
        }
    }
}