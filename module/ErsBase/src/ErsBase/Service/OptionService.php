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
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $queryBuilder1 = $entityManager->getRepository('ErsBase\Entity\Country')->createQueryBuilder('n');
        $queryBuilder1->where($queryBuilder1->expr()->isNotNull('n.position'));
        $queryBuilder1->orderBy('n.position', 'ASC');
        $result1 = $queryBuilder1->getQuery()->getResult();
        
        $queryBuilder2 = $entityManager->getRepository('ErsBase\Entity\Country')->createQueryBuilder('n');
        $queryBuilder2->where($queryBuilder2->expr()->isNull('n.position'));
        $queryBuilder2->orderBy('n.name', 'ASC');
        $result2 = $queryBuilder2->getQuery()->getResult();

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
    
    public function getCurrencyOptions() {
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        
        $currencies = $entityManager->getRepository('ErsBase\Entity\Currency')
                ->findBy(array('active' => 1), array('position' => 'ASC'));
        
        $container = new Container('ers');
        
        $options = array();
        foreach($currencies as $currency) {
            $selected = false;
            if($container->currency == $currency->getShort()) {
                $selected = true;
            }
            $options[] = array(
                'value' => $currency->getId(),
                'label' => $currency->getName(),
                'selected' => $selected,
            );
        }
        return $options;
    }
    
    public function getPersonOptions(\ErsBase\Entity\Product $product, $participant_id=null) {
        $cartContainer = new Container('ers');
        $options = array();
        if($participant_id == null) {
            $selected = true;
        } else {
            $selected = false;
        }
        $options[] = array(
            'value' => 0,
            'label' => _('select a person'),
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
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $agegroups = $entityManager->getRepository('ErsBase\Entity\Agegroup')
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
    
    public function getDeadlineOptions() {
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $deadlines = $entityManager->getRepository('ErsBase\Entity\Deadline')
                ->findBy(array(), array('deadline' => 'ASC'));

        $options = array();
        foreach ($deadlines as $deadline) {
            $options[] = array(
                'value' => $deadline->getId(),
                'label' => 'Deadline: ' . $deadline->getDeadline()->format('Y-m-d H:i:s')
            );
        }
        $options[] = array(
            'value' => 0,
            'label' => 'after last deadline'
        );

        return $options;
    }
    
    /*public function getCurrencyOptions() {
        $entityManager = $this->getServiceLocator()
            ->get('Doctrine\ORM\EntityManager');
        $currencies = $entityManager->getRepository('ErsBase\Entity\Currency')
                ->findBy(array(), array('position' => 'ASC'));
        
        $options = array();
        foreach ($currencies as $currency) {
            $options[] = array(
                'value' => $currency->getId(),
                'label' => $currency->getName().' ('.$currency->getSymbol().' / '.$currency->getShort().')',
            );
        }
        $options[] = array(
            'value' => 0,
            'label' => 'Choose currency...',
            'disabled' => true,
        );

        return $options;
    }*/
}
