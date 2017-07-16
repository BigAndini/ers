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
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        /*
         * check if tax exist
         */
        $taxError = false;
        $taxes = $entityManager->getRepository('ErsBase\Entity\Tax')
                ->findAll();
        
        if(count($taxes) <= 0) {
            $taxError = true;
        }
        
        /*
         * check if deadline exists
         */
        $deadlineError = false;
        $deadlines = $entityManager->getRepository('ErsBase\Entity\Deadline')
                ->findAll();
        
        if(count($deadlines) <= 0) {
            $deadlineError = true;
        }
        
        /*
         * check if agegroup exists
         */
        $agegroupError = false;
        $agegroups = $entityManager->getRepository('ErsBase\Entity\Agegroup')
                ->findAll();
        
        if(count($agegroups) <= 0) {
            $agegroupError = true;
        }
        
        return new ViewModel(array(
            'order_search_form' => new Form\SearchOrder(),
            'showTaxError' => $taxError,
            'showDeadlineError' => $deadlineError,
            'showAgegroupError' => $agegroupError,
        ));
    }
}