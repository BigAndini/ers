<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

return array(
    
    'service_manager' => array(
        'aliases' => array(
            'translator' => 'MvcTranslator',
            #'Logger'     => 'EddieJaoude\Zf2Logger',
        ),
        'shared' => array(
            'DOMPDF' => false,
            'ViewPdfRenderer' => false,
            'ErsBase\Service\ETicketService' => false,
            'ErsBase\Service\EmailService' => false,
            'ErsBase\Service\AgegroupService:ticket' => false,
        ),
    ),
    
    'translator' => array(
        'locale' => 'en_US',
        'translation_file_patterns' => array(
            array(
                'type'     => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.mo',
            ),
        ),
    ),
    
    'doctrine' => array(
        'driver' => array(
            'ers_entities' => array(
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => array(__DIR__ . '/../src/ErsBase/Entity'),
            ),

            'orm_default' => array(
                'drivers' => array(
                    'ErsBase\Entity' => 'ers_entities'
                )
            )
        )
    ),
    
    'zfcuser' => array(
        // telling ZfcUser to use our own class
        'user_entity_class'       => 'ErsBase\Entity\User',
        // telling ZfcUserDoctrineORM to skip the entities it defines
        'enable_default_entities' => false,
    ),
 
    'bjyauthorize' => array(
        // Using the authentication identity provider, which basically reads the roles from the auth service's identity
        'identity_provider' => 'BjyAuthorize\Provider\Identity\AuthenticationIdentityProvider',
 
        'role_providers'        => array(
            // using an object repository (entity repository) to load all roles into our ACL
            'BjyAuthorize\Provider\Role\ObjectRepositoryProvider' => array(
                #'object_manager'    => 'doctrine.entity_manager.orm_default',
                'object_manager'    => 'doctrine.entitymanager.orm_default',
                'role_entity_class' => 'ErsBase\Entity\Role',
            ),
        ),
    ),
    'validators' => array(
        'invokables' => array(
            'NotEmptyAllowZero'            => '\ErsBase\Validator\NotEmptyAllowZero'
         ),
    ),
);
