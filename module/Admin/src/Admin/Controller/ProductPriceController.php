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

class ProductPriceController extends AbstractActionController {
    public function indexAction() {
        return $this->notFoundAction();
    }
    
    public function viewAction() {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/product');
        }
        $forrest = new Service\BreadcrumbFactory();
        $forrest->set('product-price', 'admin/product-price', array(
            'action' => 'view',
            'id' => $id,
            ));
        
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $product = $em->getRepository("ersEntity\Entity\Product")
                ->findOneBy(array('id' => $id));
        $deadlines = $em->getRepository("ersEntity\Entity\Deadline")
                ->findBy(array('priceChange' => '1'), array('deadline' => 'ASC'));
        $agegroups = $em->getRepository("ersEntity\Entity\Agegroup")
                ->findBy(array('priceChange' => '1'), array('agegroup' => 'DESC'));
        
        return new ViewModel(array(
            'product'   => $product,
            'deadlines' => $deadlines,
            'agegroups' => $agegroups,
        ));
    }

    /**
     * Gives an array of deadlines which can be handed over to a select form element
     * 
     * @param type $deadlineId
     * @return array
     */
    private function getDeadlineOptions($deadlineId = null) {
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $deadlines = $em->getRepository("ersEntity\Entity\Deadline")
                ->findBy(array('priceChange' => '1'), array('deadline' => 'ASC'));
        $options = array();
        foreach($deadlines as $deadline) {
            $selected = false;
            if($deadlineId == $deadline->getId()) {
                $selected = true;
            }
            $options[] = array(
                'value' => $deadline->getId(),
                'label' => 'Deadline: '.$deadline->getDeadline()->format('Y-m-d H:i:s'),
                'selected' => $selected,
            );
        }
        $selected = false;
        if($deadlineId == null) {
            $selected = true;
        }
        $options[] = array(
            'value' => 0,
            'label' => 'no Deadline',
            'selected' => $selected,
        );
        return $options;
    }
    
    /**
     * Gives an array of deadlines which can be handed over to a select form element
     * 
     * @param type $agegroupId
     * @return array
     */
    private function getAgegroupOptions($agegroupId = null) {
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $agegroups = $em->getRepository("ersEntity\Entity\Agegroup")
                ->findBy(array('priceChange' => '1'), array('agegroup' => 'ASC'));
        $options = array();
        foreach($agegroups as $agegroup) {
            $selected = false;
            if($agegroupId == $agegroup->getId()) {
                $selected = true;
            }
            $options[] = array(
                'value' => $agegroup->getId(),
                'label' => 'Agegroup: '.$agegroup->getName().' ('.$agegroup->getAgegroup()->format('Y-m-d').')',
                'selected' => $selected,
            );
        }
        $selected = false;
        if($agegroupId == null) {
            $selected = true;
        }
        $options[] = array(
            'value' => 0,
            'label' => 'no Agegroup',
            'selected' => $selected,
        );
        return $options;
    }
    
    
    public function addAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/product');
        }
        $forrest = new Service\BreadcrumbFactory();
        
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $productprice = new Entity\ProductPrice();
        $productprice->setProductId($id);

        $form = new Form\ProductPrice();
        
        $form->bind($productprice);
        
        $form->get('Deadline_id')->setValueOptions($this->getDeadlineOptions());
        $form->get('Agegroup_id')->setValueOptions($this->getAgegroupOptions());
        $form->get('submit')->setValue('Add');
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $productprice = new Entity\ProductPrice();
            
            $form->setInputFilter($productprice->getInputFilter());
            $form->setData($request->getPost());
            
            if ($form->isValid()) {
                $productprice = $form->getData();
                
                if($productprice->getDeadlineId() == 0) {
                    $productprice->setDeadline(null);
                } else {
                    $deadline = $em->getRepository("ersEntity\Entity\Deadline")
                        ->findOneBy(array('id' => $productprice->getDeadlineId()));
                    $productprice->setDeadline($deadline);
                }
                
                if($productprice->getAgegroupId() == 0) {
                    $productprice->setAgegroup(null);
                } else {
                    $agegroup = $em->getRepository("ersEntity\Entity\Agegroup")
                        ->findOneBy(array('id' => $productprice->getAgegroupId()));
                    $productprice->setAgegroup($agegroup);
                }
                
                $product = $em->getRepository("ersEntity\Entity\Product")
                    ->findOneBy(array('id' => $productprice->getProductId()));
                $productprice->setProduct($product);
                
                
                $em->persist($productprice);
                $em->flush();
                
                $breadcrumb = $forrest->get('product-price');
                return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
            } else {
                $logger = $this
                    ->getServiceLocator()
                    ->get('Logger');
                $logger->warn($form->getMessages());
            }
        }
        
        $product = $em->getRepository("ersEntity\Entity\Product")->findOneBy(array('id' => $id));
        
        if(!$forrest->exists('product-price')) {
            $forrest->set('product-price', 'admin/product');
        }
        
        return new ViewModel(array(
            'product' => $product,
            'form' => $form,                
            'breadcrumb' => $forrest->get('product-price'),
        ));
    }

    public function editAction()
    {        
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/product-price', array(
                'action' => 'add'
            ));
        }
        $forrest = new Service\BreadcrumbFactory();
        
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $productprice = $em->getRepository("ersEntity\Entity\ProductPrice")
                ->findOneBy(array('id' => $id));

        $form = new Form\ProductPrice();
        $form->bind($productprice);
        
        $form->get('Deadline_id')->setAttribute('options', $this->getDeadlineOptions($productprice->getDeadlineId()));
        $form->get('Agegroup_id')->setAttribute('options', $this->getAgegroupOptions($productprice->getAgegroupId()));
        
        $form->get('submit')->setAttribute('value', 'Edit');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($productprice->getInputFilter());
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $productprice = $form->getData();
                
                if($productprice->getDeadlineId() == 0) {
                    $productprice->setDeadlineId(null);
                }
                
                if($productprice->getAgegroupId() == 0) {
                    $productprice->setAgegroupId(null);
                }
                
                $em->persist($productprice);
                $em->flush();

                $breadcrumb = $forrest->get('product-price');
                return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
            }
        }
        
        $product = $em->getRepository("ersEntity\Entity\Product")->findOneBy(array('id' => $productprice->getProductId()));
        
        return new ViewModel(array(
            'id' => $id,
            'product' => $product,
            'form' => $form,
            'breadcrumb' => $forrest->get('product-price'),
        ));
    }

    public function deleteAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/product');
        }
        $forrest = new Service\BreadcrumbFactory();
        
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('del', 'No');

            if ($del == 'Yes') {
                
                $id = (int) $request->getPost('id');
                $productprice = $em->getRepository("ersEntity\Entity\ProductPrice")
                        ->findOneBy(array('id' => $id));
                $em->remove($productprice);
                $em->flush();
            }

            $breadcrumb = $forrest->get('product-price');
            return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
        }

        $productprice = $em->getRepository("ersEntity\Entity\ProductPrice")
                        ->findOneBy(array('id' => $id));
        
        $product = $em->getRepository("ersEntity\Entity\Product")->findOneBy(array('id' => $productprice->getProductId()));
        
        return new ViewModel(array(
            'id'    => $id,
            'product' => $product,
            'price' => $productprice,
            'breadcrumb' => $forrest->get('product-price'),
        ));
    }
}