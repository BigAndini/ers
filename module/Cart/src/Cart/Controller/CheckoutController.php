<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cart\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
#use StickyNotes\Model\User;
#use Cart\Form\UserForm;
use Zend\Session\Container;

class CheckoutController extends AbstractActionController {
    protected $table;
    
    /*public function getTable($name)
    {
        if (!isset($this->table[$name])) {
            $sm = $this->getServiceLocator();
            $className = "Cart\Model\\".$name."Table";
            $this->table[$name] = $sm->get($className);
        }
        return $this->table[$name];
    }*/
    
    public function getTable($name)
    {
        if (!isset($this->table[$name])) {
            $sm = $this->getServiceLocator();
            $className = "Cart\Model\\".$name."Table";
            $this->table[$name] = $sm->get($className);
            $this->table[$name]->setServiceLocator($sm);
        }
        return $this->table[$name];
    }
    
    /*
     * Overview of the shopping cart as is with possibilities to jump to the 
     * single products to be able to edit them.
     */
    public function indexAction()
    {
        return new ViewModel();
    }
    
    /*
     * Overview of the possible payment types; choose one payment type and give 
     * according information.
     */
    public function paymentAction()
    {
        return new ViewModel();
    }
    
    /*
     * Overview of the shopping cart; no possibility to change anything; buy 
     * this shoppingcart as is.
     */
    public function finalizeAction()
    {
        return new ViewModel();
    }
}