<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

return [
    'service_manager' => [
        'aliases' => [
            'translator' => 'MvcTranslator',
            #'Logger'     => 'EddieJaoude\Zf2Logger',
        ],
        'shared' => [
            'DOMPDF' => false,
            'ViewPdfRenderer' => false,
            'ErsBase\Service\ETicketService' => false,
            'ErsBase\Service\EmailService' => false,
            'ErsBase\Service\AgegroupService:ticket' => false,
        ],
        'factories' => [
            'Zend\Session\SessionManager' => 'Zend\Session\Service\SessionManagerFactory',
            'Zend\Session\Config\ConfigInterface' => 'Zend\Session\Service\SessionConfigFactory',
        ],
    ],
    'session_config' => [
        'remember_me_seconds' => 2419200,
        'use_cookies' => true,
        'cookie_httponly' => true,
    ],
    
    'translator' => [
        'locale' => 'en_US',
        'translation_file_patterns' => [
            [
                'type'     => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.mo',
            ],
        ],
    ],
    
    'doctrine' => [
        'driver' => [
            'ers_entities' => [
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => [__DIR__ . '/../src/ErsBase/Entity'],
            ],

            'orm_default' => [
                'drivers' => [
                    'ErsBase\Entity' => 'ers_entities'
                ],
            ],
        ],
    ],
    
    'zfcuser' => [
        // telling ZfcUser to use our own class
        'user_entity_class'       => 'ErsBase\Entity\User',
        // telling ZfcUserDoctrineORM to skip the entities it defines
        'enable_default_entities' => false,
    ],
 
    'bjyauthorize' => [
        // Using the authentication identity provider, which basically reads the roles from the auth service's identity
        'identity_provider' => 'BjyAuthorize\Provider\Identity\AuthenticationIdentityProvider',
 
        'role_providers'        => [
            // using an object repository (entity repository) to load all roles into our ACL
            'BjyAuthorize\Provider\Role\ObjectRepositoryProvider' => [
                #'object_manager'    => 'doctrine.entity_manager.orm_default',
                'object_manager'    => 'doctrine.entitymanager.orm_default',
                'role_entity_class' => 'ErsBase\Entity\Role',
            ],
        ],
    ],
    'validators' => [
        'invokables' => [
            'NotEmptyAllowZero' => '\ErsBase\Validator\NotEmptyAllowZero'
         ],
    ],
];
