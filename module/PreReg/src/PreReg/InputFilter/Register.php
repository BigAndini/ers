<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace PreReg\InputFilter;

use Zend\InputFilter\Factory as InputFactory; 
use Zend\InputFilter\InputFilter; 
use Zend\InputFilter\InputFilterAwareInterface; 
use Zend\InputFilter\InputFilterInterface; 
use Zend\Session\Container;

class Register implements InputFilterAwareInterface 
{ 
    protected $inputFilter; 
    protected $em;
    protected $loginEmail;
    protected $email;
    
    public function setEntityManager(\Doctrine\ORM\EntityManager $em) {
        $this->em = $em;
    }
    public function getEntityManager() {
        return $this->em;
    }
    
    public function setLoginEmail($loginEmail) {
        $this->loginEmail = $loginEmail;
    }
    public function getLoginEmail() {
        return $this->loginEmail;
    }
    
    public function setEmail($email) {
        $this->email = $email;
    }
    public function getEmail() {
        return $this->email;
    }
    
    public function setInputFilter(InputFilterInterface $inputFilter) 
    { 
        throw new \Exception("Not used"); 
    } 
    
    public function getInputFilter()
    {
        if (!$this->inputFilter) 
        { 
            $inputFilter = new InputFilter(); 
            $factory = new InputFactory();             
            
            $inputFilter->add($factory->createInput([ 
                'name' => 'buyer_id', 
                'required' => true, 
                'filters' => array( 
                    array('name' => 'Int'), 
                ),
                'validators' => array(
                    array(
                        'name' => 'Callback',
                        'options' => array(
                            'messages' => array(
                                \Zend\Validator\Callback::INVALID_VALUE => 'Please select a buyer.',
                            ),
                            'callback' => function($value, $context=array()) {
                                /*
                                 * If the buyer_id is not 0 the user adds an 
                                 * already existing participant as buyer.
                                 */
                                if($context['buyer_id'] != 0) {
                                    return true;
                                }
                                
                                return false;
                            },
                            
                        ),
                    ),
                    array(
                        'name' => 'Callback',
                        'options' => array(
                            'messages' => array(
                                \Zend\Validator\Callback::INVALID_VALUE => 'The email of this buyer already exists. Please login with this account to continue.',
                            ),
                            'callback' => function($value, $context=array()) {
                                $cartContainer = new Container('cart');
                                $participant = $cartContainer->order->getParticipantBySessionId($context['buyer_id']);
                                
                                $this->setEmail($participant->getEmail());
                                
                                if($this->getLoginEmail() == $participant->getEmail()) {
                                    return true;
                                }
                                
                                $em = $this->getEntityManager();
                                
                                $user = $em->getRepository("ersEntity\Entity\User")->findOneBy(array('email' => $participant->getEmail()));
                                if($user == null) {
                                    return true;
                                }
                                return false;
                            },
                            
                        ),
                    ),
                ),
            ]));
            
            $this->inputFilter = $inputFilter; 
        } 
        
        return $this->inputFilter; 
    } 
} 