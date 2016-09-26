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
use ErsBase\Service;
use Zend\Session\Container;
use ErsBase\Entity;

class ProductController extends AbstractActionController {
    public function indexAction()
    {
        $this->getServiceLocator()->get('ErsBase\Service\TicketCounterService')
                ->checkLimits();
        
        $breadcrumbService = new Service\BreadcrumbService();
        $breadcrumbService->reset();
        $breadcrumbService->set('participant', 'product');
        
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $tmp = $em->getRepository('ErsBase\Entity\Product')
            ->findBy(
                    array(
                        'active' => 1,
                        'visible' => 1,
                        'deleted' => 0,
                    ),
                    array(
                        'position' => 'ASC'
                    )
                );
        $products = array();
        foreach($tmp as $product) {
            if($product->getProductPrice()->getCharge() != null) {
                $products[] = $product;
            }
        }
        
        $agegroups = $em->getRepository('ErsBase\Entity\Agegroup')
                    ->findBy(array('price_change' => '1'), array('agegroup' => 'DESC'));
        
        $deadlineService = $this->getServiceLocator()
                ->get('ErsBase\Service\DeadlineService:price');
        $deadline = $deadlineService->getDeadline();
        
        #$cartContainer = new Container('cart');
        $orderService = $this->getServiceLocator()
                ->get('ErsBase\Service\OrderService');
        
        return new ViewModel(array(
            'products' => $products,
            'agegroups' => $agegroups,
            'deadline' => $deadline,
            #'order' => $cartContainer->order,
            'order' => $orderService->getOrder(),
            'ers_config' => $this->getServiceLocator()->get('Config')['ERS'],
        ));
    }
    
    /*
     * initialize shopping cart
     */
    /*private function initializeCart() {
        $cartContainer = new Container('cart');
        if(!isset($cartContainer->init) && $cartContainer->init == 1) {
            $cartContainer->order = new Entity\Order();
            $cartContainer->init = 1;
        }
    }*/
    
