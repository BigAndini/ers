<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace PreReg\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use PreReg\Form;
use PreReg\Service;
use Zend\Session\Container;

class ProductController extends AbstractActionController {
    public function indexAction()
    {
        $forrest = new Service\BreadcrumbFactory();
        $forrest->reset();
        $forrest->set('participant', 'product');
        
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $tmp = $em->getRepository("ersEntity\Entity\Product")
            ->findBy(
                    array(
                        'active' => 1,
                        'visible' => 1,
                        'deleted' => 0,
                    ),
                    array(
                        'ordering' => 'ASC'
                    )
                );
        $products = array();
        foreach($tmp as $product) {
            if($product->getProductPrice()->getCharge() != null) {
                $products[] = $product;
            }
        }
        
        $agegroups = $em->getRepository("ersEntity\Entity\Agegroup")
                    ->findBy(array('priceChange' => '1'), array('agegroup' => 'DESC'));
        $deadlineService = new Service\DeadlineService();
        $deadlines = $em->getRepository("ersEntity\Entity\Deadline")
            ->findAll();
        $deadlineService->setDeadlines($deadlines);
        $deadline = $deadlineService->getDeadline();
        
        $cartContainer = new Container('cart');
        
        return new ViewModel(array(
            'products' => $products,
            'agegroups' => $agegroups,
            'deadline' => $deadline,
            'order' => $cartContainer->order,
        ));
    }
    
    public function addAction() {
        /*
         * Get and check parameters
         */
        $product_id = (int) $this->params()->fromRoute('product_id', 0);
        $item_id = (int) $this->params()->fromRoute('item_id', 0);
        if (!$product_id) {
            return $this->redirect()->toRoute('product', array(
                'action' => 'index'
            ));
        }
        $participant_id = $this->params()->fromQuery('participant_id');
        $agegroup_id = $this->params()->fromQuery('agegroup_id');
        
        if(!is_numeric($participant_id)) {
            $participant_id = null;
        }
        if(!is_numeric($agegroup_id)) {
            $agegroup_id = null;
        }
        
        /*
         * Build and set breadcrumbs
         */
        $forrest = new Service\BreadcrumbFactory();
        if(!$forrest->exists('product')) {
            $forrest->set('product', 'product');
        }
        
        $params = array();
        #$params2 = array();
        $options = array();
        
        $params['action'] = 'add';
        $params['product_id'] = $product_id;
        if($participant_id != 0) {
            $options['query'] = array(
                'participant_id' => $participant_id,
            );
        } elseif($agegroup_id != 0) {
            $options['query'] = array(
                'agegroup_id' => $agegroup_id,
            );
        }
        $params2 = $params;

        if($item_id) {
            $params['action'] = 'edit';
            # When we're in edit mode there may not be a item_id from 
            # returning back from participant (#114)
            $params2 = $params;
            $params2['item_id'] = $item_id;
        }
        $forrest->set('participant', 'product', $params, $options);
        $forrest->set('cart', 'product', $params2, $options);
        $forrest->set('bc_stay', 'product', $params2, $options);
        
        /*
         * Get data for this product
         */
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $product = $em->getRepository("ersEntity\Entity\Product")
                ->findOneBy(array('id' => $product_id));
        
        $form = $this->getServiceLocator()->get('PreReg\Form\ProductView');

        /*
         * Build and set form action url
         */
        #if(isset($participant_id) && is_numeric($participant_id) && $item_id) {
        /*if($item_id) {
            $url = $this->url()->fromRoute('cart', 
                    array(
                        'action' => 'add',
                        'item_id' => $item_id
                    ));
        } else {*/
            $url = $this->url()->fromRoute('cart', array('action' => 'add'));
        #}
        $form->setAttribute('action', $url);
        
        /*
         * Get variants for this product and subproducts
         */
        $variants = $em->getRepository("ersEntity\Entity\ProductVariant")
                ->findBy(array('Product_id' => $product_id));
        
        $productPackages = $em->getRepository("ersEntity\Entity\ProductPackage")
                ->findBy(array('Product_id' => $product_id));
        foreach($productPackages as $package) {
            $subProduct = $package->getSubProduct();
            $subVariants = $em->getRepository("ersEntity\Entity\ProductVariant")
                ->findBy(array('Product_id' => $subProduct->getId()));
            $variants = array_merge($variants, $subVariants);
        }
        
        $defaults = $this->params()->fromQuery();
        $form->setVariants($variants, $defaults);
        
        /*
         * Set form values
         */
        $form->get('submit')->setAttribute('value', 'Add to Cart');
        
        /*
         * save the item we need to edit (when in edit mode)
         */
        $cartContainer = new Container('cart');
        $item = '';
        if($item_id) {
            $item = $cartContainer->order->getItem($item_id);
            $cartContainer->editItem = $item;
        }
        
        /*
         * build participant select options
         */
        $person_options = $this->getPersonOptions($product, $participant_id);
        $form->get('participant_id')->setAttribute('options', $person_options);
        
        /*
         * Disable submit button when there is no person but the ticket is personalized
         */
        if(count($person_options) <= 0 && $product->getPersonalized()) {
            $form->get('submit')->setAttribute('disabled', 'disabled');
        }
        
        /*
         * Get and build agegroup select options
         */
        $form->get('agegroup_id')->setAttribute('options', $this->getAgegroupOptions());
        
        /*
         * get all variables for ViewModel
         */
        $breadcrumb = $forrest->get('product');
        
        $chooser = $cartContainer->chooser;
        $cartContainer->chooser = false;

        $agegroups = $em->getRepository("ersEntity\Entity\Agegroup")
                    ->findBy(array('priceChange' => '1'), array('agegroup' => 'DESC'));
        
        $deadlineService = new Service\DeadlineService();
        $deadlines = $em->getRepository("ersEntity\Entity\Deadline")
                    ->findBy(array('priceChange' => '1'));
        $deadlineService->setDeadlines($deadlines);
        $deadline = $deadlineService->getDeadline();
        
        return new ViewModel(array(
            #'participants' => $options,
            'product' => $product,
            #'participant' => $participant,
            'item' => $item,
            'form' => $form,
            'breadcrumb' => $breadcrumb,
            'bc_stay' => $forrest->get('bc_stay'),
            'chooser' => $chooser,
            'agegroups' => $agegroups,
            #'agegroup' => $agegroup,
            'deadline' => $deadline,
        ));
    }
    
