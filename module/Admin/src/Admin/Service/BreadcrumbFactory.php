<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Service;

use Zend\Session\Container;

/**
 * breadcrumb factory DEPRECATED!!!
 * use ersBase\Service\BreadcrumbService instead.
 */
class BreadcrumbFactory
{
    protected $forrest;

    public function __construct() {
        $this->forrest = new Container('forrest');
        if(!$this->forrest->trace instanceof \ArrayObject) {
            $this->forrest->trace = new \ArrayObject();
        }
    }
    
    public function set($context, $route, $params=array(), $options=array()) {
        $value = new \ArrayObject();
        $value->route   = $route;
        $value->params  = $params;
        $value->options = $options;
        $this->forrest->trace->offsetSet($context, $value);
        
        return $this->get($context);
    }
    
    public function get($context) {
        return $this->forrest->trace->offsetGet($context);
    }
    
    public function exists($context) {
        if($this->forrest->trace->offsetExists($context)) {
            return true;
        }
        return false;
    }
    
    public function reset() {
        $clearance = new Container('forrest');
        $clearance->getManager()->getStorage()->clear('forrest');
        $this->forrest = new Container('forrest');
        $this->forrest->trace = new \ArrayObject();
    }
}
