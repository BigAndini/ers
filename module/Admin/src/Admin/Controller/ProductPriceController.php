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

class ProductPriceController extends AbstractActionController {
    public function indexAction() {
        return $this->notFoundAction();
    }
    
    public function viewAction() {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/product');
        }
        $forrest = new Service\BreadcrumbService();
        $forrest->set('product-price', 'admin/product-price', array(
            'action' => 'view',
            'id' => $id,
            ));
        
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $product = $entityManager->getRepository('ErsBase\Entity\Product')
                ->findOneBy(array('id' => $id));
        $deadlines = $entityManager->getRepository('ErsBase\Entity\Deadline')
                ->findBy(array('price_change' => '1'), array('deadline' => 'ASC'));
        $agegroups = $entityManager->getRepository('ErsBase\Entity\Agegroup')
                ->findBy(array('price_change' => '1'), array('agegroup' => 'DESC'));
        $currencies = $entityManager->getRepository('ErsBase\Entity\Currency')
                ->findBy(array('active' => 1), array('position' => 'ASC'));
        
        return new ViewModel(array(
            'product'   => $product,
            'deadlines' => $deadlines,
            'agegroups' => $agegroups,
            'currencies' => $currencies,
        ));
    }

    /**
     * Gives an array of currencies which can be handed over to a select form element
     * 
     * @param type $currencyId
     * @return array
     */
    private function getCurrencyOptions($currencyId = null) {
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $currencys = $entityManager->getRepository('ErsBase\Entity\Currency')
                ->findBy(array('active' => '1'), array('position' => 'ASC'));
        $options = array();
        $options[] = array(
            'value' => 0,
            'label' => 'Select Currency ...',
            'disabled' => true,
            'selected' => true,
        );
        foreach($currencys as $currency) {
            $selected = false;
            if($currencyId == $currency->getId()) {
                $selected = true;
                $options[0]['selected'] = false;
            }
            $options[] = array(
                'value' => $currency->getId(),
                'label' => $currency->getName().' ('.$currency->getSymbol().' / '.$currency->getShort().')',
                'selected' => $selected,
            );
        }
        $selected = false;
        if($currencyId == null) {
            $selected = true;
        }
        
        return $options;
    }
    
    /**
     * Gives an array of deadlines which can be handed over to a select form element
     * 
     * @param type $deadlineId
     * @return array
     */
    private function getDeadlineOptions($deadlineId = null) {
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $deadlines = $entityManager->getRepository('ErsBase\Entity\Deadline')
                ->findBy(array('price_change' => '1'), array('deadline' => 'ASC'));
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
            'label' => 'after last deadline',
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
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $agegroups = $entityManager->getRepository('ErsBase\Entity\Agegroup')
                ->findBy(array('price_change' => '1'), array('agegroup' => 'ASC'));
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
            'label' => 'adult',
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
        $currencyId = (int) $this->params()->fromQuery('currency_id', null);
        $deadlineId = (int) $this->params()->fromQuery('deadline_id', null);
        $agegroupId = (int) $this->params()->fromQuery('agegroup_id', null);
        
        $forrest = new Service\BreadcrumbService();
        
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $productprice = new Entity\ProductPrice();
        $productprice->setProductId($id);

        $form = new Form\ProductPrice();
        
        $form->bind($productprice);
        
        $form->get('currency_id')->setAttribute('options', $this->getCurrencyOptions($currencyId));
        $form->get('Deadline_id')->setAttribute('options', $this->getDeadlineOptions($deadlineId));
        $form->get('Agegroup_id')->setAttribute('options', $this->getAgegroupOptions($agegroupId));
        
        $form->get('submit')->setValue('Add');
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $productprice = new Entity\ProductPrice();
            
            #$form->setInputFilter($productprice->getInputFilter());
            $form->setData($request->getPost());
            
            if ($form->isValid()) {
                $productprice = $form->getData();
                
                if($productprice->getDeadlineId() == 0) {
                    $productprice->setDeadline(null);
                } else {
                    $deadline = $entityManager->getRepository('ErsBase\Entity\Deadline')
                        ->findOneBy(array('id' => $productprice->getDeadlineId()));
                    $productprice->setDeadline($deadline);
                }
                
                if($productprice->getAgegroupId() == 0) {
                    $productprice->setAgegroup(null);
                } else {
                    $agegroup = $entityManager->getRepository('ErsBase\Entity\Agegroup')
                        ->findOneBy(array('id' => $productprice->getAgegroupId()));
                    $productprice->setAgegroup($agegroup);
                }
                
                $product = $entityManager->getRepository('ErsBase\Entity\Product')
                    ->findOneBy(array('id' => $productprice->getProductId()));
                $productprice->setProduct($product);
                
                $currency = $entityManager->getRepository('ErsBase\Entity\Currency')
                    ->findOneBy(array('id' => $productprice->getCurrencyId()));
                $productprice->setCurrency($currency);
                
                $entityManager->persist($productprice);
                $entityManager->flush();
                
                if(!$forrest->exists('product-price')) {
                    $forrest->set('product-price', 'product');
                }
                
                $breadcrumb = $forrest->get('product-price');
                return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
            } else {
                $logger = $this->getServiceLocator()->get('Logger');
                $logger->warn($form->getMessages());
            }
        }
        
        $product = $entityManager->getRepository('ErsBase\Entity\Product')->findOneBy(array('id' => $id));
        
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
        $forrest = new Service\BreadcrumbService();
        
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $productprice = $entityManager->getRepository('ErsBase\Entity\ProductPrice')
                ->findOneBy(array('id' => $id));

        $form = new Form\ProductPrice();
        $form->bind($productprice);
        
        $form->get('currency_id')->setAttribute('options', $this->getCurrencyOptions($productprice->getCurrencyId()));
        $form->get('Deadline_id')->setAttribute('options', $this->getDeadlineOptions($productprice->getDeadlineId()));
        $form->get('Agegroup_id')->setAttribute('options', $this->getAgegroupOptions($productprice->getAgegroupId()));
        
        $form->get('submit')->setAttribute('value', 'Edit');

        $request = $this->getRequest();
        if ($request->isPost()) {
            #$form->setInputFilter($productprice->getInputFilter());
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $productprice = $form->getData();
                
                if($productprice->getDeadlineId() == 0) {
                    $productprice->setDeadlineId(null);
                }
                
                if($productprice->getAgegroupId() == 0) {
                    $productprice->setAgegroupId(null);
                }
                
                $entityManager->persist($productprice);
                $entityManager->flush();

                $breadcrumb = $forrest->get('product-price');
                return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
            }
        }
        
        $product = $entityManager->getRepository('ErsBase\Entity\Product')->findOneBy(array('id' => $productprice->getProductId()));
        
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
        $forrest = new Service\BreadcrumbService();
        
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $productprice = $entityManager->getRepository('ErsBase\Entity\ProductPrice')
                ->findOneBy(array('id' => $id));
	if(!$forrest->exists('product-price')) {
            $forrest->set('product-price', 'admin/product-price', ['action' => 'view', 'id' => $productprice->getProduct()->getId()]);
        }
        $breadcrumb = $forrest->get('product-price');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('del', 'No');

            if ($del == 'Yes') {
                
                $id = (int) $request->getPost('id');
                $productprice = $entityManager->getRepository('ErsBase\Entity\ProductPrice')
                        ->findOneBy(array('id' => $id));
                $entityManager->remove($productprice);
                $entityManager->flush();
            }

            return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
        }

        $productprice = $entityManager->getRepository('ErsBase\Entity\ProductPrice')
                        ->findOneBy(array('id' => $id));
        
        $product = $entityManager->getRepository('ErsBase\Entity\Product')->findOneBy(array('id' => $productprice->getProductId()));
        
        return new ViewModel(array(
            'id'    => $id,
            'product' => $product,
            'price' => $productprice,
            'breadcrumb' => $forrest->get('product-price'),
        ));
    }
}
