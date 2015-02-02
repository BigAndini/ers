<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Model\Entity;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ServiceLocatorAwareEntity implements ServiceLocatorAwareInterface {
    protected $sm;
    
    protected $id;
    protected $updated;
    protected $created;
    
    protected $exclude_vars;
    protected $inputFilter;
    

    public function __construct(array $options = null) {
        if($options != null) {
            $this->exchangeArray($options);
        }
        $this->exclude_vars[] = 'exclude_vars';
        $this->exclude_vars[] = 'dbAdapter';
        $this->exclude_vars[] = 'inputFilter';
        $this->exclude_vars[] = '_serviceLocator';
    }
    
    public function getServiceLocator() {
        return $this->sm;
    }

    public function setServiceLocator(\Zend\ServiceManager\ServiceLocatorInterface $sm) {
        #error_log('got the servicelocator!');
        $this->sm = $sm;    
    }
    
    public function exchangeArray($data)
    {
        if(is_object($data)) {
            $this->id = (!empty($data->id)) ? $data->id : null;
            $this->updated = (!empty($data->updated)) ? $data->updated : null;
            $this->created = (!empty($data->created)) ? $data->created : null;
        } elseif(is_array($data)) {
            $this->id     = (!empty($data['id'])) ? $data['id'] : null;
            $this->updated  = (!empty($data['updated'])) ? $data['updated'] : null;
            $this->created  = (!empty($data['created'])) ? $data['created'] : null;
        } else {
            error_log('exchangeArray: given data is either an object nor an array!');
        }
    }
    
    public function getArrayCopy()
    {
        return get_object_vars($this);
    }
    
    public function __get($name) {
        $func = 'get'.ucfirst(strtolower($name));
        if(method_exists($this, $func)) {
            return $this->$func();
        } else {
            return $this->$name;
        }
    }
    public function __set($name, $value) {
        $func = 'set'.ucfirst(strtolower($name));
        if(method_exists($this, $func)) {
            $this->$func($value);
        } else {
            $this->$name = $value;
        }
        return $this;
    }
    
    public function getEntityVars() {
        $vars = get_object_vars($this);

        $ret = array();
        foreach($vars as $k => $v) {
            if(!\in_array($k, $this->exclude_vars)) {
                $ret[] = $k;
            }
        }
        error_log(var_export($ret,true));
        return $ret;
    }
}