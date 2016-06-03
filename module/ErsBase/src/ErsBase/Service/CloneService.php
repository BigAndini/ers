<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ErsBase\Service;

use Doctrine\Common\Persistence\ObjectRepository;
use DoctrineModule\Validator\ObjectExists;
use ErsBase\Entity;

class CloneService
{
    protected $sm;
    protected $transfer;

    public function __construct() {
        $this->transfer = false;
    }
    
    public function setServiceLocator($sm) {
        $this->sm = $sm;
    }
    public function getServiceLocator() {
        return $this->sm;
    }
    
    public function setTransfer($transfer) {
        $this->transfer = $transfer;
    }
    public function getTransfer() {
        return $this->transfer;
    }
    
    public function cloneOrder(Entity\Order $order) {
        $em = $this->getServiceLocator()
                ->get('Doctrine\ORM\EntityManager');
        
        $newOrder = clone $order;
        
        return $newOrder;
    }
    
    public function clonePackage(Entity\Package $package) {
        $em = $this->getServiceLocator()
                ->get('Doctrine\ORM\EntityManager');
        
        $newPackage = clone $package;
        
        $newPackage->setOrder($package->getOrder());
        
        foreach($package->getItems() as $item) {
            if($item->hasParentItems()) {
                continue;
            }
            $newItem = $this->cloneItem($item);
            $newPackage->addItem($newItem);
        }
        $em->persist($newPackage);
        
        if($this->getTransfer()) {
            $package->setTransferredPackage($newPackage);
            $em->persist($package);
        }
        
        return $newPackage;
    }
    
    public function cloneItem(Entity\Item $item) {
        $em = $this->getServiceLocator()
                ->get('Doctrine\ORM\EntityManager');
        
        $newItem = clone $item;
        
        foreach($item->getItemPackageRelatedBySurItemIds() as $itemPackage) {
            $newItemPackage = $this->cloneItemPackage($itemPackage);
            $newItemPackage->setSurItem($newItem);
            $newItem->addItemPackageRelatedBySurItemId($newItemPackage);
            $em->persist($newItemPackage);
        }
        $em->persist($newItem);
        
        if($this->getTransfer()) {
            #$item->setTransferredItemId($newItem->getId());
            $item->setTransferredItem($newItem);
            $status = $em->getRepository("ErsBase\Entity\Status")
                ->findOneBy(array('value' => 'transferred'));
            $item->setStatus($status);
            $em->persist($item);
        }
        
        return $newItem;
    }
    
    public function cloneItemPackage(Entity\ItemPackage $itemPackage) {
        $em = $this->getServiceLocator()
                ->get('Doctrine\ORM\EntityManager');
        
        $newItemPackage = clone $itemPackage;
        
        #$newSurItem = $this->cloneItem($itemPackage->getSurItem());
        #$newItemPackage->setSubItem($newSurItem);
        
        $newSubItem = $this->cloneItem($itemPackage->getSubItem());
        $newItemPackage->setSubItem($newSubItem);
        
        #$em->persist($newItemPackage);
        
        return $newItemPackage;
    }
    
    public function transferPackage(Entity\Package $package) {
        $em = $this->getServiceLocator()
                ->get('Doctrine\ORM\EntityManager');
        
        $newPackage = clone $package;
        $newPackage->setOrder($package->getOrder());
        foreach($package->getItems() as $item) {
            if($item->hasParentItems()) {
                continue;
            }
            $newItem = clone $item;
            $item->setTransferredItem($newItem);
            $statusTransferred = $em->getRepository('ErsBase\Entity\Status')
                    ->findOneBy(array('value' => 'transferred'));
            $item->setStatus($statusTransferred);
            $item->setPackage($package);
            $em->persist($item);
            $newPackage->addItem($newItem);
            $newItem->setPackage($newPackage);
            $em->persist($newItem);
        }
        $package->setTransferredPackage($newPackage);
        $em->persist($package);
        
        return $newPackage;
    }
}