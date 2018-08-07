<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace PreRegTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class ProductControllerTest extends AbstractHttpControllerTestCase
{
    protected $traceError = true;
    
    public function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__.'/../../../../../config/application.config.php'
        );
        parent::setUp();
    }
    
    public function testProductActionCanBeAccessed()
    {
        $this->dispatch('/product');
        $this->assertResponseStatusCode(200);

        $this->assertModuleName('PreReg');
        $this->assertControllerName('PreReg\Controller\Product');
        $this->assertControllerClass('ProductController');
        $this->assertMatchedRouteName('product');
    }
    
}