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
class StatusService
{
    protected $_sl;

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
    
    public function setOrderStatus(Entity\Order $order, $status, $flush=true) {
        $entityManager = $this->getServiceLocator()
                ->get('Doctrine\ORM\EntityManager');
        if(is_string($status)) {
            $status = $entityManager->getRepository('ErsBase\Entity\Status')
                    ->findOneBy(array('value' => $status));
        }
        if(!$status instanceof Entity\Status) {
            throw new \Exception('Error getting correct status');
        }
        
        # ignore if package is actually in one of the following states
        $ignore = array('cancelled', 'transferred', 'shipped');
        
        foreach($order->getPackages() as $package) {
            if(!in_array($package->getStatus()->getValue(), $ignore)) {
                $this->setPackageStatus($package, $status, false);
            }
            $package = null;
        }
        $order->setStatus($status);
        $entityManager->persist($order);
        
        if($flush) {
            $entityManager->flush();
        }
        $order = null;
        $status = null;
    }
    
    public function setPackageStatus(Entity\Package $package, $status, $flush=true) {
        $entityManager = $this->getServiceLocator()
                ->get('Doctrine\ORM\EntityManager');
        if(is_string($status)) {
            $status = $entityManager->getRepository('ErsBase\Entity\Status')
                    ->findOneBy(array('value' => $status));
        }
        if(!$status instanceof Entity\Status) {
            throw new \Exception('Error getting correct status');
        }
        
        # ignore if item is actually in one of the following states
        $ignore = array('cancelled', 'transferred', 'shipped');
        
        foreach($package->getItems() as $item) {
            if(!in_array($item->getStatus()->getValue(), $ignore)) {
                if($item->getStatus()->getValue() != $status->getValue()) {
                    echo "set item (".$item->getId().") status from ".$item->getStauts()->getValue()." to ".$status->getValue().PHP_EOL;
                }
                $this->setItemStatus($item, $status, false);
            }
            $item = null;
        }
        $package->setStatus($status);
        $entityManager->persist($package);
        
        if($flush) {
            $entityManager->flush();
        }
        $package = null;
    }
    
    public function setItemStatus(Entity\Item $item, $status, $flush=true) {
        $entityManager = $this->getServiceLocator()
                ->get('Doctrine\ORM\EntityManager');
        if(is_string($status)) {
            $status = $entityManager->getRepository('ErsBase\Entity\Status')
                    ->findOneBy(array('value' => $status));
        }
        if(!$status instanceof Entity\Status) {
            throw new \Exception('Error getting correct status');
        }
        
        $item->setStatus($status);
        $entityManager->persist($item);
        
        if($flush) {
            $entityManager->flush();
        }
        $item = null;
    }
}
