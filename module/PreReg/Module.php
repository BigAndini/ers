<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace PreReg;

use ErsBase\Entity;
use Zend\Mvc\ModuleRouteListener;
use Zend\Session\SessionManager;
use Zend\Session\Container;
use Zend\Mvc\MvcEvent;
use BjyAuthorize\View\RedirectionStrategy;

class Module
{
    public function onBootstrap($event)
    {
        $eventManager        = $event->getApplication()->getEventManager();

        #$strategy = new RedirectionStrategy();

        // eventually set the URI to be used for redirects
        #$baseUrl = $event->getRequest()->getUriString();
        #$strategy->setRedirectUri('/user/login?redirect='.\rawurlencode($baseUrl));
        #$eventManager->attach($strategy);
        
        $eventManager->getSharedManager()->attach('Zend\Mvc\Controller\AbstractActionController', 'dispatch', function($event) {
            $controller      = $event->getTarget();
            $controllerClass = get_class($controller);
            $moduleNamespace = substr($controllerClass, 0, strpos($controllerClass, '\\'));
            $config          = $event->getApplication()->getServiceManager()->get('config');
            if (isset($config['module_layouts'][$moduleNamespace])) {
                $controller->layout($config['module_layouts'][$moduleNamespace]);
            }
        }, 100);
        
        # bootstrap session
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
        $this->bootstrapSession($event);
        
        $translator = $event->getApplication()->getServiceManager()->get('translator');
        $translator->setLocale('en_US');
        setlocale(LC_TIME, 'en_US');
        #$translator->setLocale('de_DE');
        #setlocale(LC_TIME, 'de_DE');
        
        $application   = $event->getApplication();
        $serviceManager = $application->getServiceManager();
        $auth = $serviceManager->get('BjyAuthorize\Service\Authorize');

        if(!\Zend\Console\Console::isConsole()) {
            $acl  = $auth->getAcl();
            $role = $auth->getIdentity();
            \Zend\View\Helper\Navigation::setDefaultAcl($acl);
            \Zend\View\Helper\Navigation::setDefaultRole($role);
        }
        
        $sharedManager = $application->getEventManager()->getSharedManager();
        $sharedManager->attach('Zend\Mvc\Application', 'dispatch.error',
                function($event) use ($serviceManager) {
                    if ($event->getParam('exception')){
                        $emailService = $serviceManager->get('ErsBase\Service\EmailService');
                        $emailService->sendExceptionEmail($event->getParam('exception'));
                    }
                }
            );

        $zfcAuthEvents = $serviceManager->get('ZfcUser\Authentication\Adapter\AdapterChain')->getEventManager();

        $zfcAuthEvents->attach( 'authenticate.success', function( $authEvent ) use( $serviceManager ){
            $loginService =  $serviceManager->get( 'PreReg\Service\LoginService' );
            $user_id = $authEvent->getIdentity();
            $loginService->setUserId($user_id);
            $loginService->onLogin();
            return true;
        });
        
        $zfcAuthEvents->attach( 'logout', function( $authEvent ) use( $serviceManager ){
            $loginService =  $serviceManager->get( 'PreReg\Service\LoginService' );
            $loginService->onLogout();
            return true;
        });
    }
    
    public function bootstrapSession($event)
    {
        if(\Zend\Console\Console::isConsole()) {
            #echo "not starting session -> console".PHP_EOL;
            return;
        }
        
        $sessionManager = $event->getApplication()
                    ->getServiceManager()
                    ->get('Zend\Session\SessionManager');
        $sessionManager->start();
        
        $container = new Container('ers');
	# removed to run through changeCurrency feature
        /*if (isset($container->init)) {
            return;
        }*/
        
        $serviceManager = $event->getApplication()->getServiceManager();
        $request        = $serviceManager->get('Request');

        $sessionManager->regenerateId(true);
        $container->init          = 1;
        $container->remoteAddr    = $request->getServer()->get('REMOTE_ADDR');
        # not needed since we do not check this.
        #$container->httpUserAgent = $request->getServer()->get('HTTP_USER_AGENT');

        $config = $serviceManager->get('Config');
        if (!isset($config['session'])) {
            return;
        }

        $sessionConfig = $config['session'];
        
        if (! isset($sessionConfig['validators'])) {
            return;
        }

        $chain   = $sessionManager->getValidatorChain();

        foreach ($sessionConfig['validators'] as $validator) {
            switch ($validator) {
                case Validator\HttpUserAgent::class:
                    $validator = new $validator($container->httpUserAgent);
                    break;
                case Validator\RemoteAddr::class:
                    $validator  = new $validator($container->remoteAddr);
                    break;
                default:
                    $validator = new $validator();
            }

            $chain->attach('session.validate', array($validator, 'isValid'));
        }
        
        $expiration_time = 3600;
        $container->setExpirationSeconds( $expiration_time, 'initialized' );
        if(!$sessionManager->isValid()) {
            error_log('Session is not valid');
            $container->init = 0;
        }
        #$container->getManager()->getStorage()->clear('initialized');
        if (!isset($container->init) || $container->lifetime < time()) {
            $container->getManager()->getStorage()->clear('ers');
            $container = new Container('ers');
            $container->init = 1;
            $container->checkout = array();
            
            $container->getManager()->getStorage()->clear('cart');
        }
        $container->lifetime = time()+$expiration_time;
        
        if(!isset($container->currency)) {
            # TODO: put this into a CurrencyService
            /*$entityManager = $this->getServiceLocator()
                ->get('Doctrine\ORM\EntityManager');

            $currency = $entityManager->getRepository('ErsBase\Entity\Currency')
                ->findOneBy(array('position' => '1'));*/
            
            $container->currency = 'EUR';
        }
        
        $orderService = $serviceManager->get('ErsBase\Service\OrderService');
        $order = $orderService->getOrder();
        if($order->getCurrency()->getShort() != $container->currency) {
            $orderService->changeCurrency($container->currency);
        }
        
        /*
         * shopping cart debugging
         */
        /*error_log('=== Order Info ===');
        error_log('paymenttype_id: '.$cartContainer->order->getPaymentTypeId());
        error_log('buyer_id: '.$cartContainer->order->getBuyerId());
        $buyer = $cartContainer->order->getBuyer();
        if($buyer) {
            error_log('buyer email: '.$cartContainer->order->getBuyer()->getEmail());
        }
        foreach($cartContainer->order->getPackages() as $package) {
            error_log('  --- Package Info ---');
            error_log('  participant_id: '.$package->getParticipantId());
            $items = $package->getItems();
            foreach($items as $item) {
                error_log(' - '.$item->getName().' (product_id: '.$item->getProductId().')');
            }
            error_log('  --------------------');
        }
        error_log('==================');*/
    }
    
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
    
