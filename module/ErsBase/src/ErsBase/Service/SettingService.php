<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ErsBase\Service;

/**
 * setting service
 */
class SettingService
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
    
    public function get($identifier) {
        $em = $this->getServiceLocator()
                ->get('Doctrine\ORM\EntityManager');
        
        $setting = $em->getRepository('ErsBase\Entity\Setting')
                ->findOneBy(['key' => $identifier]);
        
        if($setting) {
            return $setting->getValue();
        }
        return '';
    }
}
