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
        $entityManager = $this->getServiceLocator()
                ->get('Doctrine\ORM\EntityManager');
        
        $newOrder = clone $order;
        
        return $newOrder;
    }
    
    public function clonePackage(Entity\Package $package) {
        $entityManager = $this->getServiceLocator()
                ->get('Doctrine\ORM\EntityManager');
        
        $newPackage = clone $package;
        
        $newPackage->setOrder($package->getOrder());
        $newPackage->setStatus($package->getStatus());
        
        foreach($package->getItems() as $item) {
            if($item->hasParentItems()) {
                continue;
            }
            $newItem = $this->cloneItem($item);
            $newItem->setStatus($item->getStatus());
            $newPackage->addItem($newItem);
        }
        $entityManager->persist($newPackage);
        
        if($this->getTransfer()) {
            $package->setTransferredPackage($newPackage);
            $entityManager->persist($package);
        }
        
        return $newPackage;
    }
    
    public function cloneItem(Entity\Item $item) {
        $entityManager = $this->getServiceLocator()
                ->get('Doctrine\ORM\EntityManager');
        
        $newItem = clone $item;
        $newItem->setStatus($item->getStatus());
        
        foreach($item->getItemPackageRelatedBySurItemIds() as $itemPackage) {
            $newItemPackage = $this->cloneItemPackage($itemPackage);
            $newItemPackage->setSurItem($newItem);
            $newItem->addItemPackageRelatedBySurItemId($newItemPackage);
            $entityManager->persist($newItemPackage);
        }
        $entityManager->persist($newItem);
        
        if($this->getTransfer()) {
            #$item->setTransferredItemId($newItem->getId());
            $item->setTransferredItem($newItem);
            $status = $entityManager->getRepository("ErsBase\Entity\Status")
                ->findOneBy(array('value' => 'transferred'));
            $item->setStatus($status);
            $entityManager->persist($item);
        }
        
        return $newItem;
    }
    
    public function cloneItemPackage(Entity\ItemPackage $itemPackage) {
        $entityManager = $this->getServiceLocator()
                ->get('Doctrine\ORM\EntityManager');
        
        $newItemPackage = clone $itemPackage;
        
        #$newSurItem = $this->cloneItem($itemPackage->getSurItem());
        #$newItemPackage->setSubItem($newSurItem);
        
        $newSubItem = $this->cloneItem($itemPackage->getSubItem());
        $newItemPackage->setSubItem($newSubItem);
        
        #$entityManager->persist($newItemPackage);
        
        return $newItemPackage;
    }
    
    public function transferPackage(Entity\Package $package) {
        $entityManager = $this->getServiceLocator()
                ->get('Doctrine\ORM\EntityManager');
        
        $newPackage = clone $package;
        $newPackage->setOrder($package->getOrder());
        foreach($package->getItems() as $item) {
            if($item->hasParentItems()) {
                continue;
            }
            $newItem = clone $item;
            $item->setTransferredItem($newItem);
            $statusTransferred = $entityManager->getRepository('ErsBase\Entity\Status')
                    ->findOneBy(array('value' => 'transferred'));
            $item->setStatus($statusTransferred);
            $item->setPackage($package);
            $entityManager->persist($item);
            $newPackage->addItem($newItem);
            $newItem->setPackage($newPackage);
            $entityManager->persist($newItem);
        }
        $package->setTransferredPackage($newPackage);
        $entityManager->persist($package);
        
        return $newPackage;
    }
}