<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Order\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
#use StickyNotes\Model\User;
use Order\Form;

class PackageController extends AbstractActionController {
 
    /*
     * overview of this package
     */
    public function indexAction() {
        return new ViewModel();
    }
    
    /*
     * add an Item to this package
     */
    public function addAction() {
        
    }
    
    /*
     * add a Participant to this package
     */
    public function addParticipantAction() {
        
    }
    
    /*
     * edit an Item of this package
     */
    public function editAction() {
        
    }
    
    /*
     * delete an Item of this package
     */
    public function deleteAction() {
        
    }
}