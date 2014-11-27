<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cart\Model\Entity;

class PaymentType extends Entity {
    /*
     * database fields
     */
    
    protected $id;
    protected $name;
    protected $days2payment;
    
    public function getName() {
        return $this->name;
    }
    public function setName($value) {
        $this->name = $value;
    }
    
    public function getDays2Payment() {
        return $this->days2payment;
    }
    public function setDays2Payment($value) {
        $this->days2payment = $value;
    }
}