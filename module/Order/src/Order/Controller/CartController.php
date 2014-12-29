<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Order\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use Order\Model;
use Order\Form;

class CartController extends AbstractActionController {
    protected $table;
    
    public function getTable($name)
    {
        if (!isset($this->table[$name])) {
            
            $sm = $this->getServiceLocator();
            $className = "Order\Model\\".$name."Table";
            $this->table[$name] = $sm->get($className);
            $this->table[$name]->setServiceLocator($sm);
        }
        return $this->table[$name];
    }
    
    /*
     * initialize shopping cart
     */
    
    private function initialize() {
        $session_cart = new Container('cart');
        if(!isset($session_cart->init)) {
            $session_cart->order = new Model\Entity\Order();
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
        error_log('add to Cart');
        $request = $this->getRequest();
        if ($request->isPost()) {
            error_log('POST Request');
            $data = $request->getPost();
            foreach($data as $k => $v) {
                error_log('key: '.$k.' value: '.$v);
            }
            $Product_id = $data['Product_id'];
            $product = $this->getTable('Product')->getById($Product_id);
            $price = $product->getPrice();
            error_log('product price charge: '.$price->charge);
        }
        return $this->redirect()->toRoute('product', array(
            'action' => 'index',
        ));
    }
    
    /*
     * remove Item from cart
     */
    public function removeAction() {
        
    }
    
    /*
     * delete a Package of this Order
     */
    public function deleteAction() {
        
    }
}