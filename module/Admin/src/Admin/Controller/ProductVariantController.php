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
        $form->get('product_id')->setValue($product_id);
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
                $em = $this->getServiceLocator()
                    ->get('Doctrine\ORM\EntityManager');

                $productvariant->populate($form->getData());
                $product = $em->getRepository('ErsBase\Entity\Product')->findOneBy(array('id' => $productvariant->getProductId()));
                $productvariant->setProduct($product);
                
                $em->persist($productvariant);
                $em->flush();
                
                $breadcrumb = $forrest->get('product-variant');
                return $this->redirect()->toRoute(
                        $breadcrumb->route, 
                        $breadcrumb->params, 
                        $breadcrumb->options
                        );
            } else {
                $logger = $this->getServiceLocator()->get('Logger');
                $logger->warn($form->getMessages());
            }
        }
        
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $product = $em->getRepository('ErsBase\Entity\Product')->findOneBy(array('id' => $product_id));
        
        
        $breadcrumb = $forrest->get('product-variant');
        return new ViewModel(array(
            'breadcrumb' => $breadcrumb,
            'product' => $product,
            'form' => $form,                
        ));
    }

    public function editAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/product-variant', array(
                'action' => 'add'
            ));
        }
        $forrest = new Service\BreadcrumbService();
        
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $productvariant = $em->getRepository('ErsBase\Entity\ProductVariant')->findOneBy(array('id' => $id));

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
                
                $em->persist($productvariant);
                $em->flush();
                
                $forrest = new Service\BreadcrumbService();
                if(!$forrest->exists('product-variant')) {
                    $forrest->set('product-variant', 'product');
                }
                $breadcrumb = $forrest->get('product-variant');
                return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
            }
        }
        
        $product = $em->getRepository('ErsBase\Entity\Product')->findOneBy(array('id' => $productvariant->getProductId()));

        return new ViewModel(array(
            'id' => $id,
            'product' => $product,
            'form' => $form,
            'breadcrumb' => $forrest->get('product-variant'),
        ));
    }

    public function deleteAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/product');
        }
        $forrest = new Service\BreadcrumbService();
        
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('del', 'No');

            if ($del == 'Yes') {
                
                $id = (int) $request->getPost('id');
                $productvariant = $em->getRepository('ErsBase\Entity\ProductVariant')
                        ->findOneBy(array('id' => $id));
                $values = $em->getRepository('ErsBase\Entity\ProductVariantValue')
                        ->findBy(array('ProductVariant_id' => $productvariant->getId()));
                foreach($values as $value) {
                    $em->remove($value);
                }
                $em->remove($productvariant);
                $em->flush();
            }

            $breadcrumb = $forrest->get('product-variant');
            return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
        }
        
        $productvariant = $em->getRepository('ErsBase\Entity\ProductVariant')
                        ->findOneBy(array('id' => $id));
        $product = $em->getRepository('ErsBase\Entity\Product')
                ->findOneBy(array('id' => $productvariant->getProductId()));
        
        return new ViewModel(array(
            'id'    => $id,
            'product' => $product,
            'productvariant' => $productvariant,
            'breadcrumb' => $forrest->get('product-variant'),
        ));
    }
}