    public function addAction() {
        $this->getServiceLocator()->get('ErsBase\Service\TicketCounterService')
                ->checkLimits();
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
        $breadcrumbService = new Service\BreadcrumbService();
        if(!$breadcrumbService->exists('product')) {
            $breadcrumbService->set('product', 'product');
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
        $breadcrumbService->set('participant', 'product', $params2, $options);
        $breadcrumbService->set('cart', 'product', $params2, $options);
        $breadcrumbService->set('product', 'product', $params2, $options);
        $breadcrumbService->set('bc_stay', 'product', $params2, $options);
        
        /*
         * Get data for this product
         */
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $product = $em->getRepository('ErsBase\Entity\Product')
                ->findOneBy(array('id' => $product_id));
        $status = $em->getRepository('ErsBase\Entity\Status')
                ->findOneBy(array('value' => 'order pending'));
        # TODO: check if order pending status was found.
        
        $form = $this->getServiceLocator()->get('PreReg\Form\ProductView');
        #$url = $this->url()->fromRoute('cart', array('action' => 'add'));
        $url = $this->url()->fromRoute('product', $params2, $options);
        $form->setAttribute('action', $url);
        
        /*
         * Get variants for this product and subproducts
         */
        $variants = $em->getRepository('ErsBase\Entity\ProductVariant')
                ->findBy(array('Product_id' => $product_id));
        $defaults = $this->params()->fromQuery();
        
        $package_info = array();
        foreach($variants as $variant) {
            $package_info[$variant->getId()] = false;
        }
        
        $productPackages = $em->getRepository('ErsBase\Entity\ProductPackage')
                ->findBy(array('Product_id' => $product_id));
        foreach($productPackages as $package) {
            $subProduct = $package->getSubProduct();
            $subVariants = $em->getRepository('ErsBase\Entity\ProductVariant')
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
        $deadlineService = $this->getServiceLocator()
                ->get('ErsBase\Service\DeadlineService:price');
        $deadline = $deadlineService->getDeadline();
        
        /*
         * Here starts the cart add Action
         */
        $logger = $this->getServiceLocator()->get('Logger');
 
        #$this->initializeCart();
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
                $orderService = $this->getServiceLocator()
                        ->get('ErsBase\Service\OrderService');
                $order = $orderService->getOrder();
                $package = $order->getPackageByParticipantId($participant_id);
                if($package != null && $package->hasPersonalizedItem()) {
                    $logger->warn('Package for participant '.$participant_id.' already has a personalized item. What should I do?');
                }
                
                /*
                 * get according product entity from database
                 */
                $em = $this->getServiceLocator()
                    ->get('Doctrine\ORM\EntityManager');
                $product = $em->getRepository('ErsBase\Entity\Product')
                        ->findOneBy(array('id' => $data['Product_id']));

                /*
                 * search the according agegroup
                 */
                $agegroup = null;
                if($participant_id != 0) {
                    $participant = $order->getParticipantById($participant_id);

                    if($participant instanceof Entity\User) {
                        $agegroupService = $this->getServiceLocator()
                                ->get('ErsBase\Service\AgegroupService:price');
                        $agegroup = $agegroupService->getAgegroupByUser($participant);
                    }
                } elseif($agegroup_id != 0) {
                    $agegroup = $em->getRepository('ErsBase\Entity\Agegroup')
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
                $item->setStatus($status);
                $item->setProduct($product);
                $item->setAmount(1);
                if($agegroup != null) {
                    $item->setAgegroup($agegroup->getAgegroup());
                }
                $item->populate((array) $product_data);
                
                /*
                 * add variant data to item entity
                 */
                $variant_data = $data['pv'];
                foreach($product->getProductVariants() as $variant) {
                    $value = $em->getRepository('ErsBase\Entity\ProductVariantValue')
                        ->findOneBy(array('id' => $variant_data[$variant->getId()]));
                    if($value && !$value->getDisabled()) {
                        $itemVariant = new Entity\ItemVariant();
                        $itemVariant->populateFromEntity($variant, $value);
                        $itemVariant->setProductVariant($variant);
                        $itemVariant->setProductVariantValue($value);
                        $item->addItemVariant($itemVariant);
                        $itemVariant->setItem($item);
                    } else {
                        $logger->warn('Unable to find value for variant: '.$variant->getName().' (id: '.$variant->getId().')');
                    }
                }
                
                /*
                 * delete the item we edit when we're in edit mode
                 */
                if(is_numeric($item_id) && $item_id != 0) {
                    #$order->removeItem($item_id);
                    $orderService->removeItemById($item_id);
                    #$cartContainer->order->removeItem($item_id);
                }
                
                /*
                 * check product packages and add data to item entity
                 */
                $productPackages = $em->getRepository('ErsBase\Entity\ProductPackage')
                    ->findBy(array('Product_id' => $product->getId()));
                foreach($productPackages as $package) {
                    $subProduct = $package->getSubProduct();
                    $subItem = new Entity\Item();
                    $subItem->setStatus($status);
                    $subItem->setProduct($subProduct);
                    $subItem->setAmount($package->getAmount());
                    if($agegroup) {
                        $subItem->setAgegroup($agegroup->getAgegroup());
                    }
                    $product_data = $subProduct->getArrayCopy();
                    $product_data['Product_id'] = $product_data['id'];
                    unset($product_data['id']);
                    $subItem->populate($product_data);
                    $subItem->setPrice(0);

                    $add = false;
                    foreach($subProduct->getProductVariants() as $variant) {
                        $value = $em->getRepository('ErsBase\Entity\ProductVariantValue')
                            ->findOneBy(array('id' => $variant_data[$variant->getId()]));
                        if($value && !$value->getDisabled()) {
                            $add = true;
                            $itemVariant = new Entity\ItemVariant();
                            $itemVariant->populateFromEntity($variant, $value);
                            $itemVariant->setProductVariant($variant);
                            $itemVariant->setProductVariantValue($value);
                            $subItem->addItemVariant($itemVariant);
                            $itemVariant->setItem($subItem);
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
                 * add the newly created item
                 */
                $order->addItem($item, $participant_id);
                
                $em->persist($order);
                $em->flush();
                
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
                $breadcrumbService = new Service\BreadcrumbService();
                if(!$breadcrumbService->exists('product')) {
                    $breadcrumbService->set('product', 'product');
                }
                $breadcrumb = $breadcrumbService->get('product');

                return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
            } else {
                $logger = $this->getServiceLocator()->get('Logger');
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
        $optionService = $this->getServiceLocator()
                ->get('ErsBase\Service\OptionService');
        $person_options = $optionService->getPersonOptions($product, $participant_id);
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
        $form->get('agegroup_id')->setAttribute('options', $optionService->getAgegroupOptions($agegroup_id));
        
        /*
         * get all variables for ViewModel
         */
        $breadcrumb = $breadcrumbService->get('product');
        
        $chooser = $cartContainer->chooser;
        $cartContainer->chooser = false;

        $agegroups = $em->getRepository('ErsBase\Entity\Agegroup')
                    ->findBy(array('price_change' => '1'), array('agegroup' => 'DESC'));
        
        $participantForm = new Form\Participant(); 
        $participantForm->setServiceLocator($this->getServiceLocator());
        $participantForm->get('Country_id')->setValueOptions($optionService->getCountryOptions());
        
        return new ViewModel(array(
            'product' => $product,
            'formfail' => $formfail,
            #'item' => $item,
            'form' => $form,
            'participantForm' => $participantForm,
            'breadcrumb' => $breadcrumb,
            'bc_stay' => $breadcrumbService->get('bc_stay'),
            'chooser' => $chooser,
            'agegroups' => $agegroups,
            'deadline' => $deadline,
        ));
    }
    
    
    public function editAction() {
        $this->getServiceLocator()->get('ErsBase\Service\TicketCounterService')
                ->checkLimits();
        
        $breadcrumbService = new Service\BreadcrumbService();
        if(!$breadcrumbService->exists('product')) {
            $breadcrumbService->set('product', 'product', array('action' => 'edit'));
        }
        #$breadcrumbService->set('participant', 'product', array('action' => 'edit'));
        
        $viewModel = $this->addAction();
        if($viewModel instanceof ViewModel) {
            $viewModel->setTemplate('pre-reg/product/edit');
        } else {
            $breadcrumbService = new Service\BreadcrumbService();
            if(!$breadcrumbService->exists('product')) {
                $breadcrumbService->set('product', 'product');
            }
            $breadcrumb = $breadcrumbService->get('product');

            return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
        }
        
        return $viewModel;
    }
    
    public function deleteAction() {
        $logger = $this->getServiceLocator()->get('Logger');

        $breadcrumbService = new Service\BreadcrumbService();

        $breadcrumbService = new Service\BreadcrumbService();
        
        if(!$breadcrumbService->exists('product')) {
            $breadcrumbService->set('product', 'order');
        }
        
        $product_id = (int) $this->params()->fromRoute('product_id', 0);
        $id = (int) $this->params()->fromRoute('item_id', 0);
        if (!is_numeric($id)) {
        #if (!is_numeric($product_id) || !is_numeric($item_id)) {
            $breadcrumb = $breadcrumbService->get('product');
            return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
        }
        
        $orderService = $this->getServiceLocator()
                ->get('ErsBase\Service\OrderService');
        $order = $orderService->getOrder();
        
        $participant = $order->getParticipantByItemId($id);
        $item = $order->getItem($id);
        $product = $item->getProduct();
        
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');

        $form = new Form\SimpleForm($em);
        $form->get('submit')->setAttributes(array(
            'value' => 'Delete',
            'class' => 'btn btn-danger',
        ));
        
        $form->bind($item);

        if ($this->request->isPost()) {
            $form->setData($this->request->getPost());

            if ($form->isValid()) {
                $orderService = $this->getServiceLocator()
                        ->get('ErsBase\Service\OrderService');
                $orderService->removeItemById($item->getId());
                
                $breadcrumb = $breadcrumbService->get('product');
                return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
            } else {
                $logger->warn($form->getMessages());
            }
        }

        return new ViewModel(array(
            'form' => $form,
            'participant' => $participant,
            'item' => $item,
            'product' => $product,
            'breadcrumb' => $breadcrumbService->get('product'),
        ));
    }
}