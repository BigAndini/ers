<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Admin\Form;

class IndexController extends AbstractActionController {
    
    public function indexAction() {
        $order_search_form = new Form\SearchOrder();
        return new ViewModel(array(
            'order_search_form' => $order_search_form,
        ));
    }
}