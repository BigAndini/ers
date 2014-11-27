<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace User\Model\Entity;

#class User extends \RegistrationSystem\Model\Entity\Entity {
class User extends Entity {
    /*
     * database fields
     */
    protected $id;
    protected $prename;
    protected $surname;
    protected $email;
    /* 
     * hashed password of the user
     */
    protected $password;
    protected $active;
    /* 
     * birthday is a mysql formatted date string:
     * 2014-11-22
     */
    protected $birthday;
    
    /*
     * additional storage
     */
    protected $roles;
    

    public function __construct(array $options = null) {
        #parent::__construct($options);
    }

    public function getEmail() {
        return $this->email;
    }
    public function setEmail($email) {
        # TODO: 
        # - check if given email is an email.
        # - optional check if mails can be delivered to this mailbox.
        $this->email = $email;
    }
    
    public function getPrename() {
        return $this->prename;
    }
    public function setPrename($value) {
        $this->prename = $value;
    }
    
    public function getSurname() {
        return $this->surname;
    }
    public function setSurname($value) {
        $this->surname = $value;
    }
    
    /* birthday is returned as Date object */
    public function getBirthday() {
        return new \Date($this->birthday);
    }
    public function setBirthday($value) {
        if(is_object($value)) {
            if(get_class($value) == 'Date') {
                $this->birthday = $value->format('YYYY-MM-DD');
            }
        } elseif(is_string($value)) {
            $date = new \Date($value);
            if($date) {
                $this->birthday = $value;
            }
        } else {
            error_log('USER: unable to setBirthday with value: '.$value);
        }
    }
    
    /*
     * TODO: Add here a more secure password hashing.
     */
    public function setPassword($value) {
        $this->password = md5($value);
    }
    
    /*
     * check if the users password is correct.
     * 
     * TODO: we need to change this function when the setPassword function is getting more secure.
     */
    public function authenticate($password) {
        if($this->password == md5($password)) {
            return true;
        } else {
            return false;
        }
    }
    
    public function setRoles(array $Roles) {
        foreach($Roles as $r) {
            if(get_class($r) == 'Entity\Role') {
                $this->addRole($r);
            }
        }
    }
    public function getRoles() {
        return $this->roles;
    }
    public function addRole(Entity\Role $Role) {
        $this->roles[] = $Role;
    }
    public function getRole($name) {
        foreach($this->roles as $r) {
            if(\strtolower($r->getName()) === \strtolower($name)) {
                return $r;
            }
        }
    }
}