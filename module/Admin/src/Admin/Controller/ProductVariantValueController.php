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
use Admin\Service;

class ProductVariantValueController extends AbstractActionController 
{    
    public function indexAction()
    {
        return $this->$this->notFoundAction();
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
            return $this->redirect()->toRoute('admin/product-variant-value');
        }
        
        $forrest = new Service\BreadcrumbFactory();
        if(!$forrest->exists('product-variant-value')) {
            $forrest->set('product-variant-value', 'product-variant-value');
        }
        
        $form = new Form\ProductVariantValue();
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
                $productvariant = $em->getRepository("ersEntity\Entity\ProductVariant")->findOneBy(array('id' => $value->getProductVariantId()));
                $value->setProductVariant($productvariant);
                
                $em->persist($value);
                $em->flush();
                
                $breadcrumb = $forrest->get('product-variant-value');
                return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
            } else {
                error_log(var_export($form->getMessages(),true));
            }
        }
        
        return new ViewModel(array(
            'productvariant_id' => $id,
            'breadcrum' => $forrest->get('product-variant-value'),
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
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $value = $em->getRepository("ersEntity\Entity\ProductVariantValue")->findOneBy(array('id' => $id));

        $form  = new Form\ProductVariantValue();
        $form->bind($value);
        $form->get('submit')->setAttribute('value', 'Edit');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($value->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $em->persist($form->getData());
                $em->flush();

                $forrest = new Service\BreadcrumbFactory();
                $breadcrumb = $forrest->get('product-variant-value');
                return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
            }
        }

        return new ViewModel(array(
            'id' => $id,
            'form' => $form,
        ));
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

            $forrest = new Service\BreadcrumbFactory();
            $breadcrumb = $forrest->get('product-variant-value');
            return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
        }
        
        return new ViewModel(array(
            'id'    => $id,
            'value' => $em->getRepository("ersEntity\Entity\ProductVariantValue")
                        ->findOneBy(array('id' => $id)),
        ));
    }
}