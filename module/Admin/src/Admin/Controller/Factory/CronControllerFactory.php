<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Admin\Controller\Factory;

#use Zend\Mvc\Controller\AbstractActionController;
#use Application\Controller\AbstractActionController;
#use Zend\View\Model\ViewModel;
#use ErsBase\Service;

use Admin\Controller;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class CronControllerFactory implements FactoryInterface
{    
    /**
      * Create service
      *
      * @param ServiceLocatorInterface $serviceLocator
      *
      * @return mixed
      */
    public function createService(ServiceLocatorInterface $container)
    {
        $realServiceLocator = $container->getServiceLocator();
        #$postService        = $realServiceLocator->get('Blog\Service\PostServiceInterface');

        #error_log('name: '.$name);
        #error_log('requestedName: '.$requestedName);
        
        
        return $this($container, Controller\CronController::class);
        #return new Controller\IndexController($realServiceLocator);
    }
    
    public function __invoke(ContainerInterface $container, $name, array $options = null)
    {
        $parentLocator = $container->getServiceLocator();
        
        error_log('name: '.$name);
        #error_log('requestedName: '.$requestedName);
        
        #$ErrorLogerService = $parentLocator->get('Error_Logger');
        return new CollectionController( $parentLocator );
    }
}
