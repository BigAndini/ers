<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace PreRegTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class InfoControllerTest extends AbstractHttpControllerTestCase
{
    protected $traceError = true;
    
    public function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__.'/../../../../../config/application.config.php'
        );
        parent::setUp();
    }
    
    public function testIndexActionCanBeAccessed()
    {
        $this->dispatch('/');
        $this->assertResponseStatusCode(200);

        $this->assertModuleName('PreReg');
        $this->assertControllerName('PreReg\Controller\Info');
        $this->assertControllerClass('InfoController');
        $this->assertMatchedRouteName('home');
    }
    
    public function testFormsActionCanBeAccessed()
    {
        $this->dispatch('/info/forms');
        $this->assertResponseStatusCode(200);

        $this->assertModuleName('PreReg');
        $this->assertControllerName('PreReg\Controller\Info');
        $this->assertControllerClass('InfoController');
        $this->assertMatchedRouteName('info');
    }
    
    public function testTermsActionCanBeAccessed()
    {
        $this->dispatch('/info/terms');
        $this->assertResponseStatusCode(200);

        $this->assertModuleName('PreReg');
        $this->assertControllerName('PreReg\Controller\Info');
        $this->assertControllerClass('InfoController');
        $this->assertMatchedRouteName('info');
    }
    
    public function testImpressumActionCanBeAccessed()
    {
        $this->dispatch('/info/impressum');
        $this->assertResponseStatusCode(200);

        $this->assertModuleName('PreReg');
        $this->assertControllerName('PreReg\Controller\Info');
        $this->assertControllerClass('InfoController');
        $this->assertMatchedRouteName('info');
    }
    
    public function testHelpActionCanBeAccessed()
    {
        $this->dispatch('/info/help');
        $this->assertResponseStatusCode(200);

        $this->assertModuleName('PreReg');
        $this->assertControllerName('PreReg\Controller\Info');
        $this->assertControllerClass('InfoController');
        $this->assertMatchedRouteName('info');
    }
}