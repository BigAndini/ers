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
class OptionService
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
    
    public function getCountryOptions($countryId = null) {
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $qb1 = $em->getRepository("ErsBase\Entity\Country")->createQueryBuilder('n');
        $qb1->where($qb1->expr()->isNotNull('n.position'));
        $qb1->orderBy('n.position', 'ASC');
        $result1 = $qb1->getQuery()->getResult();
        
        $qb2 = $em->getRepository("ErsBase\Entity\Country")->createQueryBuilder('n');
        $qb2->where($qb2->expr()->isNull('n.position'));
        $qb2->orderBy('n.name', 'ASC');
        $result2 = $qb2->getQuery()->getResult();

        $countries = array_merge($result1, $result2);

        $orderService = $this->getServiceLocator()
                ->get('ErsBase\Service\OrderService');
        $countryContainerId = $orderService->getCountryId();
        
        $options = array();
        $selected = false;
        if($countryId == null && $countryContainerId == null) {
            $selected = true;
        }
        $options[] = array(
            'value' => 0,
            'label' => 'no Country',
            'selected' => $selected,
        );
        foreach($countries as $country) {
            $selected = false;
            if($countryContainerId == $country->getId()) {
                $selected = true;
            }
            if($countryId == $country->getId()) {
                $selected = true;
            }
            $options[] = array(
                'value' => $country->getId(),
                'label' => $country->getName(),
                'selected' => $selected,
            );
        }
        return $options;
    }
    
    public function getPersonOptions(\ErsBase\Entity\Product $product, $participant_id=null) {
        $cartContainer = new Container('cart');
        $options = array();
        if($participant_id == null) {
            $selected = true;
        } else {
            $selected = false;
        }
        $options[] = array(
            'value' => 0,
            'label' => 'select a person',
            'selected' => $selected,
            'disabled' => true,
        );
        $orderService = $this->getServiceLocator()
                ->get('ErsBase\Service\OrderService');
        $order = $orderService->getOrder();
        #foreach($cartContainer->order->getParticipants() as $v) {
        foreach($order->getParticipants() as $v) {
            $disabled = false;
            if($v->getFirstname() == '') {
                $disabled = true;
            }
            if($v->getSurname() == '') {
                $disabled = true;
            }
            if($v->getBirthday() == null) {
                $disabled = true;
            }
            $selected = false;
            if($v->getId() == $participant_id) {
                $selected = true;
            }
            $options[] = array(
                'value' => $v->getId(),
                'label' => $v->getFirstname().' '.$v->getSurname(),
                'selected' => $selected,
                'disabled' => $disabled,
            );
        }
        $selected = false;
        if($participant_id == 0) {
            $selected = true;
        }
        # there will be no possibility to not assign a ticket/product this year
        /*if(!$product->getPersonalized() && count($options) > 0 ) {
            array_unshift($options, array(
                'value' => 0,
                'label' => 'do not assign this product',
                'selected' => $selected,
                ));
        }*/
        
        return $options;
    }
    
    public function getAgegroupOptions($agegroup_id = null) {
        $em = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $agegroups = $em->getRepository("ErsBase\Entity\Agegroup")
                    ->findBy(array('price_change' => '1'), array('agegroup' => 'DESC'));
        $options = array();
        
        foreach($agegroups as $agegroup) {
            if($agegroup_id == $agegroup->getId()) {
                $selected = true;
            } else {
                $selected = false;
            }
            $options[] = array(
                'value' => $agegroup->getId(),
                'label' => $agegroup->getName(),
                'selected' => $selected,
            );
        }
        if($agegroup_id == null) {
            $selected = false;
        } elseif($agegroup_id == 0) {
            $selected = true;
        } else {
            $selected = false;
        }
        $options[] = array(
                'value' => '0',
                'label' => 'normal',
                'selected' => $selected,
            );
        return $options;
    }
}
