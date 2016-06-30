<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ErsBase\Service;

use Zend\Session\Container;
use ErsBase\Entity;

/**
 * order service
 */
class OrderService
{
    protected $_sl;
    protected $order;

    public function __construct() {
        
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
    
    public function setOrder(Entity\Order $order) {
        $this->order = $order;
    }
    
    public function getOrder() {
        if($this->order instanceof Entity\Order) {
            return $this->order;
        }
        $cartContainer = new Container('cart');
        $em = $this->getServiceLocator()
                ->get('Doctrine\ORM\EntityManager');
        if(isset($cartContainer->order_id) && is_numeric($cartContainer->order_id)) {
            $order = $em->getRepository('ErsBase\Entity\Order')
                    ->findOneBy(array('id' => $cartContainer->order_id));
            if(!$order) {
                # Cannot find order with given id: Creating new order...
                $order = new Entity\Order();
                $status = $em->getRepository('ErsBase\Entity\Status')
                    ->findOneBy(array('value' => 'order pending'));
                $order->setStatus($status);
            
                $em->persist($order);
                $em->flush();

                $cartContainer->order_id = $order->getId();
            }
            
            $this->order = $order;
            $this->addLoggedInUser();
            return $order;
        } else {
            $order = new Entity\Order();
            $status = $em->getRepository('ErsBase\Entity\Status')
                ->findOneBy(array('value' => 'order pending'));
            $order->setStatus($status);
            
            $em->persist($order);
            $em->flush();
            
            $cartContainer->order_id = $order->getId();
            
            $this->order = $order;
            $this->addLoggedInUser();
            
            return $order;
        }
    }
    
    public function addLoggedInUser() {
        $auth = $this->getServiceLocator()
                ->get('zfcuser_auth_service');
        if ($auth->hasIdentity()) {
            $login_email = $auth->getIdentity()->getEmail();

            $order = $this->getOrder();
            $package = $order->getPackageByParticipantEmail($login_email);
            if(!$package) {
                $em = $this->getServiceLocator()
                    ->get('Doctrine\ORM\EntityManager');
                $user = $em->getRepository('ErsBase\Entity\User')
                        ->findOneBy(array('email' => $login_email));
                $this->addParticipant($user);
            }
        }
        
    }
    
    public function setCountryId($country_id) {
        $cartContainer = new Container('cart');
        $cartContainer->country_id = $country_id;
    }
    
    public function getCountryId() {
        $cartContainer = new Container('cart');
        return $cartContainer->country_id;
    }
    
    /**
     * Add Participant (add new Package and set participant)
     * 
     * @param \Entity\User $participant
     * @return \Entity\Order
     */
    
    public function addParticipant(Entity\User $participant) {
        $package = new Entity\Package();
        $em = $this->getServiceLocator()
                ->get('Doctrine\ORM\EntityManager');
        $status = $em->getRepository('ErsBase\Entity\Status')
                    ->findOneBy(array('value' => 'order pending'));
        if(!$status) {
            throw new \Exception('Please setup status "order pending"');
        }
        $package->setStatus($status);
        $status->addPackage($package);
        
        $package->setParticipant($participant);
        
        $this->getOrder()->addPackage($package);
        $package->setOrder($this->getOrder());
        
        return $this->getOrder();
    }
    
    public function removeParticipant(Entity\User $participant, $flush=true) {
        if(!$participant) {
            throw new \Exception('Unable to find participant with id: '.$id);
        }
        
        # remove package of the active order
        $package = $this->getOrder()->getPackageByParticipantId($participant->getId());
        if(!$package) {
            throw new \Exception('Unable to find package for participant id: '.$participant->getId());
        }
        
        $this->removePackage($package, false);
        
        $em = $this->getServiceLocator()
                ->get('Doctrine\ORM\EntityManager');
        #$em->remove($package);
        if(!$participant->getActive()) {
            foreach($participant->getPackages() as $oldPackage) {
                $this->removePackage($oldPackage, false);
                #$em->remove($oldPackage);
            }
            $em->remove($participant);
        }
        if($participant->getId() == $this->getOrder()->getBuyerId()) {
            $this->getOrder()->setBuyer(null);
            $this->getOrder()->setBuyerId(null);
            $container = new Container('initialized');
            if(!is_array($container->checkout)) {
                $container->checkout = array();
            }
            $container->checkout['/order/buyer'] = 0;
        }
        
        if($flush) {
            $em->flush();
        }
    }
    
    public function removePackage(Entity\Package $package, $flush=true) {
        $em = $this->getServiceLocator()
                ->get('Doctrine\ORM\EntityManager');
        
        foreach($package->getItems() as $item) {
            error_log('removing item '.$item->getName().' ('.$item->getId().')');
            $item->setPackage(null);
            $package->removeItem($item);
            $em->remove($item);
        }
        $package->setParticipant(null);
        $em->remove($package);
        if($flush) {
            $em->flush();
        }
    }
    
    public function removeItemById($item_id) {
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $item = $em->getRepository('ErsBase\Entity\Item')
            ->findOneBy(array('id' => $item_id));
        if(!$item) {
            throw new \Exception('Unable to remove item with id: '.$item_id);
        }
        foreach($item->getChildItems() as $cItem) {
            error_log('remove child item '.$cItem->getName());
            $em->remove($cItem);
        }
        error_log('remove item '.$item->getName());
        $em->remove($item);
        $em->flush();
    }
}
