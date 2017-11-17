<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace PreReg\Controller\Factory;

#use Zend\Mvc\Controller\AbstractActionController;
#use Application\Controller\AbstractActionController;
#use Zend\View\Model\ViewModel;
#use ErsBase\Service;

use PreReg\Controller;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ControllerFactory implements FactoryInterface
{    
    /**
      * Create service
      *
      * @param ServiceLocatorInterface $serviceLocator
      *
      * @return mixed
      */
    public function createService(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        $realServiceLocator = $serviceLocator->getServiceLocator();
        #$postService        = $realServiceLocator->get('Blog\Service\PostServiceInterface');

        error_log('name: '.$name);
        error_log('requestedName: '.$requestedName);
        
        return new Controller\IndexController($realServiceLocator);
    }
    
    public function __invoke($container, $name, $requestedName)
    {
        $realServiceLocator = $serviceLocator->getServiceLocator();
        
        error_log('name: '.$name);
        error_log('requestedName: '.$requestedName);
        
        return new Controller\IndexController($realServiceLocator);
    }
}
