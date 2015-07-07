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


namespace ZendTest\PreReg;

use PreReg;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

#class ReachabilityTest extends \PHPUnit_Framework_TestCase
class ReachabilityTest extends AbstractHttpControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(
            include '../../config/application.config.php'
        );
        parent::setUp();
        #Cache\PatternFactory::resetPluginManager();
    }
    /*public function tearDown()
    {
        Cache\PatternFactory::resetPluginManager();
    }*/
    public function testIndexActionCanBeAccessed()
    {
        $this->dispatch('/');
        $this->assertResponseStatusCode(200);

        $this->assertModuleName('PreReg');
        $this->assertControllerName('PreReg\Controller\Index');
        $this->assertControllerClass('PreRegController');
        $this->assertMatchedRouteName('home');
    }
    
}