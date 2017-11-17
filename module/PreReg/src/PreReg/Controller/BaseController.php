<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace PreReg\Controller;

use Zend\Mvc\Controller\AbstractActionController;

class BaseController extends AbstractActionController {
    
    protected $sm;
    
    public function __construct($sm = null) {
        if($sm != null) {
            $this->setServiceLocator($sm);
        }
    }
    
    public function setServiceLocator($sm) {
        $this->sm = $sm;
    }
            
    public function getServiceLocator() {
        return $this->sm;
    }
}