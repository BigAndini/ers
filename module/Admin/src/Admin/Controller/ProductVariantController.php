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
use Admin\Form;
use ErsBase\Service;

class ProductVariantController extends AbstractActionController 
{    
    public function indexAction()
    {
        return $this->notFoundAction();
    }

    public function addAction()
    {
        $product_id = (int) $this->params()->fromRoute('id', 0);
        if (!$product_id) {
            return $this->redirect()->toRoute('admin/product');
        }
        
        $forrest = new Service\BreadcrumbService();
        if(!$forrest->exists('product-variant')) {
            $forrest->set('product-variant', 'admin/product-variant');
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
            #$form->setInputFilter($productvariant->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $entityManager = $this->getServiceLocator()
                    ->get('Doctrine\ORM\EntityManager');

                $productvariant->populate($form->getData());
                $product = $entityManager->getRepository('ErsBase\Entity\Product')->findOneBy(array('id' => $productvariant->getProductId()));
                $productvariant->setProduct($product);
                
                $entityManager->persist($productvariant);
                $entityManager->flush();
                
                $breadcrumb = $forrest->get('product-variant');
                return $this->redirect()->toRoute(
                        $breadcrumb->route, 
                        $breadcrumb->params, 
                        $breadcrumb->options
                        );
            }
            $logger = $this->getServiceLocator()->get('Logger');
            $logger->warn($form->getMessages());
        }
        
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $product = $entityManager->getRepository('ErsBase\Entity\Product')->findOneBy(array('id' => $product_id));
        
        
        $breadcrumb = $forrest->get('product-variant');
        return new ViewModel(array(
            'breadcrumb' => $breadcrumb,
            'product' => $product,
            'form' => $form,                
        ));
    }

    public function editAction()
    {
        $productVariantId = (int) $this->params()->fromRoute('id', 0);
        if (!$productVariantId) {
            return $this->redirect()->toRoute('admin/product-variant', array(
                'action' => 'add'
            ));
        }
        $forrest = new Service\BreadcrumbService();
        
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $productvariant = $entityManager->getRepository('ErsBase\Entity\ProductVariant')->findOneBy(array('id' => $productVariantId));

        #$form  = new Form\ProductVariant();
        $form = $this->getServiceLocator()->get('Admin\Form\ProductVariant');
        $form->bind($productvariant);
        $form->get('submit')->setAttribute('value', 'Edit');

        $request = $this->getRequest();
        if ($request->isPost()) {
            #$form->setInputFilter($productvariant->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $productvariant = $form->getData();
                
                $entityManager->persist($productvariant);
                $entityManager->flush();
                
                $forrest = new Service\BreadcrumbService();
                if(!$forrest->exists('product-variant')) {
                    $forrest->set('product-variant', 'product');
                }
                $breadcrumb = $forrest->get('product-variant');
                return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
            }
        }
        
        $product = $entityManager->getRepository('ErsBase\Entity\Product')->findOneBy(array('id' => $productvariant->getProductId()));

        return new ViewModel(array(
            'id' => $productVariantId,
            'product' => $product,
            'form' => $form,
            'breadcrumb' => $forrest->get('product-variant'),
        ));
    }

    public function deleteAction()
    {
        $productVariantId = (int) $this->params()->fromRoute('id', 0);
        if (!$productVariantId) {
            return $this->redirect()->toRoute('admin/product');
        }
        $forrest = new Service\BreadcrumbService();
        
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('del', 'No');

            if ($del == 'Yes') {
                
                $productVariantId = (int) $request->getPost('id');
                $productvariant = $entityManager->getRepository('ErsBase\Entity\ProductVariant')
                        ->findOneBy(array('id' => $productVariantId));
                $values = $entityManager->getRepository('ErsBase\Entity\ProductVariantValue')
                        ->findBy(array('ProductVariant_id' => $productvariant->getId()));
                foreach($values as $value) {
                    $entityManager->remove($value);
                }
                $entityManager->remove($productvariant);
                $entityManager->flush();
            }

            $breadcrumb = $forrest->get('product-variant');
            return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
        }
        
        $productvariant = $entityManager->getRepository('ErsBase\Entity\ProductVariant')
                        ->findOneBy(array('id' => $productVariantId));
        $product = $entityManager->getRepository('ErsBase\Entity\Product')
                ->findOneBy(array('id' => $productvariant->getProductId()));
        
        return new ViewModel(array(
            'id'    => $productVariantId,
            'product' => $product,
            'productvariant' => $productvariant,
            'breadcrumb' => $forrest->get('product-variant'),
        ));
    }
}