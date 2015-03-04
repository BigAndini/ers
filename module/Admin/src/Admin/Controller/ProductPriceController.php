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

class ProductPriceController extends AbstractActionController {
    /*public function indexAction()
    {
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        return new ViewModel(array(
            'productprices' => $em->getRepository("ersEntity\Entity\ProductPrice")->findAll(),
         ));
    }*/
    
    public function viewAction() {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/product');
        }
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $product = $em->getRepository("ersEntity\Entity\Product")
                ->findOneBy(array('id' => $id));
        $deadlines = $em->getRepository("ersEntity\Entity\Deadline")
                ->findBy(array(), array('deadline' => 'ASC'));
        $agegroups = $em->getRepository("ersEntity\Entity\Agegroup")
                ->findBy(array(), array('agegroup' => 'ASC'));
        
        return new ViewModel(array(
            'product' => $product,
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
                ->findBy(array(), array('deadline' => 'ASC'));
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
     * @param type $deadlineId
     * @return array
     */
    private function getAgegroupOptions($agegroupId = null) {
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $agegroups = $em->getRepository("ersEntity\Entity\Agegroup")
                ->findBy(array(), array('agegroup' => 'ASC'));
        $options = array();
        foreach($agegroups as $agegroup) {
            $selected = false;
            if($agegroupId == $agegroup->getId()) {
                $selected = true;
            }
            $options[] = array(
                'value' => $agegroup->getId(),
                'label' => 'Agegroup: '.$agegroup->getAgegroup()->format('Y-m-d H:i:s'),
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
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $productprice = new Entity\ProductPrice();
        $productprice->setProductId($id);

        $form = new Form\ProductPrice();
        
        $form->get('Deadline_id')->setAttribute('options', $this->getDeadlineOptions());
        $form->get('Agegroup_id')->setAttribute('options', $this->getAgegroupOptions());
        $form->bind($productprice);
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
                
                $product = $em->getRepository("ersEntity\Entity\Product")
                    ->findOneBy(array('id' => $productprice->getProductId()));
                $productprice->setProduct($product);
                
                
                $em->persist($productprice);
                $em->flush();
                
                $context = new Container('context');
                if(isset($context->route)) {
                    return $this->redirect()->toRoute($context->route, $context->params, $context->options);
                } else {
                    return $this->redirect()->toRoute('admin/product');
                }
            } else {
                error_log(var_export($form->getMessages(), true));
            }
        }
        
        $product = $em->getRepository("ersEntity\Entity\Product")->findOneBy(array('id' => $id));
        
        return array(
            'product' => $product,
            'form' => $form,                
        );
    }

    public function editAction()
    {
        
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/product-price', array(
                'action' => 'add'
            ));
        }
        
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
                
                $em->persist($productprice);
                $em->flush();

                $context = new Container('context');
                if(isset($context->route)) {
                    return $this->redirect()->toRoute($context->route, $context->params, $context->options);
                } else {
                    return $this->redirect()->toRoute('admin/product');
                }
            }
        }
        
        $product = $em->getRepository("ersEntity\Entity\Product")->findOneBy(array('id' => $productprice->getProductId()));
        
        return array(
            'id' => $id,
            'product' => $product,
            'form' => $form,
        );
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

            $context = new Container('context');
            if(isset($context->route)) {
                return $this->redirect()->toRoute($context->route, $context->params, $context->options);
            } else {
                return $this->redirect()->toRoute('admin/product');
            }
        }

        $productprice = $em->getRepository("ersEntity\Entity\ProductPrice")
                        ->findOneBy(array('id' => $id));
        
        $product = $em->getRepository("ersEntity\Entity\Product")->findOneBy(array('id' => $productprice->getProductId()));
        
        return array(
            'id'    => $id,
            'product' => $product,
            'price' => $productprice,
        );
    }
}