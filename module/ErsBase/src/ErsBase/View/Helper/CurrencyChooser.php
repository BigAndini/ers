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
#use Zend\View\HelperPluginManager as ServiceManager;
use Zend\Session\Container;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class CurrencyChooser extends AbstractHelper implements ServiceLocatorAwareInterface{

    protected $sm;

    public function __construct() {
        #$this->sm = $sm;
    }
    
    public function setServiceLocator(ServiceLocatorInterface $sm) {
        $this->sm = $sm;
    }
    public function getServiceLocator() {
        return $this->sm;
    }

    public function __invoke() {
        if(!$this->getServiceLocator()) {
            throw new \Exception('ServiceLocator is needed for Currency Chooser');
        }
        # two times getServiceLocator because the first one is just the PluginManager, 
        # the second one is the ApplicationManager
        return $this->getServiceLocator()->getServiceLocator()
                ->get('PreReg\Form\CurrencyChooser');
    }

}