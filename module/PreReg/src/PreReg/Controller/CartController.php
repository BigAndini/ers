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
        $session_cart = new Container('cart');
        if(!isset($session_cart->init) && $session_cart->init == 1) {
            $session_cart->order = new Entity\Order();
            $session_cart->init = 1;
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
        $param_participant_id = (int) $this->params()->fromRoute('participant_id', 0);
        $param_item_id = (int) $this->params()->fromRoute('item_id', 0);
        /*if (!$participant_id) {
            return $this->redirect()->toRoute('order');
        }*/
        
        $this->initialize();
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            if(isset($data['addParticipant'])) {
                return $this->redirect()->toRoute('participant', array(
                    'action' => 'add',
                ));        
            }
            
            $participant_id = 0;
            if(isset($data['participant_id'])) {
                $participant_id = $data['participant_id'];
                unset($data['participant_id']);
            }
            
            # check if participant already has a personalized ticket
            $session_cart = new Container('cart');
            $package = $session_cart->order->getPackageByParticipantSessionId($participant_id);
            if($package->hasPersonalizedItem()) {
                error_log('Package for participant '.$participant_id.' already has a personalized item. What should I do?');
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
            
            $item->populate($product_data);
            $item->setPrice($product->getPrice()->getCharge());
            $item->setAmount(1);
            $item->populate((array) $product_data);
            
            
            error_log('param_participant_id: '.$param_participant_id.', param_item_id: '.$param_item_id);
            if(
                isset($param_participant_id) && is_numeric($param_participant_id) && 
                $param_item_id) {
                $session_cart->order->removeItem($param_participant_id, $param_item_id);
            }
            $session_cart->order->addItem($item, $participant_id);
            $session_cart->chooser = true;
        }
        
        $forrest = new Service\BreadcrumbFactory();
        $breadcrumb = $forrest->get('cart');
        
        #$forrest = new Container('forrest');
        #$breadcrumb = $forrest->trace->cart;
        
        return $this->redirect()->toRoute(
                $breadcrumb->route, 
                $breadcrumb->params, 
                $breadcrumb->options
            );
    }
    
    public function resetAction() {
        $session_cart = new Container('cart');
        $session_cart->init = 0;
        return new ViewModel();
    }
    
    /*
     * remove Item from cart
     */
    public function removeAction() {
        
    }
}