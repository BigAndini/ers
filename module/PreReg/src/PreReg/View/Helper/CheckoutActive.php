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

namespace PreReg\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\View\HelperPluginManager as ServiceManager;
use Zend\Session\Container;

class CheckoutActive extends AbstractHelper {

    #protected $serviceManager;
    protected $sm;

    public function __construct() {
        #$this->serviceManager = $serviceManager;
        #$this->setServiceLocator($sm);
    }
    
    public function setServiceLocator($sm) {
        $this->sm = $sm;
    }
    public function getServiceLocator() {
        return $this->sm;
    }

    public function __invoke($name) {
        $container = new Container('initialized');
        /*foreach($container->checkout as $key => $item) {
            error_log($key.': '.$item);
        }*/
        if(isset($container->checkout[$name]) && $container->checkout[$name] == 1) {
            return true;
        } else {
            return false;
        }
    }

}