    private function getPersonOptions(\ersEntity\Entity\Product $product, $participant_id=null) {
        $cartContainer = new Container('cart');
        $options = array();
        foreach($cartContainer->order->getParticipants() as $k => $v) {
            $disabled = false;
            if($v->getFirstname() == '') {
                $disabled = true;
            }
            if($v->getSurname() == '') {
                $disabled = true;
            }
            if($v->getBirthday() == null) {
                $disabled = true;
            }
            $selected = false;
            if($k == $participant_id) {
                $selected = true;
            }
            $options[] = array(
                'value' => $k,
                'label' => $v->getFirstname().' '.$v->getSurname(),
                'selected' => $selected,
                'disabled' => $disabled,
            );
        }
        $selected = false;
        if($participant_id == 0) {
            $selected = true;
        }
        if(!$product->getPersonalized() && count($options) > 0) {
            array_unshift($options, array(
                'value' => 0,
                'label' => 'do not assign this product',
                'selected' => $selected,
                ));
        }
        
        return $options;
    }
    
    private function getAgegroupOptions() {
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $agegroups = $em->getRepository("ersEntity\Entity\Agegroup")
                    ->findBy(array('priceChange' => '1'), array('agegroup' => 'DESC'));
        $options = array();
        
        foreach($agegroups as $agegroup) {
            $options[] = array(
                'value' => $agegroup->getId(),
                'label' => $agegroup->getName(),
            );
        }
        $options[] = array(
                'value' => '0',
                'label' => 'normal',
                #'selected' => false,
            );
        return $options;
    }
    
    public function editAction() {
        $forrest = new Service\BreadcrumbFactory();
        if(!$forrest->exists('product')) {
            $forrest->set('product', 'product', array('action' => 'edit'));
        }
        $forrest->set('participant', 'product', array('action' => 'edit'));
        
        $viewModel = $this->addAction();
        $viewModel->setTemplate('pre-reg/product/edit');
        
        return $viewModel;
    }
    
    public function deleteAction() {
        $forrest = new Service\BreadcrumbFactory();
        
        if(!$forrest->exists('product')) {
            $forrest->set('product', 'order');
        }
        
        $product_id = (int) $this->params()->fromRoute('product_id', 0);
        #$participant_id = (int) $this->params()->fromRoute('participant_id', 0);
        $item_id = (int) $this->params()->fromRoute('item_id', 0);
        if (!is_numeric($product_id) || !is_numeric($item_id)) {
        #if (!is_numeric($product_id) || !is_numeric($participant_id) || !is_numeric($item_id)) {
            $breadcrumb = $forrest->get('product');
            return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
        }
        
        $cartContainer = new Container('cart');
        #$participant = $cartContainer->order->getParticipantBySessionId($participant_id);
        $participant = $cartContainer->order->getParticipantByItemId($item_id);
        #$item = $cartContainer->order->getItem($participant_id, $item_id);
        $item = $cartContainer->order->getItem($item_id);
        
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $product = $em->getRepository("ersEntity\Entity\Product")
                ->findOneBy(array('id' => $product_id));
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('del', 'No');

            if ($del == 'Yes') {
                #$participant_id = (int) $request->getPost('participant_id');
                $item_id = (int) $request->getPost('item_id');
                
                #$package = $cartContainer->order->getPackageByParticipantSessionId($participant_id);
                
                #$cartContainer->order->removeItem($package->getSessionId(), $item_id);
                $cartContainer->order->removeItem($item_id);
            }

            $breadcrumb = $forrest->get('product');
            return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
        }
        
        return new ViewModel(array(
            'id'    => $product_id,
            'participant' => $participant,
            'item' => $item,
            'product' => $product,
            'breadcrumb' => $forrest->get('product'),
        ));
    }
}