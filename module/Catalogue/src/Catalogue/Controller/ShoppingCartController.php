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

class ShoppingCartController extends AbstractActionController {
    protected $table;
    
    /*public function getTable($name)
    {
        if (!isset($this->table[$name])) {
            $sm = $this->getServiceLocator();
            $className = "Catalogue\Model\\".$name."Table";
            $this->table[$name] = $sm->get($className);
        }
        return $this->table[$name];
    }*/
    
    public function getTable($name)
    {
        if (!isset($this->table[$name])) {
            $sm = $this->getServiceLocator();
            $className = "Catalogue\Model\\".$name."Table";
            $this->table[$name] = $sm->get($className);
            $this->table[$name]->setServiceLocator($sm);
        }
        return $this->table[$name];
    }
    public function indexAction()
    {
        # adding shopping cart
        $session_cart = new Container('shoppingcart');
        $session_cart->cart = 'foobarbaz';
        return new ViewModel(array(
            'products' => $this->getTable('Product')->fetchAll('order ASC'),
         ));
    }
    public function addAction() {
        # There will be a form and we get some data which lead to the generation 
        # of an Item and some ItemVariants.
        # After adding the Item the user should decide if he wants to go on 
        # shopping or head over to the checkout process.
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $form->getData();
            # Variables for Item creation
            $Item = new \Catalogue\Model\Entity\Item();
            $Item->Product_id = $data['Product_id'];
            $Item->name = $data['name'];
            $Item->charge = $data['charge'];
            $Item->amount = $data['amount'];
            $Item->status= 'ordered';
            $Item_id = $this->getTable('Item')->save($Item);
            
            
            $counter = 0;
            while(isset($data['variant_'.$counter])) {
                $ItemVariant = new \Catalogue\Model\Entity\ItemVariant();
                $ItemVariant->Item_id = $Item_id;
                $ItemVariant->ProductVariant_id = $data['variant_'.$counter.'_ProductVariant_id'];
                $ItemVariant->ProductVariantValue_id = $data['variant_'.$counter.'_ProductVariantValue_id'];
                $ItemVariant->name = $data['variant_'.$counter.'_name'];
                $ItemVariant->value = $data['variant_'.$counter.'_value'];
                $Item_id = $this->getTable('ItemVariant')->save($ItemVariant);
                
                unset($ItemVariant);
                $counter++;
            }
            
            // Redirect to question if the customer will go on shopping or 
            // head over to the checkout.
            return $this->redirect()->toRoute('cart', array(
                'action' => 'question',
            ));
        }
        
        
    }
    public function deleteAction() {
        # There will be a form which gives an identification of an Item which 
        # needs to be deleted from the ShoppingCart Session Storage
    }
    public function editAction() {
        # The edit Action will call the Product of the stored Item again and 
        # will fill in the previously given data. When click on save/edit the 
        # previously saved Item will be deleted and the new Item will be added 
        # to the ShoppingCart.
    }
    public function checkoutAction() {
        # It would be best to split the checkout process into different sub 
        # pages.
        # 1. Collecting data of the Purchaser to create the User Account
        # 2. Ask for the PaymentType
        # 3. Overview with button to order with costs
        # These pages will be unlocked when a page before is finished correctly 
        # by a SESSION variable. The user is able to jump back and forward to 
        # correct the settings.
    }
}