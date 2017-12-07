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

class ProductPackageController extends AbstractActionController {
    
    public function indexAction()
    {
        return $this->notFoundAction();
    }

    /**
     * Gives an array of products which can be handed over to a select form element
     * 
     * @param type $thisProduct
     * @return array
     */
    private function getProductOptions(Entity\Product $thisProduct = null, $productId = null) {
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $products = $entityManager->getRepository('ErsBase\Entity\Product')
                ->findBy(array(), array('position' => 'ASC'));
        $options = array();
        foreach($products as $product) {
            $selected = false;
            if($thisProduct->getId() == $product->getId()) {
                continue;
            }
            if($productId == $product->getId()) {
                $selected = true;
            }
            $options[] = array(
                'value' => $product->getId(),
                'label' => $product->getName(),
                'selected' => $selected,
            );
        }
        return $options;
    }
    
    public function addAction()
    {
        $forrest = new Service\BreadcrumbService();
        $breadcrumb = $forrest->get('product-package');
        
        $productId = (int) $this->params()->fromRoute('id', 0);
        if (!$productId) {
            return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
        }
        
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $form = new Form\ProductPackage();
        $form->get('submit')->setValue('Add');
        
        $thisProduct = $entityManager->getRepository('ErsBase\Entity\Product')
                ->findOneBy(array('id' => $productId));
        $form->get('SubProduct_id')->setValueOptions($this->getProductOptions($thisProduct));
        $form->get('Product_id')->setValue($productId);
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $productPackage = new Entity\ProductPackage();
            
            #$form->setInputFilter($productPackage->getInputFilter());
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $productPackage->populate($form->getData());
                
                if($productPackage->getSubProductId() != 0) {
                    $subProduct = $entityManager->getRepository('ErsBase\Entity\Product')
                        ->findOneBy(array('id' => $productPackage->getSubProductId()));
                    $productPackage->setSubProduct($subProduct);
                }
                if($productPackage->getProductId() != 0) {
                    $product = $entityManager->getRepository('ErsBase\Entity\Product')
                        ->findOneBy(array('id' => $productPackage->getProductId()));
                    $productPackage->setProduct($product);
                }
                
                $entityManager->persist($productPackage);
                $entityManager->flush();
           
                return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
            }
            $logger = $this->getServiceLocator()->get('Logger');
            $logger->warn($form->getMessages());
        }
        
        return new ViewModel(array(
            'id' => $productId,
            'form' => $form,
            'product' => $thisProduct,
            'breadcrumb' => $breadcrumb,
        ));
    }

    public function editAction()
    {
        $forrest = new Service\BreadcrumbService();
        $breadcrumb = $forrest->get('product-package');
        
        $productPackageId = (int) $this->params()->fromRoute('id', 0);
        if (!$productPackageId) {
            return $this->redirect()->toRoute('admin/product-package', array(
                'action' => 'add'
            ));
        }
        $subproduct_id = (int) $this->params()->fromRoute('subproduct_id', 0);
        if (!$subproduct_id) {
            return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
        }
        
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $productPackage = $entityManager->getRepository('ErsBase\Entity\ProductPackage')
                ->findOneBy(array('id' => $productPackageId));

        $form = new Form\ProductPackage();
        $form->bind($productPackage);
        $form->get('submit')->setAttribute('value', 'Edit');

        $thisProduct = $entityManager->getRepository('ErsBase\Entity\Product')
                ->findOneBy(array('id' => $productPackage->getProductId()));

        $form->get('SubProduct_id')->setValueOptions($this->getProductOptions($thisProduct, $subproduct_id));
        $form->get('Product_id')->setValue($productPackageId);
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            #$form->setInputFilter($productPackage->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $productPackage = $form->getData();
                if($productPackage->getSubProductId() != 0) {
                    $subProduct = $entityManager->getRepository('ErsBase\Entity\Product')
                        ->findOneBy(array('id' => $productPackage->getSubProductId()));
                    $productPackage->setSubProduct($subProduct);
                }
                if($productPackage->getProductId() != 0) {
                    $product = $entityManager->getRepository('ErsBase\Entity\Product')
                        ->findOneBy(array('id' => $productPackage->getProductId()));
                    $productPackage->setProduct($product);
                }
                
                $entityManager->persist($productPackage);
                $entityManager->flush();

                return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
            }
        }

        return new ViewModel(array(
            'id' => $productPackageId,
            'form' => $form,
            'product' => $thisProduct,
            'breadcrumb' => $forrest->get('product-package'),
        ));
    }

    public function deleteAction()
    {
        $productPackageId = (int) $this->params()->fromRoute('id', 0);
        if (!$productPackageId) {
            return $this->redirect()->toRoute('admin/product-package');
        }
        $forrest = new Service\BreadcrumbService();
        
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $productPackage = $entityManager->getRepository('ErsBase\Entity\ProductPackage')
                ->findOneBy(array('id' => $productPackageId));
        $product = $entityManager->getRepository('ErsBase\Entity\Product')
                ->findOneBy(array('id' => $productPackage->getProductId()));
        $subproduct = $entityManager->getRepository('ErsBase\Entity\Product')
                ->findOneBy(array('id' => $productPackage->getSubProductId()));

        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('del', 'No');

            if ($del == 'Yes') {
                $productPackageId = (int) $request->getPost('id');
                $productPackage = $entityManager->getRepository('ErsBase\Entity\ProductPackage')
                    ->findOneBy(array('id' => $productPackageId));
                $entityManager->remove($productPackage);
                $entityManager->flush();
            }

            $breadcrumb = $forrest->get('product-package');
            return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
        }

        return new ViewModel(array(
            'id'    => $productPackageId,
            'product' => $product,
            'subproduct' => $subproduct,
            'productpackage' => $productPackage,
            'breadcrumb' => $forrest->get('product-package'),
        ));
    }
}