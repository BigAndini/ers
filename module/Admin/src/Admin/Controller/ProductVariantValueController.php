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

class ProductVariantValueController extends AbstractActionController 
{    
    public function indexAction()
    {
        return $this->notFoundAction();
    }

    public function viewAction()
    {
        $productVariantValueId = (int) $this->params()->fromRoute('id', 0);
        if (!$productVariantValueId) {
            return $this->redirect()->toRoute('admin/product');
        }
        
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        return new ViewModel(array(
            'productvariant' => $entityManager->getRepository('ErsBase\Entity\ProductVariantValue')->findOneBy(array('id' => $productVariantValueId)),
        ));
    }
    
    public function addAction()
    {
        $productVariantValueId = (int) $this->params()->fromRoute('id', 0);
        if (!$productVariantValueId) {
            return $this->redirect()->toRoute('admin/product-variant-value');
        }
        
        $forrest = new Service\BreadcrumbService();
        if(!$forrest->exists('product-variant-value')) {
            $forrest->set('product-variant-value', 'admin/product');
        }
        
        $form = new Form\ProductVariantValue();
        $form->get('submit')->setValue('Add');
        $form->get('ProductVariant_id')->setValue($productVariantValueId);
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $value = new Entity\ProductVariantValue();
            #$form->setInputFilter($value->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $entityManager = $this->getServiceLocator()
                    ->get('Doctrine\ORM\EntityManager');

                $value->populate($form->getData());
                $productvariant = $entityManager->getRepository('ErsBase\Entity\ProductVariant')->findOneBy(array('id' => $value->getProductVariantId()));
                $value->setProductVariant($productvariant);
                
                $entityManager->persist($value);
                $entityManager->flush();
                
                $breadcrumb = $forrest->get('product-variant-value');
                return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
            }
            $logger = $this->getServiceLocator()->get('Logger');
            $logger->warn($form->getMessages());
        }
        
        return new ViewModel(array(
            'productvariant_id' => $productVariantValueId,
            'breadcrumb' => $forrest->get('product-variant-value'),
            'form' => $form,                
        ));
    }

    public function editAction()
    {
        $productVariantValueId = (int) $this->params()->fromRoute('id', 0);
        if (!$productVariantValueId) {
            return $this->redirect()->toRoute('admin/product-variant', array(
                'action' => 'add'
            ));
        }
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $forrest = new Service\BreadcrumbService();
        if(!$forrest->exists('product-variant-value')) {
            $forrest->set('product-variant-value', 'admin/product');
        }
        
        $value = $entityManager->getRepository('ErsBase\Entity\ProductVariantValue')->findOneBy(array('id' => $productVariantValueId));

        $form  = new Form\ProductVariantValue();
        $form->bind($value);
        $form->get('submit')->setAttribute('value', 'Edit');

        $request = $this->getRequest();
        if ($request->isPost()) {
            #$form->setInputFilter($value->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $entityManager->persist($form->getData());
                $entityManager->flush();

                if(!$forrest->exists('product-variant-value')) {
                    $forrest->set('product-variant-value', 'product');
                }
                $breadcrumb = $forrest->get('product-variant-value');
                return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
            }
        }

        return new ViewModel(array(
            'id' => $productVariantValueId,
            'breadcrumb' => $forrest->get('product-variant-value'),
            'form' => $form,
        ));
    }

    public function deleteAction()
    {
        $productVariantValueId = (int) $this->params()->fromRoute('id', 0);
        if (!$productVariantValueId) {
            return $this->redirect()->toRoute('admin/product');
        }
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('del', 'No');

            if ($del == 'Yes') {
                
                $productVariantValueId = (int) $request->getPost('id');
                $value = $entityManager->getRepository('ErsBase\Entity\ProductVariantValue')
                        ->findOneBy(array('id' => $productVariantValueId));
                $entityManager->remove($value);
                $entityManager->flush();
            }

            $forrest = new Service\BreadcrumbService();
            $breadcrumb = $forrest->get('product-variant-value');
            return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
        }
        
        return new ViewModel(array(
            'id'    => $productVariantValueId,
            'value' => $entityManager->getRepository('ErsBase\Entity\ProductVariantValue')
                        ->findOneBy(array('id' => $productVariantValueId)),
        ));
    }
}