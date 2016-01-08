<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace PreReg\Service;

use ErsBase\Entity;
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
        $this->setUser($em->getRepository("ErsBase\Entity\User")
                ->findOneBy(array('id' => $user_id)));
    }
    
    public function getUserId() {
        return $this->getUser()->getId();
    }
    
    public function onLogin() {
        $this->addParticipantsToOrder();
        $this->setLoginUserAsBuyer();
    }
    
    public function onLogout() {
        $this->resetShoppingCart();
    }
    
    private function resetShoppingCart() {
        $cartContainer = new Container('cart');
        $cartContainer->init = 0;
    }
    
    private function setLoginUserAsBuyer() {
        if($this->getUser()) {
            $cartContainer = new Container('cart');
            $package = $cartContainer->order->getPackageByParticipantEmail($this->getUser()->getEmail());
            if($package && $package->getParticipant()) {
                $cartContainer->order->setBuyer($package->getParticipant());
            }
        } else {
            error_log('unable to find login user');
        }
    }
    
    private function addParticipantsToOrder() {
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        if($this->getUser()) {
            $cartContainer = new Container('cart');
            $countries = array();
            
            /*
             * add logged in user
             */
            $login_user = $this->getUser();
            $newUser = new Entity\User();
            $newUser->populate($login_user->getArrayCopy());
            
            if($newUser->getCountryId()) {
                if(isset($countries[$newUser->getCountryId()])) {
                    $country = $countries[$newUser->getCountryId()];
                } else {
                    $country = $em->getRepository("ErsBase\Entity\Country")
                        ->findOneBy(array('id' => $newUser->getCountryId()));
                    $countries[$country->getId()] = $country;
                }
                $newUser->setCountry($country);
            } else {
                $newUser->setCountry(null);
                $newUser->setCountryId(null);
            }

            $package = $cartContainer->order->getPackageByParticipantEmail($login_user->getEmail());
            if($package) {
                $package->setParticipant($newUser);
                error_log('changed logged in user in package: '.$newUser->getFirstname().' '.$newUser->getSurname());
            } else {
                $cartContainer->order->addParticipant($newUser);
                error_log('added logged in user to my persons: '.$newUser->getFirstname().' '.$newUser->getSurname());
            }
                    
            /*
             * add users from former orders
             */
            $orders = $em->getRepository("ErsBase\Entity\Order")
                ->findBy(array('buyer_id' => $this->getUser()->getId()));
        
            foreach($orders as $order) {
                $count = 1;
                foreach($order->getParticipants() as $user) {
                    $package = $cartContainer->order->getPackageByParticipantEmail($user->getEmail());
                    $newUser = new Entity\User();
                    $newUser->populate($user->getArrayCopy());
                    if($newUser->getCountryId()) {
                        if(isset($countries[$newUser->getCountryId()])) {
                            $country = $countries[$newUser->getCountryId()];
                        } else {
                            $country = $em->getRepository("ErsBase\Entity\Country")
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
                        $newUser->setSessionId($package->getParticipant()->getSessionId());
                        $package->setParticipant($newUser);
                    } else {
                        $cartContainer->order->addParticipant($newUser);
                    }
                    $count++;
                }
            }
        } else {
            throw new \Exception('unable to find logged in user');
        }
    }
}
