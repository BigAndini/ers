<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace User\Model\Entity;

#class User extends \RegistrationSystem\Model\Entity\Entity {
class Role extends Entity {
    protected $name;

    public function __construct(array $options = null) {
        #parent::__construct($options);
    }

    public function getName() {
        return $this->name;
    }
    public function setName($value) {
        $this->name = $value;
    }
}