<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace PreReg\Service;

use ersEntity\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Zend\Session\Container;

/**
 * eTicket Serivce
 */
class LoginService
{
    protected $_sl;
    protected $_user;


    public function __construct() {
        $this->_agegroup = null;
        $this->_Items = new ArrayCollection();
        $this->_personalItems = new ArrayCollection();
    }
    
    /**
     * set ServiceLocator
     * 
     * @param ServiceLocator $sl
     */
    public function setServiceLocator($sl) {
        $this->_sl = $sl;
    }
    
    /**
     * get ServiceLocator
     * 
     * @return ServiceLocator
     */
    protected function getServiceLocator() {
        return $this->_sl;
    }
    
    public function setUser(Entity\User $user) {
        $this->_user = $user;
    }
    
    public function getUser() {
        return $this->_user;
    }
    
    public function setUserId($user_id) {
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $this->setUser($em->getRepository("ersEntity\Entity\User")
                ->findOneBy(array('id' => $user_id)));
    }
    
    public function getUserId() {
        return $this->getUser()->getId();
    }
    
    public function onLogin() {
        $this->addParticipantsToOrder();
    }
    
    public function onLogout() {
        $this->resetShoppingCart();
    }
    
    private function resetShoppingCart() {
        $cartContainer = new Container('cart');
        $cartContainer->init = 0;
    }
    
    private function addParticipantsToOrder() {
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        if($this->getUser()) {
            $orders = $em->getRepository("ersEntity\Entity\Order")
                ->findBy(array('Buyer_id' => $this->getUser()->getId()));
        
            $cartContainer = new Container('cart');
            if($cartContainer->order) {
                error_log('order is ok: '.get_class($cartContainer->order));
            }
            if(!method_exists($cartContainer->order, 'getPackageByParticipantEmail')) {
                error_log('unable to find method: getPackageByParticipantEmail in '.  get_class($cartContainer->order));
            }
            
            $countries = array();
            foreach($orders as $order) {
                $count = 1;
                foreach($order->getParticipants() as $user) {
                    error_log('this is run '.$count);
                    error_log('order class: '.  get_class($cartContainer->order));
                    $package = $cartContainer->order->getPackageByParticipantEmail($user->getEmail());
                    $newUser = new Entity\User();
                    $newUser->populate($user->getArrayCopy());
                    error_log('country_id: '.$newUser->getCountryId());
                    if($newUser->getCountryId()) {
                        if(isset($countries[$newUser->getCountryId()])) {
                            $country = $countries[$newUser->getCountryId()];
                        } else {
                            $country = $em->getRepository("ersEntity\Entity\Country")
                                ->findOneBy(array('id' => $newUser->getCountryId()));
                            $countries[$country->getId()] = $country;
                            error_log('found country: '.$country->getName());
                        }
                        $newUser->setCountry($country);
                    } else {
                        $newUser->setCountry(null);
                        $newUser->setCountryId(null);
                    }
                    
                    
                    if($package) {
                        $package->setParticipant($newUser);
                        error_log('changed user in package: '.$newUser->getFirstname().' '.$newUser->getSurname());
                    } else {
                        $cartContainer->order->addParticipant($newUser);
                        error_log('add Participant. '.$newUser->getFirstname().' '.$newUser->getSurname());
                    }
                    $count++;
                }
            }
        } else {
            error_log('unable to find login user');
        }
        
       
    }
}
