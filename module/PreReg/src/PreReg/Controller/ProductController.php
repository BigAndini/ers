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
use ersEntity\Entity;

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
    
    /*
     * initialize shopping cart
     */
    private function initializeCart() {
        $cartContainer = new Container('cart');
        if(!isset($cartContainer->init) && $cartContainer->init == 1) {
            $cartContainer->order = new Entity\Order();
            $cartContainer->init = 1;
        }
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
        $options = $this->params()->fromQuery();
        
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
        $forrest->set('participant', 'product', $params2, $options);
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
        #$url = $this->url()->fromRoute('cart', array('action' => 'add'));
        $url = $this->url()->fromRoute('product', $params2, $options);
        $form->setAttribute('action', $url);
        
        /*
         * Get variants for this product and subproducts
         */
        $variants = $em->getRepository("ersEntity\Entity\ProductVariant")
                ->findBy(array('Product_id' => $product_id));
        $defaults = $this->params()->fromQuery();
        #$form->setVariants($variants, $defaults);
        
        $package_info = array();
        foreach($variants as $variant) {
            $package_info[$variant->getId()] = false;
        }
        
        $productPackages = $em->getRepository("ersEntity\Entity\ProductPackage")
                ->findBy(array('Product_id' => $product_id));
        foreach($productPackages as $package) {
            $subProduct = $package->getSubProduct();
            $subVariants = $em->getRepository("ersEntity\Entity\ProductVariant")
                ->findBy(array('Product_id' => $subProduct->getId()));
            foreach($subVariants as $variant) {
                $package_info[$variant->getId()] = true;
            }
            $variants = array_merge($variants, $subVariants);
        }
        
        $form->setVariants($variants, $defaults, $package_info);
        
        /*
         * get the according deadline
         */
        $deadlineService = new Service\DeadlineService();
        $deadlines = $em->getRepository("ersEntity\Entity\Deadline")
                    ->findBy(array('priceChange' => '1'));
        $deadlineService->setDeadlines($deadlines);
        $deadline = $deadlineService->getDeadline();
        
        /*
         * Here starts the cart add Action
         */
        $logger = $this
            ->getServiceLocator()
            ->get('Logger');
        
        $this->initializeCart();
        $cartContainer = new Container('cart');
        
        $formfail = false;
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $inputFilter = $form->getInputFilter();
            $form->setInputFilter($inputFilter); 
            $form->setData($request->getPost()); 
        
            if($form->isValid())
            { 
                $data = $request->getPost();
                $logger->info(var_export($data, true));

                /*
                 * get needed variables
                 */
                $participant_id = 0;
                if(isset($data['participant_id'])) {
                    $participant_id = $data['participant_id'];
                    unset($data['participant_id']);
                }
                $agegroup_id = 0;
                if(isset($data['agegroup_id'])) {
                    $agegroup_id = $data['agegroup_id'];
                    unset($data['agegroup_id']);
                }
                
                /*
                 *  check if participant already has a personalized ticket
                 */
                $package = $cartContainer->order->getPackageByParticipantSessionId($participant_id);
                if($package != null && $package->hasPersonalizedItem()) {
                    $logger->warn('Package for participant '.$participant_id.' already has a personalized item. What should I do?');
                }
                
                /*
                 * get according product entity from database
                 */
                $em = $this
                    ->getServiceLocator()
                    ->get('Doctrine\ORM\EntityManager');
                $product = $em->getRepository("ersEntity\Entity\Product")
                        ->findOneBy(array('id' => $data['Product_id']));

                /*
                 * search the according agegroup
                 */
                if($participant_id != 0) {
                    $participant = $cartContainer->order->getParticipantBySessionId($participant_id);

                    $agegroupService = $this->getServiceLocator()
                            ->get('PreReg\Service\AgegroupService');
                    $agegroup = $agegroupService->getAgegroupByUser($participant);
                } elseif($agegroup_id != 0) {
                    $agegroup = $em->getRepository("ersEntity\Entity\Agegroup")
                            ->findOneBy(array('id' => $agegroup_id));
                } else {
                    $logger->emerg('Unable to add/edit product!');
                }
                
                /*
                 *  prepare product data to populate item
                 */
                $product_data = $product->getArrayCopy();
                $product_data['Product_id'] = $product_data['id'];
                unset($product_data['id']);
                
                /*
                 * build up item entity
                 */
                $item = new Entity\Item();
                $item->setPrice($product->getProductPrice($agegroup, $deadline)->getCharge());
                $item->setAmount(1);
                if($agegroup) {
                    $item->setAgegroup($agegroup->getAgegroup());
                }
                $item->populate((array) $product_data);
                
                /*
                 * add variant data to item entity
                 */
                $variant_data = $data['pv'];
                foreach($product->getProductVariants() as $variant) {
                    $value = $em->getRepository("ersEntity\Entity\ProductVariantValue")
                        ->findOneBy(array('id' => $variant_data[$variant->getId()]));
                    if($value && !$value->getDisabled()) {
                        $itemVariant = new Entity\ItemVariant();
                        $itemVariant->populateFromEntity($variant, $value);
                        $item->addItemVariant($itemVariant);
                        #$logger->info('VARIANT '.$variant->getName().': '.$value->getValue());
                    } else {
                        $logger->warn('Unable to find value for variant: '.$variant->getName().' (id: '.$variant->getId().')');
                    }
                }
                
                /*
                 * check product packages and add data to item entity
                 */
                $productPackages = $em->getRepository("ersEntity\Entity\ProductPackage")
                    ->findBy(array('Product_id' => $product->getId()));
                foreach($productPackages as $package) {
                    $subProduct = $package->getSubProduct();
                    $subItem = new Entity\Item();
                    $subItem->setPrice(0);
                    $subItem->setAmount($package->getAmount());
                    if($agegroup) {
                        $subItem->setAgegroup($agegroup->getAgegroup());
                    }
                    $product_data = $subProduct->getArrayCopy();
                    $product_data['Product_id'] = $product_data['id'];
                    unset($product_data['id']);
                    $subItem->populate($product_data);

                    $add = false;
                    foreach($subProduct->getProductVariants() as $variant) {
                        $value = $em->getRepository("ersEntity\Entity\ProductVariantValue")
                            ->findOneBy(array('id' => $variant_data[$variant->getId()]));
                        if($value && !$value->getDisabled()) {
                            $add = true;
                            $itemVariant = new Entity\ItemVariant();
                            $itemVariant->populateFromEntity($variant, $value);
                            $subItem->addItemVariant($itemVariant);
                            #$logger->info('VARIANT '.$variant->getName().': '.$value->getValue());
                        } else {
                            $logger->warn('Unable to find value for variant of subItem: '.$variant->getName().' (id: '.$variant->getId().')');
                        }
                    }

                    if($add) {
                        $itemPackage = new Entity\ItemPackage();
                        $itemPackage->setSurItem($item);
                        $itemPackage->setSubItem($subItem);
                        $item->addItemPackageRelatedBySurItemId($itemPackage);
                    }
                }
                
                /*
                 * delete the item we have edited when we're in edit mode
                 */
                if(is_numeric($item_id) && $item_id != 0) {
                    $cartContainer->order->removeItem($item_id);
                }
                
                /*if(isset($cartContainer->editItem) && $cartContainer->editItem instanceof Entity\Item) {
                    $cartContainer->order->removeItem($cartContainer->editItem->getSessionId());
                    unset($cartContainer->editItem);
                }*/
                
                /*
                 * add the newly created item
                 */
                $cartContainer->order->addItem($item, $participant_id);
                
                /*
                 * the chooser for product, shopping cart or stay on product 
                 * page will be visible for two pageloads. This prevents the 
                 * chooser overlay from getting displayd in strange situations
                 */
                $cartContainer->chooser = true;
                $cartContainer->chooserCount = 2;
                
                /*
                 * go the route of the breadcrumbs and find the way back. :)
                 */
                $forrest = new Service\BreadcrumbFactory();
                if(!$forrest->exists('cart')) {
                    $forrest->set('cart', 'product');
                }
                $breadcrumb = $forrest->get('cart');
                $logger->info(var_export($breadcrumb, true));

                #return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
            } else {
                $logger = $this
                    ->getServiceLocator()
                    ->get('Logger');
                $logger->warn($form->getMessages());
                $formfail = true;
            } 
        }
        
        /*
         * Set form values
         */
        $form->get('submit')->setAttribute('value', 'Add to Cart');
        
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
        $form->get('agegroup_id')->setAttribute('options', $this->getAgegroupOptions($agegroup_id));
        
        /*
         * get all variables for ViewModel
         */
        $breadcrumb = $forrest->get('product');
        
        $chooser = $cartContainer->chooser;
        $cartContainer->chooser = false;

        $agegroups = $em->getRepository("ersEntity\Entity\Agegroup")
                    ->findBy(array('priceChange' => '1'), array('agegroup' => 'DESC'));
        
        return new ViewModel(array(
            'product' => $product,
            'formfail' => $formfail,
            #'item' => $item,
            'form' => $form,
            'breadcrumb' => $breadcrumb,
            'bc_stay' => $forrest->get('bc_stay'),
            'chooser' => $chooser,
            'agegroups' => $agegroups,
            'deadline' => $deadline,
        ));
    }
    
    private function getPersonOptions(\ersEntity\Entity\Product $product, $participant_id=null) {
        $cartContainer = new Container('cart');
        $options = array();
        foreach($cartContainer->order->getParticipants() as $v) {
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
            if($v->getSessionId() == $participant_id) {
                $selected = true;
            }
            $options[] = array(
                'value' => $v->getSessionId(),
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
    
    private function getAgegroupOptions($agegroup_id = null) {
        $em = $this
            ->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $agegroups = $em->getRepository("ersEntity\Entity\Agegroup")
                    ->findBy(array('priceChange' => '1'), array('agegroup' => 'DESC'));
        $options = array();
        
        foreach($agegroups as $agegroup) {
            if($agegroup_id == $agegroup->getId()) {
                $selected = true;
            } else {
                $selected = false;
            }
            $options[] = array(
                'value' => $agegroup->getId(),
                'label' => $agegroup->getName(),
                'selected' => $selected,
            );
        }
        if($agegroup_id == null) {
            $selected = false;
        } elseif($agegroup_id == 0) {
            $selected = true;
        } else {
            $selected = false;
        }
        $options[] = array(
                'value' => '0',
                'label' => 'normal',
                'selected' => $selected,
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
        if($viewModel instanceof ViewModel) {
            $viewModel->setTemplate('pre-reg/product/edit');
        }
        
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