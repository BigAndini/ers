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
    protected $currency;
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
    
    public function setCurrency(Entity\Currency $currency) {
        $this->currency = $currency;
    }
    public function getCurrency() {
        if(!$this->currency) {
            $em = $this->getServiceLocator()
                ->get('Doctrine\ORM\EntityManager');
            $container = new Container('ers');
            $this->currency = $em->getRepository('ErsBase\Entity\Currency')
                    ->findOneBy(array('short' => $container->currency));
        }
        return $this->currency;
    }
    
    public function setOrder(Entity\Order $order) {
        $this->order = $order;
    }
    
    public function getOrder() {
        if($this->order instanceof Entity\Order) {
            return $this->order;
        }
        $container = new Container('ers');
        $em = $this->getServiceLocator()
                ->get('Doctrine\ORM\EntityManager');
        $em->flush();
        if(isset($container->order_id) && is_numeric($container->order_id)) {
            $checkOrder = $em->getRepository('ErsBase\Entity\Order')
                    ->findOneBy(array('id' => $container->order_id));
            if($checkOrder) {
                $this->order = $checkOrder;
                $this->addLoggedInUser();
                return $checkOrder;
            }
        }
        
        $newOrder = $this->createNewOrder();

        $container->order_id = $newOrder->getId();

        $this->order = $newOrder;
        $this->addLoggedInUser();

        return $newOrder;
    }
    
    private function createNewOrder() {
        $em = $this->getServiceLocator()
                ->get('Doctrine\ORM\EntityManager');
        #$newOrder = new Entity\Order();
        $newOrder = $this->getServiceLocator()
                ->get('ErsBase\Entity\Order');
        $status = $em->getRepository('ErsBase\Entity\Status')
            ->findOneBy(array('value' => 'order pending'));
        $newOrder->setStatus($status);
        /*$container = new Container('ers');
        $currency = $em->getRepository('ErsBase\Entity\Currency')
            ->findOneBy(array('short' => $container->currency));
        $newOrder->setCurrency($currency);*/

        $em->persist($newOrder);
        $em->flush();
        
        return $newOrder;
    }
    
    public function updateShoppingCart() {
        $em = $this->getServiceLocator()
                ->get('Doctrine\ORM\EntityManager');
        
        $debug = true;
        $order = $this->getOrder();
        $currency = $order->getCurrency();
        foreach($order->getPackages() as $package) {
            foreach($package->getItems() as $item) {
                if($item->hasParentItems()) {
                    continue;
                }
                $product = $item->getProduct();
                $participant = $item->getPackage()->getParticipant();

                $agegroupService = $this->getServiceLocator()
                        ->get('ErsBase\Service\AgegroupService');
                $agegroupService->setMode('price');
                $agegroup = $agegroupService->getAgegroupByUser($participant);
                if($debug) {
                    if($agegroup != null) {
                        error_log('found agegroup: '.$agegroup->getName());
                    }
                }

                $deadlineService = $this->getServiceLocator()
                        ->get('ErsBase\Service\DeadlineService');
                $deadlineService->setMode('price');
                $deadline = $deadlineService->getDeadline($order->getCreated());
                if($debug) {
                    if($deadline != null) {
                        error_log('found deadline: '.$deadline->getName());
                    }
                }

                $price = $product->getProductPrice($agegroup, $deadline, $currency);
                if($debug) {
                    error_log('price: '.$price->getCharge());
                }
                $item->setPrice($price->getCharge());
                $em->persist($item);
            }
        }
        $em->persist($order);
        $em->flush();
    }
    
    public function changeCurrency($paramCurrency) {
        $em = $this->getServiceLocator()
                ->get('Doctrine\ORM\EntityManager');
        if(! $paramCurrency instanceof Entity\Currency) {
            $currency = $em->getRepository('ErsBase\Entity\Currency')
                ->findOneBy(array('short' => $paramCurrency));
        } else {
            $currency = $paramCurrency;
        }
        $debug = false;
        $order = $this->getOrder();
        if($order->getCurrency()->getShort() != $currency->getShort()) {
            foreach($order->getPackages() as $package) {
                $package->setCurrency($currency);
                if($debug) {
                    error_log('set package to currency: '.$currency);
                }
                foreach($package->getItems() as $item) {
                    $item->setCurrency($currency);
                    if($debug) {
                        error_log('set item to currency: '.$currency);
                    }
                    if($item->hasParentItems()) {
                        continue;
                    }
                    $product = $item->getProduct();
                    $participant = $item->getPackage()->getParticipant();

                    $agegroupService = $this->getServiceLocator()
                            ->get('ErsBase\Service\AgegroupService');
                    $agegroupService->setMode('price');
                    $agegroup = $agegroupService->getAgegroupByUser($participant);
                    #$agegroup = $participant->getAgegroup();

                    $deadlineService = $this->getServiceLocator()
                            ->get('ErsBase\Service\DeadlineService');
                    $deadlineService->setMode('price');
                    $deadline = $deadlineService->getDeadline($order->getCreated());

                    $price = $product->getProductPrice($agegroup, $deadline, $currency);
                    if($debug) {
                        error_log('price: '.$price->getCharge());
                    }
                    if(!$price) {
                        throw new \Exception('Unable to find price for '.$product->getName().'.');
                    }
                    $item->setPrice($price->getCharge());
                    #$em->persist($item);
                }
                #$em->persist($package);
            }
            $order->setCurrency($currency);
            if($debug) {
                error_log('set order to currency: '.$currency);
            }
            if($order->getPaymentType()) {
                $order->setPaymentType(null);
            }
            $em->persist($order);
            $em->flush();
        }
        
        return $this;
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
        $container = new Container('ers');
        $container->country_id = $country_id;
    }
    
    public function getCountryId() {
        $container = new Container('ers');
        return $container->country_id;
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
            $container = new Container('ers');
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
            #$item->setPackage(null);
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
    
    public function recalcPackage(Entity\Package $package, $agegroup, $deadline) {
        $itemArray = array();
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $statusOrdered = $em->getRepository('ErsBase\Entity\Status')
                    ->findOneBy(array('value' => 'ordered'));
        $statusPaid = $em->getRepository('ErsBase\Entity\Status')
                    ->findOneBy(array('value' => 'paid'));
        
        foreach($package->getItems() as $item) {
            if($item->getStatus() == 'refund') {
                continue;
            }
            if($item->hasParentItems()) {
                continue;
            }
            $product = $item->getProduct();
            #$price = $product->getProductPrice($agegroup, $deadline);
            $price = $product->getProductPrice($agegroup, $deadline, $package->getCurrency());
            
            if($item->getPrice() != $price->getCharge()) {
                /*
                 * disable item and create new item
                 */
                #$newItem = clone $item;
                $newItem = new Entity\Item();
                $newItem->populate($item->getArrayCopy());
                #$newItem->setStatus($item->getStatus());
                foreach($item->getItemVariants() as $itemVariant) {
                    $newItemVariant = clone $itemVariant;
                    $newItem->addItemVariant($newItemVariant);
                    $newItemVariant->setItem($newItem);
                }
                $newItem->setPrice($price->getCharge());

                $newItem->setProduct($item->getProduct());
                $newItem->setPackage($item->getPackage());
                
                if($newItem->getPrice() == 0) {
                    # set item to paid if it's 0 € worth
                    $newItem->setStatus($statusPaid);
                } elseif($item->getStatus()->getValue() == 'paid') {
                    # if it's not 0 € worth set the item to ordered when it was paid.
                    $newItem->setStatus($statusOrdered);
                } else {
                    # let the item in the old state otherwise
                    $newItem->setStatus($item->getStatus());
                }
                
                $code = new Entity\Code();
                $code->genCode();
                $codecheck = 1;
                while($codecheck != null) {
                    $code->genCode();
                    $codecheck = $em->getRepository('ErsBase\Entity\Code')
                        ->findOneBy(array('value' => $code->getValue()));
                }
                $newItem->setCodeId(null);
                $newItem->setCode($code);

                /*
                 * add subitems to main item
                 */
                foreach($item->getChildItems() as $cItem) {
                    $itemPackage = new Entity\ItemPackage();
                    $itemPackage->setSurItem($newItem);
                    $itemPackage->setSubItem($cItem);
                    $newItem->addItemPackageRelatedBySurItemId($itemPackage);
                }

                $itemArray[$item->getId()]['after'] = $newItem;
            }
            $itemArray[$item->getId()]['before'] = $item;
        }
        return $itemArray;
    }
    
    public function saveRecalcPackage(Entity\Package $package, $agegroup, $deadline) {
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $statusCancelled = $em->getRepository('ErsBase\Entity\Status')
                        ->findOneBy(array('value' => 'cancelled'));

        $itemArray = $this->recalcPackage($package, $agegroup, $deadline);
                
        foreach($itemArray as $items) {
            if(isset($items['after'])) {
                $itemAfter = $items['after'];
                $itemBefore = $items['before'];

                #$itemAfter->setStatus($itemBefore->getStatus());

                $em->persist($itemAfter);

                $order = $itemAfter->getPackage()->getOrder();
                if($order->getPaymentStatus() == 'paid') {
                    $order->setPaymentStatus('unpaid');
                }
                $order->setOrderSum($order->getPrice());
                $order->setTotalSum($order->getSum());
                $em->persist($order);

                $itemBefore->setStatus($statusCancelled);
                $em->persist($itemBefore);

                $em->flush();
            }
        }
    }
}
