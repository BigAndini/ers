<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Model\Entity;

#class User extends \RegistrationSystem\Model\Entity\Entity {
class User extends Entity {
    protected $_name;
    protected $_email;
    protected $_secondaryMail;

    public function __construct(array $options = null) {
        #parent::__construct($options);
    }

    public function getEmail() {
        return $this->_email;
    }
    public function setEmail($email) {
        # TODO: 
        # - check if given email is an email.
        # - optional check if mails can be delivered to this mailbox.
        $this->_email = $email;
    }

}