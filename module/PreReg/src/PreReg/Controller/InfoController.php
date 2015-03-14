<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace PreReg\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class InfoController extends AbstractActionController {
    public function indexAction() {
        $logger = $this
            ->getServiceLocator()
            ->get('Logger');
        $logger->log(\Zend\Log\Logger::INFO, 'test log');
        $logger->emerg('Emergency message');
        $logger->info('Info message');
        $logger->warn('Warn message');
        return new ViewModel();
    }
    public function termsAction() {
        return new ViewModel();
    }
}