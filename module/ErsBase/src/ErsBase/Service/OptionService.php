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
}
