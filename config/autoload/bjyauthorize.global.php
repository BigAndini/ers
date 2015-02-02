<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

// For PHP <= 5.4, you should replace any ::class references with strings
// remove the first \ and the ::class part and encase in single quotes

return [
    'bjyauthorize' => [

        // set the 'guest' role as default (must be defined in a role provider)
        'default_role' => 'guest',

        /* this module uses a meta-role that inherits from any roles that should
         * be applied to the active user. the identity provider tells us which
         * roles the "identity role" should inherit from.
         * for ZfcUser, this will be your default identity provider
        */
        'identity_provider' => \BjyAuthorize\Provider\Identity\ZfcUserZendDb::class,
        #'identity_provider' => \ErsAuthorize\Provider\Identity\ErsUserZendDb::class,

        /* If you only have a default role and an authenticated role, you can
         * use the 'AuthenticationIdentityProvider' to allow/restrict access
         * with the guards based on the state 'logged in' and 'not logged in'.
         *
         * 'default_role'       => 'guest',         // not authenticated
         * 'authenticated_role' => 'user',          // authenticated
         * 'identity_provider'  => \BjyAuthorize\Provider\Identity\AuthenticationIdentityProvider::class,
         */

        /* role providers simply provide a list of roles that should be inserted
         * into the Zend\Acl instance. the module comes with two providers, one
         * to specify roles in a config file and one to load roles using a
         * Zend\Db adapter.
         */
        'role_providers' => [

            /* here, 'guest' and 'user are defined as top-level roles, with
             * 'admin' inheriting from user
             */
            \BjyAuthorize\Provider\Role\Config::class => [
                'guest' => [
                    'children' => [
                        'user'  => [
                            'children' => [
                                'admin' => [],
                            ]
                        ],
                    ],
                ],
            ],

            // this will load roles from the user_role table in a database
            // format: user_role(role_id(varchar], parent(varchar))
            \BjyAuthorize\Provider\Role\ZendDb::class => [
                'table'                 => 'user_role',
                'identifier_field_name' => 'id',
                'role_id_field'         => 'roleId',
                'parent_role_field'     => 'parent_id',
            ],

            // this will load roles from
            // the 'BjyAuthorize\Provider\Role\ObjectRepositoryProvider' service
            /*\BjyAuthorize\Provider\Role\ObjectRepositoryProvider::class => [
                // class name of the entity representing the role
                'role_entity_class' => 'Application\Entity\Role',
                // service name of the object manager
                'object_manager'    => 'My\Doctrine\Common\Persistence\ObjectManager',
            ],*/
            /*\BjyAuthorize\Provider\Role\ObjectRepositoryProvider::class => [
                // class name of the entity representing the role
                'role_entity_class' => 'Application\Entity\Role',
                // service name of the object manager
                #'object_manager'    => 'My\Doctrine\Common\Persistence\ObjectManager',
            ],*/
        ],

        // resource providers provide a list of resources that will be tracked
        // in the ACL. like roles, they can be hierarchical
        'resource_providers' => [
            /*\BjyAuthorize\Provider\Resource\Config::class => [
                'pants' => [],
            ],*/
        ],

        /* rules can be specified here with the format:
         * [roles (array], resource, [privilege (array|string], assertion])
         * assertions will be loaded using the service manager and must implement
         * Zend\Acl\Assertion\AssertionInterface.
         * *if you use assertions, define them using the service manager!*
         */
        /*'rule_providers' => [
            \BjyAuthorize\Provider\Rule\Config::class => [
                'allow' => [
                    // allow guests and users (and admins, through inheritance)
                    // the "wear" privilege on the resource "pants"
                    [['guest', 'user'], 'pants', 'wear'],
                ],

                // Don't mix allow/deny rules if you are using role inheritance.
                // There are some weird bugs.
                'deny' => [
                    // ...
                ],
            ],
        ],*/

        /* Currently, only controller and route guards exist
         *
         * Consider enabling either the controller or the route guard depending on your needs.
         */
        'guards' => [
            /* If this guard is specified here (i.e. it is enabled], it will block
             * access to all controllers and actions unless they are specified here.
             * You may omit the 'action' index to allow access to the entire controller
             */
            \BjyAuthorize\Guard\Controller::class => [
                /* Admin */
                ['controller' => 'Admin\Controller\Admin', 'roles' => ['admin']],
                ['controller' => 'Admin\Controller\PriceLimit', 'roles' => ['admin']],
                ['controller' => 'Admin\Controller\Product', 'roles' => ['admin']],
                ['controller' => 'Admin\Controller\ProductPrice', 'roles' => ['admin']],
                ['controller' => 'Admin\Controller\ProductVariant', 'roles' => ['admin']],
                ['controller' => 'Admin\Controller\Tax', 'roles' => ['admin']],
                
                /* Application */
                ['controller' => 'Application\Controller\Index', 'roles' => ['guest', 'user']],
                
                /* PreReg */
                ['controller' => 'PreReg\Controller\Cart', 'action' => 'reset', 'roles' => ['user']],
                #['controller' => 'PreReg\Controller\Cart', 'roles' => ['guest']],
                ['controller' => 'PreReg\Controller\Order', 'roles' => ['guest']],
                ['controller' => 'PreReg\Controller\Package', 'roles' => ['guest']],
                ['controller' => 'PreReg\Controller\Product', 'roles' => ['guest']],
                ['controller' => 'PreReg\Controller\Participant', 'roles' => ['guest']],
                
                /* ZfcUser */
                ['controller' => 'zfcuser', 'roles' => ['guest']],
                
                /* Doctrine ORM */
                ['controller' => 'DoctrineORMModule\Yuml\YumlController', 'roles' => ['admin']],
            ],

            /* If this guard is specified here (i.e. it is enabled], it will block
             * access to all routes unless they are specified here.
             */
            /*\BjyAuthorize\Guard\Route::class => [
                ['route' => 'zfcuser', 'roles' => ['user']],
                ['route' => 'zfcuser/logout', 'roles' => ['user']],
                ['route' => 'zfcuser/login', 'roles' => ['guest']],
                ['route' => 'zfcuser/register', 'roles' => ['guest']],
                ['route' => 'zfcuser/changepassword', 'roles' => ['user']],
                ['route' => 'zfcuser/changeemail', 'roles' => ['user']],
                // Below is the default index action used by the ZendSkeletonApplication
                ['route' => 'home', 'roles' => ['guest', 'user']],
                #['route' => 'guest', 'roles' => ['guest', 'user']],
                ['route' => 'admin', 'roles' => ['admin']],
            ],*/
        ],
    ],
];