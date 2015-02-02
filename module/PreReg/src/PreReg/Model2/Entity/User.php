<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace PreReg\Model\Entity;

#class User extends Entity {
class User {
    protected $id;
    protected $prename;
    protected $surname;
    protected $email;
    protected $birthday;

    public function __construct(array $options = null) {
        #parent::__construct($options);
    }

    public function __sleep() {
        return array(
            'id',
            'prename',
            'surname',
            'email',
            'birthday',
            /*'updated',
            'created',*/
        );
    }
    public function exchangeArray($data) {
        
        if(is_object($data)) {
            $this->id = (!empty($data->id)) ? $data->id : null;
            $this->prename = (!empty($data->prename)) ? $data->prename : null;
            $this->surname = (!empty($data->surname)) ? $data->surname : null;
            $this->email = (!empty($data->email)) ? $data->email : null;
            $this->birthday = (!empty($data->birthday)) ? $data->birthday : null;
        } elseif(is_array($data)) {
            $this->id = (!empty($data['id'])) ? $data['id'] : null;
            $this->prename = (!empty($data['prename'])) ? $data['prename'] : null;
            $this->surname = (!empty($data['surname'])) ? $data['surname'] : null;
            $this->email = (!empty($data['email'])) ? $data['email'] : null;
            $this->birthday = (!empty($data['birthday'])) ? $data['birthday'] : null;
            
        } else {
            error_log('exchangeArray: given data is either an object nor an array!');
        }
    }
    
    public function getId() {
        return $this->id;
    }
    public function setId($id) {
        $this->id = $id;
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
    public function setPrename($surname) {
        $this->prename = $prename;
    }
    
    public function getSurname() {
        return $this->surname;
    }
    public function setSurname($surname) {
        $this->surname = $surname;
    }
    
    public function getBirthday() {
        return $this->birthday;
    }
    public function setBirthday($birthday) {
        $this->birthday = $birthday;
    }
    
   
}