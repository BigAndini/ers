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

class SearchController extends AbstractActionController {
    
    public function indexAction() {
        $q = $this->params()->fromQuery('q');
        
        $form = new Form\Search();
        
        //$form->setData($this->getRequest()->getQuery());
        // commented out so the form is not filled with the search query;
        // leaving the search box empty for the next search is probably a better UX
        
        return new ViewModel(array(
            'form' => $form,
            'query' => $q,
            'results' => [],
        ));
    }
    
}