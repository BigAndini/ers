<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace PreReg\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use ErsBase\Entity;
use ErsBase\Service;
use PreReg\Form;

class CartController extends AbstractActionController {
    /*
     * initialize shopping cart
     */
    private function initialize() {
        $cartContainer = new Container('cart');
        if(!isset($cartContainer->init) && $cartContainer->init == 1) {
            $cartContainer->order = new Entity\Order();
            $cartContainer->init = 1;
        }
    }
    
    /*
     * overview of the shopping cart
     */
    public function indexAction() {
        $this->initialize();
        return $this->redirect()->toRoute('order', array(
            'action' => 'index',
        ));
    }
    
    /*
     * add Item to cart
     * 
     * this function has moved to ProductController addAction
     */
    public function addAction() {
        return false;
        
        $logger = $this->getServiceLocator()->get('Logger');
        
        $this->initialize();
        
        $form = $this->getServiceLocator()
                ->get('PreReg\Form\ProductView');
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($form->getInputFilter()); 
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
                $cartContainer = new Container('cart');
                $package = $cartContainer->order->getPackageByParticipantSessionId($participant_id);
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
                 *  prepare product data to populate item
                 */
                $product_data = $product->getArrayCopy();
                $product_data['Product_id'] = $product_data['id'];
                unset($product_data['id']);
                
                /*
                 * search the according agegroup
                 */
                if($participant_id != 0) {
                    $participant = $cartContainer->order->getParticipantBySessionId($participant_id);

                    $agegroupService = $this->getServiceLocator()
                            ->get('ErsBase\Service\AgegroupService');
                    $agegroup = $agegroupService->getAgegroupByUser($participant);
                } elseif($agegroup_id != 0) {
                    $agegroup = $em->getRepository('ErsBase\Entity\Agegroup')
                            ->findOneBy(array('id' => $agegroup_id));
                } else {
                    $logger->emerg('Unable to add/edit product!');
                }

                /*
                 * get deadline
                 */
                $deadlineService = $this->getServiceLocator()
                    ->get('ErsBase\Service\DeadlineService:price');
                /*$deadlineService = new Service\DeadlineService();
                $deadlines = $em->getRepository('ErsBase\Entity\Deadline')
                        ->findBy(array('price_change' => '1'));
                $deadlineService->setDeadlines($deadlines);*/
                $deadline = $deadlineService->getDeadline();
                
                /*
                 * build up item entity
                 */
                $item = new Entity\Item();
                #$item->populate($product_data);
                $item->setPrice($product->getProductPrice($agegroup, $deadline)->getCharge());
                $item->setAmount(1);
                $item->populate((array) $product_data);

                /*
                 * add variant data to item entity
                 */
                $variant_data = $data['pv'];
                foreach($product->getProductVariants() as $variant) {
                    $value = $em->getRepository('ErsBase\Entity\ProductVariantValue')
                        ->findOneBy(array('id' => $variant_data[$variant->getId()]));
                    if($value) {
                        $itemVariant = new Entity\ItemVariant();
                        $itemVariant->populateFromEntity($variant, $value);
                        $item->addItemVariant($itemVariant);
                    } else {
                        $logger->warn('Unable to find value for variant: '.$variant->getName().' (id: '.$variant->getId().')');
                    }
                }
                
                /*
                 * check product packages and add data to item entity
                 */
                $productPackages = $em->getRepository('ErsBase\Entity\ProductPackage')
                    ->findBy(array('Product_id' => $product->getId()));
                foreach($productPackages as $package) {
                    $subProduct = $package->getSubProduct();
                    $subItem = new Entity\Item();
                    $subItem->setPrice(0);
                    $subItem->setAmount($package->getAmount());
                    $product_data = $subProduct->getArrayCopy();
                    $product_data['Product_id'] = $product_data['id'];
                    unset($product_data['id']);
                    $subItem->populate($product_data);

                    foreach($subProduct->getProductVariants() as $variant) {
                        $value = $em->getRepository('ErsBase\Entity\ProductVariantValue')
                            ->findOneBy(array('id' => $variant_data[$variant->getId()]));
                        if($value) {
                            $itemVariant = new Entity\ItemVariant();
                            $itemVariant->populateFromEntity($variant, $value);
                            $subItem->addItemVariant($itemVariant);
                        } else {
                            $logger->warn('Unable to find value for variant of subItem: '.$variant->getName().' (id: '.$variant->getId().')');
                        }
                    }

                    $itemPackage = new Entity\ItemPackage();
                    $itemPackage->setItem($item);
                    $itemPackage->setSubItem($subItem);
                    $item->addChildItem($itemPackage);
                }
                
                /*
                 * delete the item we have edited when we're in edit mode
                 */
                if(isset($cartContainer->editItem) && $cartContainer->editItem instanceof Entity\Item) {
                    $cartContainer->order->removeItem($cartContainer->editItem->getSessionId());
                    unset($cartContainer->editItem);
                }
                
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
                $forrest = new Service\BreadcrumbService();
                if(!$forrest->exists('cart')) {
                    $forrest->set('cart', 'product');
                }
                $breadcrumb = $forrest->get('cart');

                return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
            } else {
                $logger = $this->getServiceLocator()->get('Logger');
                $logger->warn($form->getMessages());
            } 
        }
    }
    
    public function resetAction() {
        $logger = $this->getServiceLocator()->get('Logger');

        $breadcrumbService = new Service\BreadcrumbService();
        
        $emptycart = false;

        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');

        $form = new Form\SimpleForm($em);
        $form->get('submit')->setAttributes(array(
            'value' => _('Clear Shopping Cart'),
            'class' => 'btn btn-danger',
        ));

        if ($this->request->isPost()) {
            $form->setData($this->request->getPost());

            if ($form->isValid()) {
                
                $orderService = $this->getServiceLocator()->get('ErsBase\Service\OrderService');
                $order = $orderService->getOrder();        
                
                # TODO: move delete order to OrderService
                foreach($order->getPackages() as $package) {
                    $participant = $package->getUser();
                    if(!$participant->getActive()) {
                        $em->remove($participant);
                    }
                    foreach($package->getItems() as $item) {
                        $em->remove($item);
                    }
                    $em->remove($package);
                }
                $em->remove($order);
                
                $cartContainer = new Container('cart');
                $cartContainer->init = 0;
                $emptycart = true;
            } else {
                $logger->warn($form->getMessages());
            }
        }

        return new ViewModel(array(
            'form' => $form,
            'breadcrumb' => $breadcrumbService->get('cart'),
            'emptycart' => $emptycart,
        ));
    }
}