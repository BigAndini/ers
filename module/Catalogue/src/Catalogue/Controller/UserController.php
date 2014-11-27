<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Catalogue\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
#use StickyNotes\Model\User;
#use Catalogue\Form\UserForm;
use Zend\Session\Container;

class UserController extends AbstractActionController {
    protected $_table;
    
    public function getTable()
    {
        if (!$this->_table) {
            $sm = $this->getServiceLocator();
            $this->_table = $sm->get('Catalogue\Model\UserTable');
        }
        return $this->_table;
    }
    public function indexAction()
    {
        $session_cart = new Container('shoppingcart');
        return new ViewModel(array(
            'users' => $this->getTable()->fetchAll(),
            'cart' => $session_cart->cart,
         ));
    }
}