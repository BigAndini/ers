<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace OnsiteTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class PackageControllerTest extends AbstractHttpControllerTestCase
{
    protected $traceError = true;
    
    public function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__.'/../../../../../config/application.config.php'
        );
        parent::setUp();
    }
    
    public function testPackageActionCanBeAccessed()
    {
        $this->dispatch('/onsite/package/detail/396');
        $this->assertResponseStatusCode(200);

        $this->assertModuleName('Onsite');
        $this->assertControllerName('Onsite\Controller\Package');
        $this->assertControllerClass('PackageController');
        $this->assertMatchedRouteName('package');
    }
    
}