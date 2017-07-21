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
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $products = $entityManager->getRepository('ErsBase\Entity\Product')->findBy(array(), array('position' => 'ASC'));
        
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
                
                $entityManager = $this->getServiceLocator()
                    ->get('Doctrine\ORM\EntityManager');
                
                $tax = $entityManager->getRepository('ErsBase\Entity\Tax')->findOneBy(array('id' => $product->getTaxId()));
                $product->setTax($tax);
                
                $entityManager->persist($product);
                $entityManager->flush();

                return $this->redirect()->toRoute('admin/product');
            }
            $logger = $this->getServiceLocator()->get('Logger');
            $logger->warn($form->getMessages());
        }
        
        return new ViewModel(array(
            'form' => $form,                
        ));
    }
    
    public function editAction()
    {
        $productId = (int) $this->params()->fromRoute('id', 0);
        if (!$productId) {
            return $this->redirect()->toRoute('admin/product', array(
                'action' => 'add'
            ));
        }
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $product = $entityManager->getRepository('ErsBase\Entity\Product')->findOneBy(array('id' => $productId));

        $form = $this->getServiceLocator()
                ->get('Admin\Form\Product');
        $form->bind($product);
        $form->get('submit')->setAttribute('value', 'Edit');

        $request = $this->getRequest();
        if ($request->isPost()) {
            #$form->setInputFilter($product->getInputFilter());
            $form->setData($request->getPost());
            
            if ($form->isValid()) {
                #$entityManager->persist($form->getData());
                if($product->getDeleted() == '') {
                    $product->setDeleted(0);
                }
                $entityManager->persist($product);
                $entityManager->flush();

                $forrest = new Service\BreadcrumbService();
                if(!$forrest->exists('product')) {
                    $forrest->set('product', 'admin/product');
                }
                $breadcrumb = $forrest->get('product');
                return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
            }
        }

        return new ViewModel(array(
            'id' => $productId,
            'form' => $form,
        ));
    }

    public function viewAction()
    {
        $productId = (int) $this->params()->fromRoute('id', 0);
        if (!$productId) {
            return $this->redirect()->toRoute('admin/product', array(
                'action' => 'add'
            ));
        }
        
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $product = $entityManager->getRepository('ErsBase\Entity\Product')
                ->findOneBy(array('id' => $productId));
        
        $forrest = new Service\BreadcrumbService();
        $forrest->set('product', 'admin/product', array('action' => 'view', 'id' => $productId));
        $forrest->set('product-package', 'admin/product', array('action' => 'view', 'id' => $productId));
        $forrest->set('product-price', 'admin/product', array('action' => 'view', 'id' => $productId));
        $forrest->set('product-variant', 'admin/product', array('action' => 'view', 'id' => $productId));
        $forrest->set('product-variant-value', 'admin/product', array('action' => 'view', 'id' => $productId));
        
        $deadlines = $entityManager->getRepository('ErsBase\Entity\Deadline')
                ->findBy(array('price_change' => '1'), array('deadline' => 'ASC'));
        $agegroups = $entityManager->getRepository('ErsBase\Entity\Agegroup')
                ->findBy(array('price_change' => '1'), array('agegroup' => 'DESC'));
        $currencies = $entityManager->getRepository('ErsBase\Entity\Currency')
                ->findBy(array('active' => '1'), array('position' => 'ASC'));
        
        return new ViewModel(array(
            'product' => $product,
            'agegroups' => $agegroups,
            'deadlines' => $deadlines,
            'currencies' => $currencies,
        ));
    }   
    
    public function copyAction()
    {   
        $productId = (int) $this->params()->fromRoute('id', 0);
        if (!$productId) {
            return $this->redirect()->toRoute('admin/product', array(
                'action' => 'add'
            ));
        }
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $old_product = $entityManager->getRepository('ErsBase\Entity\Product')->findOneBy(array('id' => $productId));
        
        $product = clone $old_product;

        $form = $this->getServiceLocator()->get('Admin\Form\Product');
        $form->bind($product);
        $form->get('submit')->setAttribute('value', 'Copy');

        $request = $this->getRequest();
        if ($request->isPost()) {
            #$form->setInputFilter($product->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $entityManager->persist($product);
                $entityManager->flush();
                
                #$this->copyProductPrices($productId, $new_id);   
                #$this->copyProductVariants($productId, $new_id);

                $forrest = new Service\BreadcrumbService();
                $breadcrumb = $forrest->get('product');
                return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
            }
            $logger = $this->getServiceLocator()->get('Logger');
            $logger->warn($form->getMessages());
        }

        return new ViewModel(array(
            'id' => $productId,
            'form' => $form,
        ));
    }

    public function deleteAction()
    {
        $logger = $this->getServiceLocator()->get('Logger');

        $breadcrumbService = new Service\BreadcrumbService();

        $productId = (int) $this->params()->fromRoute('id', 0);
        if (!$productId) {
            return $this->redirect()->toRoute('admin/product');
        }

        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');

        $form = new Form\SimpleForm($entityManager);
        $form->get('submit')->setAttributes(array(
            'value' => 'Delete',
            'class' => 'btn btn-danger',
        ));
        
        $product = $entityManager->getRepository('ErsBase\Entity\Product')
                ->findOneBy(array('id' => $productId));
        $form->bind($product);
        
        $items = $entityManager->getRepository('ErsBase\Entity\Item')
                ->findBy(array('Product_id' => $productId));

        if ($this->request->isPost()) {
            $form->setData($this->request->getPost());

            if ($form->isValid()) {
                
                $this->removeProductPrices($product);
                $this->removeProductVariants($product);
                
                $entityManager->remove($product);
                $entityManager->flush();

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

        
        
        
        $productId = (int) $this->params()->fromRoute('id', 0);
        if (!$productId) {
            return $this->redirect()->toRoute('admin/product');
        }
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $Product = $entityManager->getRepository('ErsBase\Entity\Product')
                ->findOneBy(array('id' => $productId));
        $Items = $entityManager->getRepository('ErsBase\Entity\Item')
                ->findBy(array('Product_id' => $productId));

        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('del', 'No');

            if ($del == 'Yes') {
                $productId = (int) $request->getPost('id');
                $Product = $entityManager->getRepository('ErsBase\Entity\Product')
                    ->findOneBy(array('id' => $productId));
                
                $this->removeProductPrices($Product);
                $this->removeProductVariants($Product);
                
                $entityManager->remove($Product);
                $entityManager->flush();
            }

            return $this->redirect()->toRoute('admin/product');
        }

        return new ViewModel(array(
            'id'    => $productId,
            'items' => $Items,
            'product' => $Product,
        ));
    }
    
    private function removeProductPrices(Entity\Product $Product) {
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $ProductPrices = $entityManager->getRepository('ErsBase\Entity\ProductPrice')
                ->findBy(array('Product_id' => $Product->getId()));
        foreach($ProductPrices as $price) {
            $entityManager->remove($price);
        }
    }
    private function removeProductVariants(Entity\Product $Product) {
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $ProductVariants = $entityManager->getRepository('ErsBase\Entity\ProductVariant')
                ->findBy(array('Product_id' => $Product->getId()));
        foreach($ProductVariants as $variant) {
            $ProductVariantValues = $entityManager->getRepository('ErsBase\Entity\ProductVariantValue')
                    ->findBy(array('ProductVariant_id' => $variant->getId()), array('position' => 'ASC'));
            foreach($ProductVariantValues as $value) {
                $entityManager->remove($value);
            }
            $entityManager->remove($variant);
        }
    }
}