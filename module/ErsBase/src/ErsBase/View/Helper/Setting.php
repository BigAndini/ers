<?php

/* 
 * Copyright (C) 2015 andi
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace ErsBase\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\View\HelperPluginManager as ServiceManager;

class Setting extends AbstractHelper {

    protected $serviceManager;

    public function setServiceManager($serviceManager) {
        $this->serviceManager = $serviceManager;
        
        return $this;
    }
    public function getServiceManager() {
        return $this->serviceManager;
    }
    
    public function __construct(ServiceManager $serviceManager) {
        $this->setServiceManager($serviceManager);
    }

    public function __invoke($key, $type = null, $param = array()) {
        $entityManager = $this->getServiceManager()->getServiceLocator()
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
                if(!$param['fromFormat']) {
                    $param['fromFormat'] = 'd.m.Y';
                }
                $date = date_create_from_format($param['fromFormat'], $setting->getValue());
                if(!$date) {
                    throw new \Exception('Unable to create date with format '.$param['fromFormat'].' from: '.$setting->getValue());
                }
                if(!$param['toFormat']) {
                    $param['toFormat'] = "%a %d.%m.%Y";
                }
                return strftime($param['toFormat'], $date->getTimestamp());
                break;
            case 'datetime':
                if(!$param['fromFormat']) {
                    $param['fromFormat'] = 'd.m.Y H:i:s';
                }
                $date = date_create_from_format($param['fromFormat'], $setting->getValue());
                if(!$date) {
                    throw new \Exception('Unable to create datetime with format '.$param['fromFormat'].' from: '.$setting->getValue());
                }
                if(!$param['toFormat']) {
                    $param['toFormat'] = "%d.%m.%Y %H:%M:%S";
                }
                return strftime($param['toFormat'], $date->getTimestamp());
                break;
            default:
                return $setting->getValue();
        }
        
    }

}