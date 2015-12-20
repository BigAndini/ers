<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use ersBase\Entity;
use Admin\Form;
use ersBase\Service;

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
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $products = $em->getRepository("ersBase\Entity\Product")
                ->findBy(array(), array('ordering' => 'ASC'));
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
        
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
        }
        
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $form = new Form\ProductPackage();
        $form->get('submit')->setValue('Add');
        
        $thisProduct = $em->getRepository("ersBase\Entity\Product")
                ->findOneBy(array('id' => $id));
        $form->get('SubProduct_id')->setValueOptions($this->getProductOptions($thisProduct));
        $form->get('Product_id')->setValue($id);
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $productPackage = new Entity\ProductPackage();
            
            $form->setInputFilter($productPackage->getInputFilter());
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $productPackage->populate($form->getData());
                
                if($productPackage->getSubProductId() != 0) {
                    $subProduct = $em->getRepository("ersBase\Entity\Product")
                        ->findOneBy(array('id' => $productPackage->getSubProductId()));
                    $productPackage->setSubProduct($subProduct);
                }
                if($productPackage->getProductId() != 0) {
                    $product = $em->getRepository("ersBase\Entity\Product")
                        ->findOneBy(array('id' => $productPackage->getProductId()));
                    $productPackage->setProduct($product);
                }
                
                $em->persist($productPackage);
                $em->flush();
           
                return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
            } else {
                $logger = $this->getServiceLocator()->get('Logger');
                $logger->warn($form->getMessages());
            }
        }
        
        return new ViewModel(array(
            'id' => $id,
            'form' => $form,
            'product' => $thisProduct,
            'breadcrumb' => $breadcrumb,
        ));
    }

    public function editAction()
    {
        $forrest = new Service\BreadcrumbService();
        $breadcrumb = $forrest->get('product-package');
        
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/product-package', array(
                'action' => 'add'
            ));
        }
        $subproduct_id = (int) $this->params()->fromRoute('subproduct_id', 0);
        if (!$subproduct_id) {
            return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
        }
        
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $productPackage = $em->getRepository("ersBase\Entity\ProductPackage")
                ->findOneBy(array('id' => $id));

        $form = new Form\ProductPackage();
        $form->bind($productPackage);
        $form->get('submit')->setAttribute('value', 'Edit');

        $thisProduct = $em->getRepository("ersBase\Entity\Product")
                ->findOneBy(array('id' => $productPackage->getProductId()));
        $form->get('SubProduct_id')->setValueOptions($this->getProductOptions($thisProduct, $subproduct_id));
        $form->get('Product_id')->setValue($id);
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($productPackage->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $productPackage = $form->getData();
                if($productPackage->getSubProductId() != 0) {
                    $subProduct = $em->getRepository("ersBase\Entity\Product")
                        ->findOneBy(array('id' => $productPackage->getSubProductId()));
                    $productPackage->setSubProduct($subProduct);
                }
                if($productPackage->getProductId() != 0) {
                    $product = $em->getRepository("ersBase\Entity\Product")
                        ->findOneBy(array('id' => $productPackage->getProductId()));
                    $productPackage->setProduct($product);
                }
                
                $em->persist($productPackage);
                $em->flush();

                return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
            }
        }

        return new ViewModel(array(
            'id' => $id,
            'form' => $form,
            'product' => $thisProduct,
            'breadcrumb' => $forrest->get('product-package'),
        ));
    }

    public function deleteAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/product-package');
        }
        $forrest = new Service\BreadcrumbService();
        
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $productPackage = $em->getRepository("ersBase\Entity\ProductPackage")
                ->findOneBy(array('id' => $id));
        $product = $em->getRepository("ersBase\Entity\Product")
                ->findOneBy(array('id' => $productPackage->getProductId()));
        $subproduct = $em->getRepository("ersBase\Entity\Product")
                ->findOneBy(array('id' => $productPackage->getSubProductId()));

        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('del', 'No');

            if ($del == 'Yes') {
                $id = (int) $request->getPost('id');
                $productPackage = $em->getRepository("ersBase\Entity\ProductPackage")
                    ->findOneBy(array('id' => $id));
                $em->remove($productPackage);
                $em->flush();
            }

            $breadcrumb = $forrest->get('product-package');
            return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
        }

        return new ViewModel(array(
            'id'    => $id,
            'product' => $product,
            'subproduct' => $subproduct,
            'productpackage' => $productPackage,
            'breadcrumb' => $forrest->get('product-package'),
        ));
    }
}