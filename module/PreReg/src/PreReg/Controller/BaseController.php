<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace PreReg\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\ServiceManager\ServiceLocatorInterface;

class BaseController extends AbstractActionController {
    
    protected $sm;
    
    public function __construct(ServiceLocatorInterface $sm) {
        if($sm != null) {
            $this->setServiceLocator($sm);
        }
    }
    
    public function setServiceLocator(ServiceLocatorInterface $sm) {
        $this->sm = $sm;
    }
            
    public function getServiceLocator() {
        return $this->sm;
    }
}