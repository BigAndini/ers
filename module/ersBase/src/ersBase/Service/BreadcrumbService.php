<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ersBase\Service;

use Zend\Session\Container;

/**
 * breadcrumb factory
 * 
 * TODO: implement per tab forrest
 */
class BreadcrumbService
{
    protected $forrest;

    public function __construct() {
        $this->forrest = new Container('forrest');
        if(empty($this->forrest->active)) {
            $this->forrest->active = 'fallback';
        }
        $id = $this->getId();
        if(!$this->forrest->$id instanceof \ArrayObject) {
            $this->forrest->$id = new \ArrayObject();
        }
    }
    
    public function set($context, $route, $params=array(), $options=array()) {
        $value = new \ArrayObject();
        $value->route   = $route;
        $value->params  = $params;
        $value->options = $options;
        
        $id = $this->getId();
        $this->forrest->$id->offsetSet($context, $value);
        
        return $this->get($context);
    }
    
    public function get($context) {
        $id = $this->getId();
        return $this->forrest->$id->offsetGet($context);
    }
    
    public function remove($context) {
        if($this->exists($context)) {
            $this->forrest->trace->offsetUnset($context);
        }
    }
    
    public function exists($context) {
        $id = $this->getId();
        if($this->forrest->$id->offsetExists($context)) {
            return true;
        }
        return false;
    }
    
    public function reset() {
        $clearance = new Container('forrest');
        $clearance->getManager()->getStorage()->clear('forrest');
        $this->forrest = new Container('forrest');
        $this->forrest->active = 'fallback';
        $id = $this->getId();
        $this->forrest->$id = new \ArrayObject();
    }
    
    public function activate($id) {
        $this->setId($id);
    }
    
    public function setId($id) {
        $this->forrest->active = $id;
        if(empty($this->forrest->$id) || !$this->forrest->$id instanceof \ArrayObject) {
            $this->forrest->$id = new \ArrayObject();
        }
    }
    public function getId() {
        return $this->forrest->active;
    }
}
