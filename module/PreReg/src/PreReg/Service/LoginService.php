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
 * Login Serivce
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
        $this->setUser($em->getRepository('ErsBase\Entity\User')
                ->findOneBy(array('id' => $user_id)));
    }
    
    public function getUserId() {
        return $this->getUser()->getId();
    }
    
    public function onLogin() {
        $logger = $this->getServiceLocator()
                ->get('Logger');
        
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $this->addParticipantsToOrder();
        $em->flush();
        
        $this->setLoginUserAsBuyer();
        $em->flush();
        
        #
        /*$user = $em->getRepository('ErsBase\Entity\User')
                ->findOneBy(array('id' => $this->getUserId()));*/
        $user = $this->getUser();
        $user->setId($this->getUserId());
        $user->increaseLoginCount();
        error_log('this login count: '.$user->getLoginCount());
        $em->merge($user);
        $em->flush();
        
        $roles = '';
        foreach($user->getRoles() as $role) {
            $roles .= $role->getRoleId().', ';
        }
        
        $logger->info('login for user: '.$user->getEmail().' (login count: '.$user->getLoginCount().', roles: '.$roles.')');
        
        
        /*$request = $this->getServiceLocator()->get('Request');
        $router = $this->getServiceLocator()->get('Router');
        $uri = $router->getRequestUri();
        
        
        error_log('referer: '.$request->getHeader('Referer')->getUri());
        $refererQueryString = parse_url(urldecode($request->getHeader('Referer')->getUri()), PHP_URL_QUERY);
        
        error_log('query string: '.$refererQueryString);
        
        $query = '';
        parse_str($refererQueryString, $query);
        error_log($query['redirect']);
        
        $response = $this->getServiceLocator()->get('Response');
        $response->getHeaders()->addHeaderLine('Location', $query['redirect']);
        $response->setStatusCode(302);
        $response->sendHeaders();*/
    }
    
    public function onLogout() {
        $this->resetShoppingCart();
    }
    
    private function resetShoppingCart() {
        $cartContainer = new Container('ers');
        $cartContainer->init = 0;
    }
    
    private function setLoginUserAsBuyer() {
        if($this->getUser()) {
            $orderService = $this->getServiceLocator()
                    ->get('ErsBase\Service\OrderService');
            $package = $orderService->getOrder()
                    ->getPackageByParticipantEmail($this->getUser()->getEmail());
            if($package && $package->getParticipant()) {
                $orderService->getOrder()
                        ->setBuyer($package->getParticipant());
            }
        }
    }
    
    private function addParticipantsToOrder() {
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        if($this->getUser()) {
            $orderService = $this->getServiceLocator()
                    ->get('ErsBase\Service\OrderService');
            $countries = array();
            
            /*
             * add logged in user
             */
            $newUser = $this->getUser();
            
            if($newUser->getCountryId()) {
                if(isset($countries[$newUser->getCountryId()])) {
                    $country = $countries[$newUser->getCountryId()];
                } else {
                    $country = $em->getRepository('ErsBase\Entity\Country')
                        ->findOneBy(array('id' => $newUser->getCountryId()));
                    $countries[$country->getId()] = $country;
                }
                $newUser->setCountry($country);
            } else {
                $newUser->setCountry(null);
                $newUser->setCountryId(null);
            }

            $package = $orderService->getOrder()
                    ->getPackageByParticipantEmail($newUser->getEmail());
            if($package) {
                $package->setParticipant($newUser);
            } else {
                $orderService->addParticipant($newUser);
            }
                    
            /*
             * add users from former orders
             */
            $orders = $em->getRepository('ErsBase\Entity\Order')
                ->findBy(array('buyer_id' => $this->getUser()->getId()));
        
            $container = new Container('ers');
            $currency = $em->getRepository('ErsBase\Entity\Currency')
                ->findOneBy(array('short' => $container->currency));
            if(!$currency) {
                throw new \Exception('Unable to find currency: '.$container->currency);
            }
            foreach($orders as $order) {
                if(!$order->getCurrency() instanceof Entity\Currency) {
                    $order->setCurrency($currency);
                }
                $count = 1;
                foreach($order->getParticipants() as $user) {
                    $package = $orderService->getOrder()
                            ->getPackageByParticipantEmail($user->getEmail());
                    /*if($user->getEmail() == $this->getUser()->getEmail()) {
                        $newUser = $this->getUser();
                    } else {
                        $newUser = $user;
                        #$newUser = new Entity\User();
                        #$newUser->populate($user->getArrayCopy());
                    }*/
                    if($user->getCountryId()) {
                        if(isset($countries[$user->getCountryId()])) {
                            $country = $countries[$user->getCountryId()];
                        } else {
                            $country = $em->getRepository('ErsBase\Entity\Country')
                                ->findOneBy(array('id' => $user->getCountryId()));
                            $countries[$country->getId()] = $country;
                        }
                        $user->setCountry($country);
                    } else {
                        $user->setCountry(null);
                        $user->setCountryId(null);
                    }
                    
                    if($package) {
                        $package->setParticipant($user);
                    } else {
                        $orderService->addParticipant($user);
                    }
                    $count++;
                }
            }
        } else {
            throw new \Exception('unable to find logged in user');
        }
    }
}