    public function getServiceConfig() {
        return array(
            'factories' => array(
                'Logger' => function($serviceManager){
                    $logger = new \Zend\Log\Logger;
                    if(!is_dir(getcwd().'/data/logs')) {
                        mkdir(getcwd().'/data/logs');
                    }
                    $writer = new \Zend\Log\Writer\Stream('./data/logs/'.date('Y-m-d').'-zend.log');
                    $logger->addWriter($writer);
                    
                    #EMERG   = 0;  // Emergency: system is unusable
                    #ALERT   = 1;  // Alert: action must be taken immediately
                    #CRIT    = 2;  // Critical: critical conditions
                    #ERR     = 3;  // Error: error conditions
                    #WARN    = 4;  // Warning: warning conditions
                    #NOTICE  = 5;  // Notice: normal but significant condition
                    #INFO    = 6;  // Informational: informational messages
                    #DEBUG   = 7;  // Debug: debug messages

                    #$filter = new \Zend\Log\Filter\Priority(\Zend\Log\Logger::CRIT);
                    #$writer->addFilter($filter);

                    return $logger;
                },
                SessionManager::class => function ($container) {
                    $config = $container->get('config');
                    if (! isset($config['session'])) {
                        $sessionManager = new SessionManager();
                        Container::setDefaultManager($sessionManager);
                        return $sessionManager;
                    }

                    $session = $config['session'];

                    $sessionConfig = null;
                    if (isset($session['config'])) {
                        $class = isset($session['config']['class'])
                            ?  $session['config']['class']
                            : SessionConfig::class;

                        $options = isset($session['config']['options'])
                            ?  $session['config']['options']
                            : [];

                        $sessionConfig = new $class();
                        $sessionConfig->setOptions($options);
                    }

                    $sessionStorage = null;
                    if (isset($session['storage'])) {
                        $class = $session['storage'];
                        $sessionStorage = new $class();
                    }

                    $sessionSaveHandler = null;
                    if (isset($session['save_handler'])) {
                        // class should be fetched from service manager
                        // since it will require constructor arguments
                        $sessionSaveHandler = $container->get($session['save_handler']);
                    }

                    $sessionManager = new SessionManager(
                        $sessionConfig,
                        $sessionStorage,
                        $sessionSaveHandler
                    );

                    Container::setDefaultManager($sessionManager);
                    return $sessionManager;
                },
                'PreReg\Form\CurrencyChooser' => function ($serviceManager) {
                    $form = new Form\CurrencyChooser();
                    
                    $optionService = $serviceManager->get('ErsBase\Service\OptionService');
                    $form->get('currency')->setValueOptions($optionService->getCurrencyOptions());
                    
                    return $form;
                },
                'PreReg\Form\ProductView' => function ($serviceManager) {
                    $productView = new Form\ProductView();
                    $productView->setServiceLocator($serviceManager);
                    return $productView;
                },
                'PreReg\Form\CreditCard' => function ($serviceManager) {
                    $form = new Form\CreditCard();
                    
                    $years = array();
                    for($i=date('Y'); $i<=(date('Y')+15); $i++) {
                        $years[] = array(
                            'value' => $i,
                            'label' => $i,
                        );
                    }
                    $form->get('cc_expdate_year')->setAttribute('options', $years);

                    $months = array();
                    for($i=1; $i<=12; $i++) {
                        $months[] = array(
                            'value' => $i,
                            'label' => sprintf('%02d', $i),
                        );
                    }
                    $form->get('cc_expdate_month')->setAttribute('options', $months);
                    
                    return $form;
                },
                'PreReg\Service\LoginService' => function($serviceManager) {
                    $loginService = new Service\LoginService();
                    $loginService->setServiceLocator($serviceManager);
                    return $loginService;
                },
                'PreReg\InputFilter\Register' => function($serviceManager) {
                    $inputFilter = new InputFilter\Register();
                    $inputFilter->setServiceLocator($serviceManager);
                    return $inputFilter;
                },
                'PreReg\InputFilter\Participant' => function($serviceManager) {
                    $inputFilter = new InputFilter\Participant();
                    $inputFilter->setServiceLocator($serviceManager);
                    return $inputFilter;
                },
                'PreReg\Form\Participant' => function($serviceManager) {
                    $form = new Form\Participant();
                    $form->setServiceLocator($serviceManager);
                    return $form;
                },
                'PreReg\InputFilter\PaymentType' => function($serviceManager) {
                    $inputFilter = new InputFilter\PaymentType();
                    $inputFilter->setServiceLocator($serviceManager);
                    return $inputFilter;
                },
            ),
        );
    }
}
