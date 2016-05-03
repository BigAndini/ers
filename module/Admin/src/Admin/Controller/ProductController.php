<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use ErsBase\Entity;
use Zend\Session\Container;
use Admin\Form;
use ErsBase\Service;

class ProductController extends AbstractActionController {
    public function indexAction()
    {
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $products = $em->getRepository('ErsBase\Entity\Product')->findBy(array(), array('position' => 'ASC'));
        
        $forrest = new Service\BreadcrumbService();
        $forrest->set('product', 'admin/product');
        $forrest->set('product-variant', 'admin/product');
        $forrest->set('product-variant-value', 'admin/product');
        $forrest->set('product-price', 'admin/product');
        
        return new ViewModel(array(
            'products' => $products,
        ));
    }

    public function addAction()
    {
        $form = $this->getServiceLocator()->get('Admin\Form\Product');
        #$form = new Form\Product();
        $form->get('submit')->setValue('Add');
        
        $product = new Entity\Product();
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            #$form->setInputFilter($product->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {                
                $product->populate($form->getData());
                
                $em = $this->getServiceLocator()
                    ->get('Doctrine\ORM\EntityManager');
                
                $tax = $em->getRepository('ErsBase\Entity\Tax')->findOneBy(array('id' => $product->getTaxId()));
                $product->setTax($tax);
                
                $em->persist($product);
                $em->flush();

                return $this->redirect()->toRoute('admin/product');
            } else {
                $logger = $this->getServiceLocator()->get('Logger');
                $logger->warn($form->getMessages());
            }
        }
        
        return new ViewModel(array(
            'form' => $form,                
        ));
    }
    
    public function editAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/product', array(
                'action' => 'add'
            ));
        }
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $product = $em->getRepository('ErsBase\Entity\Product')->findOneBy(array('id' => $id));

        $form = $this->getServiceLocator()
                ->get('Admin\Form\Product');
        $form->bind($product);
        $form->get('submit')->setAttribute('value', 'Edit');

        $request = $this->getRequest();
        if ($request->isPost()) {
            #$form->setInputFilter($product->getInputFilter());
            $form->setData($request->getPost());
            
            if ($form->isValid()) {
                $em->persist($form->getData());
                $em->flush();

                $forrest = new Service\BreadcrumbService();
                if(!$forrest->exists('product')) {
                    $forrest->set('product', 'admin/product');
                }
                $breadcrumb = $forrest->get('product');
                return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
            }
        }

        return new ViewModel(array(
            'id' => $id,
            'form' => $form,
        ));
    }

    public function viewAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/product', array(
                'action' => 'add'
            ));
        }
        
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $product = $em->getRepository('ErsBase\Entity\Product')
                ->findOneBy(array('id' => $id));
        
        $forrest = new Service\BreadcrumbService();
        $forrest->set('product', 'admin/product', array('action' => 'view', 'id' => $id));
        $forrest->set('product-package', 'admin/product', array('action' => 'view', 'id' => $id));
        $forrest->set('product-price', 'admin/product', array('action' => 'view', 'id' => $id));
        $forrest->set('product-variant', 'admin/product', array('action' => 'view', 'id' => $id));
        $forrest->set('product-variant-value', 'admin/product', array('action' => 'view', 'id' => $id));
        
        $deadlines = $em->getRepository('ErsBase\Entity\Deadline')
                ->findBy(array('price_change' => '1'), array('deadline' => 'ASC'));
        $agegroups = $em->getRepository('ErsBase\Entity\Agegroup')
                ->findBy(array('price_change' => '1'), array('agegroup' => 'ASC'));
        
        return new ViewModel(array(
            'product' => $product,
            'agegroups' => $agegroups,
            'deadlines' => $deadlines,
        ));
    }   
    
    public function copyAction()
    {   
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/product', array(
                'action' => 'add'
            ));
        }
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $old_product = $em->getRepository('ErsBase\Entity\Product')->findOneBy(array('id' => $id));
        
        $product = clone $old_product;

        $form = $this->getServiceLocator()->get('Admin\Form\Product');
        $form->bind($product);
        $form->get('submit')->setAttribute('value', 'Copy');

        $request = $this->getRequest();
        if ($request->isPost()) {
            #$form->setInputFilter($product->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $em->persist($product);
                $em->flush();
                $new_id = $product->getId();
                
                #$this->copyProductPrices($id, $new_id);   
                #$this->copyProductVariants($id, $new_id);

                $forrest = new Service\BreadcrumbService();
                $breadcrumb = $forrest->get('product');
                return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
            } else {
                $logger = $this->getServiceLocator()->get('Logger');
                $logger->warn($form->getMessages());
            }
        }

        return new ViewModel(array(
            'id' => $id,
            'form' => $form,
        ));
    }

    public function deleteAction()
    {
        $logger = $this->getServiceLocator()->get('Logger');

        $breadcrumbService = new Service\BreadcrumbService();

        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/product');
        }

        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');

        $form = new Form\SimpleForm($em);
        $form->get('submit')->setAttributes(array(
            'value' => 'Delete',
            'class' => 'btn btn-danger',
        ));
        
        $product = $em->getRepository('ErsBase\Entity\Product')
                ->findOneBy(array('id' => $id));
        $form->bind($product);
        
        $items = $em->getRepository('ErsBase\Entity\Item')
                ->findBy(array('Product_id' => $id));

        if ($this->request->isPost()) {
            $form->setData($this->request->getPost());

            if ($form->isValid()) {
                
                $this->removeProductPrices($product);
                $this->removeProductVariants($product);
                
                $em->remove($product);
                $em->flush();

                $breadcrumb = $breadcrumbService->get('product');
                return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
            } else {
                $logger->warn($form->getMessages());
            }
        }

        return new ViewModel(array(
            'form' => $form,
            'items' => $items,
            'product' => $product,
            'breadcrumb' => $breadcrumbService->get('product'),
        ));

        
        
        
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/product');
        }
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $Product = $em->getRepository('ErsBase\Entity\Product')
                ->findOneBy(array('id' => $id));
        $Items = $em->getRepository('ErsBase\Entity\Item')
                ->findBy(array('Product_id' => $id));

        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('del', 'No');

            if ($del == 'Yes') {
                $id = (int) $request->getPost('id');
                $Product = $em->getRepository('ErsBase\Entity\Product')
                    ->findOneBy(array('id' => $id));
                
                $this->removeProductPrices($Product);
                $this->removeProductVariants($Product);
                
                $em->remove($Product);
                $em->flush();
            }

            return $this->redirect()->toRoute('admin/product');
        }

        return new ViewModel(array(
            'id'    => $id,
            'items' => $Items,
            'product' => $Product,
        ));
    }
    
    private function removeProductPrices(Entity\Product $Product) {
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $ProductPrices = $em->getRepository('ErsBase\Entity\ProductPrice')
                ->findBy(array('Product_id' => $Product->getId()));
        foreach($ProductPrices as $price) {
            $em->remove($price);
        }
    }
    private function removeProductVariants(Entity\Product $Product) {
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $ProductVariants = $em->getRepository('ErsBase\Entity\ProductVariant')
                ->findBy(array('Product_id' => $Product->getId()));
        foreach($ProductVariants as $variant) {
            $ProductVariantValues = $em->getRepository('ErsBase\Entity\ProductVariantValue')
                    ->findBy(array('ProductVariant_id' => $variant->getId()), array('position' => 'ASC'));
            foreach($ProductVariantValues as $value) {
                $em->remove($value);
            }
            $em->remove($variant);
        }
    }
    
    public function addLogoAction() {
        
    }
    public function editLogoAction() {
        
    }
    public function deleteLogoAction() {
        
    }
}