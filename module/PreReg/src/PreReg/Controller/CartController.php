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
#use PreReg\Model;
use ersEntity\Entity;
use PreReg\Form;

class CartController extends AbstractActionController {
    /*
     * initialize shopping cart
     */
    private function initialize() {
        $session_cart = new Container('cart');
        if(!isset($session_cart->init) && $session_cart->init == 1) {
            $session_cart->order = new Entity\Order();
            $session_cart->init = 1;
        } else {
            error_log('Cart is already initialized'); 
        }
    }
    
    /*
     * overview of the shopping cart
     */
    public function indexAction() {
        $this->initialize();
        return new ViewModel();
    }
    
    /*
     * add Item to cart
     */
    public function addAction() {
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
            
            $item = new Entity\Item();
            $em = $this
                ->getServiceLocator()
                ->get('Doctrine\ORM\EntityManager');
            $product = $em->getRepository("ersEntity\Entity\Product")
                    ->findOneBy(array('id' => $data['Product_id']));
            #error_log(var_export($product->getArrayCopy(),true));
            $item->populate($product->getArrayCopy());
            $item->populate((array) $data);
            
            $session_cart = new Container('cart');
            $session_cart->order->addItem($item, $participant_id);
        }
        return $this->redirect()->toRoute('product', array(
            'action' => 'index',
        ));
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
    
    /*
     * delete a Item from Cart
     */
    public function delitemAction() {
        
    }
    /*
     * delete a Package from Cart
     */
    public function delpackageAction() {
        
    }
}