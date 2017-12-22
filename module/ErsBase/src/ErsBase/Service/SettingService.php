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
    
    public function get($key, $type = null, $param = []) {
        $entityManager = $this->getServiceLocator()
                ->get('Doctrine\ORM\EntityManager');
        $setting = $entityManager->getRepository('ErsBase\Entity\Setting')
                ->findOneBy(['key' => $key]);
        if(!$setting) {
            return '';
            
        }
        
        switch($type) {
            case 'hyperlink':
                if($param['class']) {
                    $class = ' class="'.$param['class'].'"';
                }
                if($param['target']) {
                    $target = ' target="'.$param['target'].'"';
                }
                return '<a '.$class.'.href="'.$setting->getValue().'"'.$target.'>'.$setting->getValue().'</a>';
                break;
            case 'email':
                return '<a href="mailto:'.$setting->getValue().'">'.$setting->getValue().'</a>';
                break;
            case 'date':
                if(empty($param['fromFormat'])) {
                    $param['fromFormat'] = 'd.m.Y';
                }
                $date = date_create_from_format($param['fromFormat'], $setting->getValue());
                if(!$date) {
                    throw new \Exception('Unable to create date with format '.$param['fromFormat'].' from: '.$setting->getValue());
                }
                if(empty($param['toFormat'])) {
                    $param['toFormat'] = "%a %d.%m.%Y";
                }
                return strftime($param['toFormat'], $date->getTimestamp());
                break;
            case 'datetime':
                if(empty($param['fromFormat'])) {
                    $param['fromFormat'] = 'd.m.Y H:i:s';
                }
                $date = date_create_from_format($param['fromFormat'], $setting->getValue());
                if(!$date) {
                    throw new \Exception('Unable to create datetime with format '.$param['fromFormat'].' from: '.$setting->getValue());
                }
                if(empty($param['toFormat'])) {
                    $param['toFormat'] = "%d.%m.%Y %H:%M:%S";
                }
                return strftime($param['toFormat'], $date->getTimestamp());
                break;
            default:
                return $setting->getValue();
        }
        
    }
    
}
