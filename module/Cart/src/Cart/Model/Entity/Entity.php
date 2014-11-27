<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cart\Model\Entity;

abstract class Entity {
    protected $id;
    protected $updated;
    protected $created;
    
    public function __construct(array $options = null) {
        if($options != null) {
            $this->exchangeArray($options);
        }
    }
    
    public function exchangeArray($data)
    {
        if(is_object($data)) {
            #$this->id = (!empty($data->id)) ? $data->id : null;
            $this->updated = (!empty($data->updated)) ? $data->updated : null;
            $this->created = (!empty($data->created)) ? $data->created : null;
        } elseif(is_array($data)) {
            #$this->id     = (!empty($data['id'])) ? $data['id'] : null;
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
            switch($k) {
                case '':
                case 'inputFilter':
                    break;
                default:
                    $ret[] = $k;
            }
        }
        return $ret;
    }
}