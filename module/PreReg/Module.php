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
    public function onBootstrap($e)
    {
        $eventManager        = $e->getApplication()->getEventManager();

        #$strategy = new RedirectionStrategy();

        // eventually set the URI to be used for redirects
        #$baseUrl = $e->getRequest()->getUriString();
        #$strategy->setRedirectUri('/user/login?redirect='.\rawurlencode($baseUrl));
        #$eventManager->attach($strategy);
        
        $eventManager->getSharedManager()->attach('Zend\Mvc\Controller\AbstractActionController', 'dispatch', function($e) {
            $controller      = $e->getTarget();
            $controllerClass = get_class($controller);
            $moduleNamespace = substr($controllerClass, 0, strpos($controllerClass, '\\'));
            $config          = $e->getApplication()->getServiceManager()->get('config');
            if (isset($config['module_layouts'][$moduleNamespace])) {
                $controller->layout($config['module_layouts'][$moduleNamespace]);
            }
        }, 100);
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
        $this->bootstrapSession($e);
        
        $translator = $e->getApplication()->getServiceManager()->get('translator');
        $translator->setLocale('en_US');
        setlocale(LC_TIME, 'en_US');
        
        $application   = $e->getApplication();
        $sm = $application->getServiceManager();
        $auth = $sm->get('BjyAuthorize\Service\Authorize');

        if(!\Zend\Console\Console::isConsole()) {
            $acl  = $auth->getAcl();
            $role = $auth->getIdentity();
            \Zend\View\Helper\Navigation::setDefaultAcl($acl);
            \Zend\View\Helper\Navigation::setDefaultRole($role);
        }
        
        $sharedManager = $application->getEventManager()->getSharedManager();
        $sharedManager->attach('Zend\Mvc\Application', 'dispatch.error',
                function($e) use ($sm) {
                    if ($e->getParam('exception')){
                        $emailService = $sm->get('ErsBase\Service\EmailService');
                        $emailService->sendExceptionEmail($e->getParam('exception'));
                    }
                }
            );

        $zfcAuthEvents = $sm->get('ZfcUser\Authentication\Adapter\AdapterChain')->getEventManager();

        $zfcAuthEvents->attach( 'authenticate.success', function( $authEvent ) use( $sm ){
            $loginService =  $sm->get( 'PreReg\Service\LoginService' );
            $user_id = $authEvent->getIdentity();
            $loginService->setUserId($user_id);
            $loginService->onLogin();
            return true;
        });
        
        $zfcAuthEvents->attach( 'logout', function( $authEvent ) use( $sm ){
            $loginService =  $sm->get( 'PreReg\Service\LoginService' );
            $loginService->onLogout();
            return true;
        });
    }
    
    public function bootstrapSession($e)
    {
        if(\Zend\Console\Console::isConsole()) {
            echo "not starting session -> console".PHP_EOL;
            return;
        }
        
        $sessionManager = $e->getApplication()
                    ->getServiceManager()
                    ->get('Zend\Session\SessionManager');
        $sessionManager->start();
        
        #error_log(var_export($_SESSION, true));
        
        $container = new Container('ers');
        #error_log('session: '.$container->init);
        if (!isset($container->init)) {
            #error_log('initializing session ('.$container->init.')');
            $serviceManager = $e->getApplication()->getServiceManager();
            $request        = $serviceManager->get('Request');

            $sessionManager->regenerateId(true);
            $container->init          = 1;
            $container->remoteAddr    = $request->getServer()->get('REMOTE_ADDR');
            $container->httpUserAgent = $request->getServer()->get('HTTP_USER_AGENT');
            
            /*$config = $serviceManager->get('Config');
            if (!isset($config['session'])) {
                return;
            }*/

            /*$sessionConfig = $config['session'];
            if (isset($sessionConfig['validators'])) {
                $chain   = $sessionManager->getValidatorChain();

                foreach ($sessionConfig['validators'] as $validator) {
                    switch ($validator) {
                        case 'Zend\Session\Validator\HttpUserAgent':
                            $validator = new $validator($container->httpUserAgent);
                            break;
                        case 'Zend\Session\Validator\RemoteAddr':
                            $validator  = new $validator($container->remoteAddr);
                            break;
                        default:
                            $validator = new $validator();
                    }

                    $chain->attach('session.validate', array($validator, 'isValid'));
                }
            }*/
        }
        
        $expiration_time = 3600;
        $container->setExpirationSeconds( $expiration_time, 'initialized' );
        if(!$sessionManager->isValid()) {
            error_log('Session is not valid');
            $container->init = 0;
        }
        #$container->getManager()->getStorage()->clear('initialized');
        if (!isset($container->init) || $container->lifetime < time()) {
            #error_log('reset session due to expiration');
            $container->getManager()->getStorage()->clear('ers');
            $container = new Container('ers');
            $container->init = 1;
            $container->lifetime = time()+$expiration_time;
            $container->checkout = array();
            
            $container->getManager()->getStorage()->clear('cart');
        } else {
            $container->lifetime = time()+$expiration_time;
        }
        
        if(!isset($container->currency)) {
            # TODO: put this into a CurrencyService
            /*$em = $this->getServiceLocator()
                ->get('Doctrine\ORM\EntityManager');

            $currency = $em->getRepository('ErsBase\Entity\Currency')
                ->findOneBy(array('position' => '1'));*/
            
            $container->currency = 'EUR';
        }
        $serviceManager = $e->getApplication()->getServiceManager();
        $orderService = $serviceManager->get('ErsBase\Service\OrderService');
        $order = $orderService->getOrder();
        if($order->getCurrency()->getShort() != $container->currency) {
            #error_log('currencies are not the same!');
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
                error_log(' - '.$item->getName().' (Product_id: '.$item->getProductId().')');
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
                'Logger' => function($sm){
                    $logger = new \Zend\Log\Logger;
                    if(!is_dir(getcwd().'/data/log')) {
                        mkdir(getcwd().'/data/log');
                    }
                    $writer = new \Zend\Log\Writer\Stream('./data/log/'.date('Y-m-d').'-zend-error.log');
                    $logger->addWriter($writer);

                    return $logger;
                },
                'Zend\Session\SessionManager' => function ($sm) {
                    $config = $sm->get('config');
                    if (isset($config['session'])) {
                        $session = $config['session'];

                        $sessionConfig = null;
                        if (isset($session['config'])) {
                            $class = isset($session['config']['class'])  ? $session['config']['class'] : 'Zend\Session\Config\SessionConfig';
                            $options = isset($session['config']['options']) ? $session['config']['options'] : array();
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
                            // class should be fetched from service manager since it will require constructor arguments
                            $sessionSaveHandler = $sm->get($session['save_handler']);
                        }

                        $sessionManager = new SessionManager($sessionConfig, $sessionStorage, $sessionSaveHandler);

                        if (isset($session['validators'])) {
                            $chain = $sessionManager->getValidatorChain();
                            foreach ($session['validators'] as $validator) {
                                $validator = new $validator();
                                $chain->attach('session.validate', array($validator, 'isValid'));
                            }
                        }
                    } else {
                        $sessionManager = new SessionManager();
                    }
                    Container::setDefaultManager($sessionManager);
                    return $sessionManager;
                },
                'PreReg\Form\CurrencyChooser' => function ($sm) {
                    $form = new Form\CurrencyChooser();
                    
                    $optionService = $sm->get('ErsBase\Service\OptionService');
                    $form->get('currency')->setValueOptions($optionService->getCurrencyOptions());
                    
                    return $form;
                },
                'PreReg\Form\ProductView' => function ($sm) {
                    $productView = new Form\ProductView();
                    $productView->setServiceLocator($sm);
                    return $productView;
                },
                'PreReg\Form\CreditCard' => function ($sm) {
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
                'PreReg\Service\LoginService' => function($sm) {
                    $loginService = new Service\LoginService();
                    $loginService->setServiceLocator($sm);
                    return $loginService;
                },
                'PreReg\Service\PayPalService' => function($sm) {
                    $payPalService = new Service\PayPalService();
                    $payPalService->setServiceLocator($sm);
                    return $payPalService;
                },
                'PreReg\InputFilter\Register' => function($sm) {
                    $inputFilter = new InputFilter\Register();
                    $inputFilter->setServiceLocator($sm);
                    return $inputFilter;
                },
                'PreReg\InputFilter\Participant' => function($sm) {
                    $inputFilter = new InputFilter\Participant();
                    $inputFilter->setServiceLocator($sm);
                    return $inputFilter;
                },
                'PreReg\Form\Participant' => function($sm) {
                    $form = new Form\Participant();
                    $form->setServiceLocator($sm);
                    return $form;
                },
                'PreReg\InputFilter\PaymentType' => function($sm) {
                    $inputFilter = new InputFilter\PaymentType();
                    $inputFilter->setServiceLocator($sm);
                    return $inputFilter;
                },
            ),
        );
    }
}