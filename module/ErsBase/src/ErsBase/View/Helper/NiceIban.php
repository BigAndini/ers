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

class NiceIban extends AbstractHelper {

    protected $serviceManager;

    public function __construct(ServiceManager $serviceManager) {
        $this->serviceManager = $serviceManager;
    }

    public function __invoke($source_iban) {
        $iban = \preg_replace('/\ /', '', $source_iban);
        $nice_iban = '<span class="iban-part">';
        for($i=0; $i < strlen($iban); $i++) {
            if($i%4 == 0 && $i != 0) {
                $nice_iban .= '</span><span class="iban-part">';
            }
            $nice_iban .= $iban[$i];
        }
        $nice_iban .= '</span>';
        return $nice_iban; 
    }

}