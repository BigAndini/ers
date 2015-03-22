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
use ersEntity\Entity;
use PreReg\Service;

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
     */
    public function addAction() {
        $logger = $this
            ->getServiceLocator()
            ->get('Logger');
        
        #$param_participant_id = (int) $this->params()->fromRoute('participant_id', 0);
        #$param_item_id = (int) $this->params()->fromRoute('item_id', 0);
        /*if (!$participant_id) {
            return $this->redirect()->toRoute('order');
        }*/
        
        $this->initialize();
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            
            $logger->info(var_export($data, true));
            
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
            
            # check if participant already has a personalized ticket
            $cartContainer = new Container('cart');
            $package = $cartContainer->order->getPackageByParticipantSessionId($participant_id);
            if($package != null && $package->hasPersonalizedItem()) {
                $logger->warn('Package for participant '.$participant_id.' already has a personalized item. What should I do?');
            }
            
            $item = new Entity\Item();
            $em = $this
                ->getServiceLocator()
                ->get('Doctrine\ORM\EntityManager');
            $product = $em->getRepository("ersEntity\Entity\Product")
                    ->findOneBy(array('id' => $data['Product_id']));
            
            # prepare product data to populate item
            $product_data = $product->getArrayCopy();
            $product_data['Product_id'] = $product_data['id'];
            unset($product_data['id']);
            
            if($participant_id != 0) {
                $participant = $cartContainer->order->getParticipantBySessionId($participant_id);
            
                $agegroupService = new Service\AgegroupService();
                $agegroups = $em->getRepository("ersEntity\Entity\Agegroup")
                        ->findBy(array('priceChange' => '1'));
                $agegroupService->setAgegroups($agegroups);
                $agegroup = $agegroupService->getAgegroupByUser($participant);
            } elseif($agegroup_id != 0) {
                $agegroup = $em->getRepository("ersEntity\Entity\Agegroup")
                        ->findOneBy(array('id' => $agegroup_id));
            } else {
                $logger->emerg('Unable to add/edit product!');
            }
            
            $deadlineService = new Service\DeadlineService();
            $deadlines = $em->getRepository("ersEntity\Entity\Deadline")
                    ->findBy(array('priceChange' => '1'));
            $deadlineService->setDeadlines($deadlines);
            $deadline = $deadlineService->getDeadline();
            
            #$item->populate($product_data);
            $item->setPrice($product->getProductPrice($agegroup, $deadline)->getCharge());
            $item->setAmount(1);
            $item->populate((array) $product_data);
            
            for($i=0; $i < count($product->getProductVariants()); $i++) {
                if(!isset($data['variant_id_'.$i])) {
                    $logger->notice('unable to find variant_id_'.$i.' in POST data.');
                    continue;
                }
                if(!isset($data['variant_value_'.$i])) {
                    $logger->notice('unable to find variant_value_'.$i.' in POST data.');
                    continue;
                }
                $logger->info('variant_id_'.$i.': '.$data['variant_id_'.$i]);
                $variant = $em->getRepository("ersEntity\Entity\ProductVariant")
                    ->findOneBy(array('id' => $data['variant_id_'.$i]));
                $logger->info('variant_value_'.$i.': '.$data['variant_value_'.$i]);
                $value = $em->getRepository("ersEntity\Entity\ProductVariantValue")
                    ->findOneBy(array('id' => $data['variant_value_'.$i]));
                if($value) {
                    $itemVariant = new Entity\ItemVariant();
                    $itemVariant->populateFromEntity($variant, $value);
                    $item->addItemVariant($itemVariant);
                }
            }
            
            if(isset($cartContainer->editItem) && $cartContainer->editItem instanceof Entity\Item) {
                $cartContainer->order->removeItem($cartContainer->editItem->getSessionId());
                unset($cartContainer->editItem);
            }
            
            error_log('adding item to participant: '.$participant_id);
            $cartContainer->order->addItem($item, $participant_id);
            $cartContainer->chooser = true;
        }
        
        $forrest = new Service\BreadcrumbFactory();
        if(!$forrest->exists('cart')) {
            $forrest->set('cart', 'product');
        }
        $breadcrumb = $forrest->get('cart');
        $logger->info(var_export($breadcrumb, true));
        
        return $this->redirect()->toRoute($breadcrumb->route, $breadcrumb->params, $breadcrumb->options);
    }
    
    public function resetAction() {
        $cartContainer = new Container('cart');
        $cartContainer->init = 0;
        return new ViewModel();
    }
    
    /*
     * remove Item from cart
     */
    public function removeAction() {
        
    }
}