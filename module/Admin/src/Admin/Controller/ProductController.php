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
    protected $table;
    
    /*public function getTable($name)
    {
        if (!isset($this->table[$name])) {
            $sm = $this->getServiceLocator();
            $className = "Admin\Model\\".$name."Table";
            $this->table[$name] = $sm->get($className);
            $this->table[$name]->setServiceLocator($sm);
        }
        return $this->table[$name];
    }*/
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
        /*return new ViewModel(array(
            'products' => $this->getTable('Product')->fetchAll('order ASC'),
            'prices' => $this->getTable('ProductPrice')->fetchAll(),
            'variants' => $this->getTable('ProductVariant')->fetchAll('order ASC'),
         ));*/
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
                $product->populate($form->getData());
                
                $em = $this
                    ->getServiceLocator()
                    ->get('Doctrine\ORM\EntityManager');
                
                $tax = $em->getRepository("ersEntity\Entity\Tax")->findOneBy(array('id' => $product->getTaxId()));
                $product->setTax($tax);
                
                $em->persist($product);
                $em->flush();

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
        
        /*$variants = $em->getRepository("ersEntity\Entity\ProductVariant")
                ->findBy(array('Product_id' => $id));
        $product->setProductVariants($variants);

        $prices = $em->getRepository("ersEntity\Entity\ProductPrice")
                ->findBy(array('Product_id' => $id));
        $product->setProductPrices($prices);*/
        
        $context = new Container('context');
        $context->route = 'admin/product';
        $context->params = array('action' => 'view', 'id' => $id);
        $context->options = array();
        
        return array(
            'product' => $product,
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
    
    private function copyProductPrices($src_id, $dst_id) {
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $ProductPrices = $em->getRepository("ersEntity\Entity\ProductPrice")
                ->findBy(array('Product_id' => $src_id));
        # foreach ProductPrices where Product_id = copied Product_id do:
        # copy ProductVariant to new Product_id
        foreach($ProductPrices as $price) {
            $price->setId(null);
            $price->setProductId($dst_id);
            #$this->getTable('ProductPrice')->save($price);
            $em->persist($price);
        }
        $em->flush();
    }
    
    private function copyProductVariants($src_id, $dst_id) {
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        # foreach ProductVariants where Product_id = copied Product_id 
        # do: copy ProductVariant to new Product_id (with ProductVariantValues)
        $ProductVariants = $em->getRepository("ersEntity\Entity\ProductVariant")
                ->findBy(array('Product_id' => $src_id));
        foreach($ProductVariants as $variant) {
            $variant->setId(null);
            $variant->ProductId($dst_id);
            $em->persist($variant);
            $em->flush();
            $variant_id = $variant->getId();
            #$ProductVariantValues = $this->getTable('ProductVariantValue')->getByField('ProductVariant_id', $variant->id);
            $ProductVariantValues = $em->getRepository("ersEntity\Entity\ProductVariantValue")
                    ->findBy(array('ProductVariant_id' => $variant->getId()));
            foreach($ProductVariantValues as $value) {
                $value->setId(null);
                $value->setProductVariantId($variant_id);
                $em->persist($value);
            }
            $em->flush();
        }
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