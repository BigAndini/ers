<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace OnsiteReg\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use OnsiteReg\Form;

class IndexController extends AbstractActionController {
    public function indexAction() {
        $form = new Form\Search();
        return new ViewModel(array(
            'form' => $form,
        ));
    }
}