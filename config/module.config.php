<?php
/**
 * BB's Zend Framework 2 Components
 * 
 * AdminModule
 *
 * @package   [MyApplication]
 * @package   BB's Zend Framework 2 Components
 * @package   AdminModule
 * @author    Björn Bartels [dragon-projects.net] <info@dragon-projects.net>
 * @link      http://gitlab.dragon-projects.de:81/groups/zf2
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @copyright copyright (c) 2016 Björn Bartels [dragon-projects.net] <info@dragon-projects.net>
 */

return array(
    'settings' => array(
        'scopes' => array(
            'system'        => 'system',
            'user'          => 'user',
            'client'        => 'client',
            'application'   => 'application',
        ),
        // ???
        'types' => array(
            'system'        => 'system',
            'user'          => 'user',
            'client'        => 'client',
            'application'   => 'application',
            'module'        => 'module',
        )
    ),
    'controllers' => array(
        'invokables' => array(
            'Admin\Controller\Index'          => 'Admin\Controller\IndexController',
            'Admin\Controller\Users'          => 'Admin\Controller\UsersController',
            'Admin\Controller\Zfcuser'        => 'Admin\Controller\ZfcuserController',
            'Admin\Controller\Acl'            => 'Admin\Controller\AclController',
            'Admin\Controller\Applications'   => 'Admin\Controller\ApplicationsController',
            'Admin\Controller\Clients'        => 'Admin\Controller\ClientsController',
            'Admin\Controller\Settings'       => 'Admin\Controller\SettingsController',
        ),
    ),
        
    'service_manager' => array(
        'factories' => array(
            
            // table services
            'Admin\Model\AclTable'            => 'Admin\Factory\AclTableFactory',
            'Admin\Model\AclresourceTable'    => 'Admin\Factory\AclresourceTableFactory',
            'Admin\Model\AclroleTable'        => 'Admin\Factory\AclroleTableFactory',
            'Admin\Model\ApplicationsTable'   => 'Admin\Factory\ApplicationsTableFactory',
            'Admin\Model\ClientsTable'        => 'Admin\Factory\ClientsTableFactory',
            'Admin\Model\SettingsTable'       => 'Admin\Factory\SettingsTableFactory',
            'Admin\Model\UserTable'           => 'Admin\Factory\UserTableFactory',
            'Admin\Model\UserProfileTable'    => 'Admin\Factory\UserProfileTableFactory',
        ),
            
        'aliases' => array(
                
            // table aliases
            'AdminAclTable'                   => 'Admin\Model\AclTable',
            'AdminAclresourceTable'           => 'Admin\Model\AclresourceTable',
            'AdminAclroleTable'               => 'Admin\Model\AclroleTable',
            'AdminApplicationsTable'          => 'Admin\Model\ApplicationsTable',
            'AdminClientsTable'               => 'Admin\Model\ClientsTable',
            'AdminSettingsTable'              => 'Admin\Model\SettingsTable',
            'AdminUserTable'                  => 'Admin\Model\UserTable',
            'AdminUserProfileTable'           => 'Admin\Model\UserProfileTable',
        ),
        
    ),
        
    'acl_helpers' => array (
        'invokables' => array(
            // override or add a view helper
            'isallowed'   => 'Admin\View\Helper\Isallowed',
            'isdenied'    => 'Admin\View\Helper\Isdenied',
        ),
    ),
        
    'translator' => array(
        //'locale' => 'en_US', // deactivated because of SlmLocale module
        'translation_file_patterns' => array(
            array(
                'type'        => 'gettext',
                'base_dir'    => __DIR__ . '/../language',
                'pattern'    => '%s.mo',
            ),
        ),
    ),
        
    'router' => array(
    'routes' => array(
    'admin' => array(
                'type'    => 'Literal',
                'options' => array(
                    // Change this to something specific to your module
                    'route'    => '/admin',
                    'defaults' => array(
                        // Change this value to reflect the namespace in which
                        // the controllers for your module are found
                        '__NAMESPACE__' => 'Admin\Controller',
                        'controller'    => 'Index',
                        'action'        => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    // This route is a sane default when developing a module;
                    // as you solidify the routes for your module, however,
                    // you may want to remove it and replace it with more
                    // specific routes.
                    'default' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/[:controller[/:action[/:user_id[/:token]]]]',
                            'constraints' => array(
                                'controller'    => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action'        => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'user_id'        => '[0-9]*',
                                'token'            => '.*',
                            ),
                            'defaults' => array(
                                'action'         => 'index'
                            ),
                        ),
                    ),
                    'acledit' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/acl[/:action[/:acl_id]]',
                            'constraints' => array(
                                'action'        => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'acl_id'        => '[0-9]*',
                            ),
                            'defaults' => array(
                                'controller'    => 'Admin\Controller\Acl',
                            ),
                        ),
                    ),
                    'settingsedit' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/settings[/:action[/:set_id]]',
                            'constraints' => array(
                                'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'set_id'     => '[0-9]*',
                            ),
                            'defaults' => array(
                                'controller' => 'Admin\Controller\Settings',
                            ),
                        ),
                    ),
                    'clientsedit' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/clients[/:action[/:client_id]]',
                            'constraints' => array(
                                'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'client_id'  => '[0-9]*',
                            ),
                            'defaults' => array(
                                'controller' => 'Admin\Controller\Clients',
                            ),
                        ),
                    ),
                    'applicationsedit' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/apps[/:action[/:application_id]]',
                            'constraints' => array(
                                'action'         => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'application_id' => '[0-9]*',
                            ),
                            'defaults' => array(
                                'controller'     => 'Admin\Controller\Applications',
                            ),
                        ),
                    ),
                ),
    ),

    'userconfirmation' => array(
                'type'    => 'Segment',
                'options' => array(
                    'route'    => '/confirmuserregistration[/:user_id[/:confirmtoken]]',
                    'constraints' => array(
                        'user_id'        => '[a-zA-Z0-9_-]*',
                        'confirmtoken'    => '.*',
                    ),
                    'defaults' => array(
                        'controller'    => 'Admin\Controller\Users',
                        'action'        => 'confirm'
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(),
    ),
    'useractivation' => array(
                'type'    => 'Segment',
                'options' => array(
                    'route'    => '/activateuser[/:user_id[/:activatetoken]]',
                    'constraints' => array(
                        'user_id'        => '[a-zA-Z0-9_-]*',
                        'activatetoken'    => '.*',
                    ),
                    'defaults' => array(
                        'controller'    => 'Admin\Controller\Users',
                        'action'        => 'activate'
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(),
    ),
    'userrequestpasswordreset' => array(
                'type'    => 'Literal',
                'options' => array(
                    'route'    => '/requestpasswordreset',
                    'constraints' => array(
                    ),
                    'defaults' => array(
                        'controller'    => 'zfcuser',
                        'action'        => 'requestpasswordreset'
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(),
    ),
    'userresetpassword' => array(
                'type'    => 'Segment',
                'options' => array(
                    'route'    => '/resetpassword[/:user_id[/:resettoken]]',
                    'constraints' => array(
                        'user_id'        => '[a-zA-Z0-9_-]*',
                        'resettoken'    => '.*',
                    ),
                    'defaults' => array(
                        'controller'    => 'zfcuser',
                        'action'        => 'resetpassword'
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(),
    ),
    'zfcuser' => array(
                'child_routes' => array(
                    'userprofile' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route' => '/profile',
                            'defaults' => array(
                                'controller' => 'zfcuser',
                                'action'     => 'index',
                            ),
                        ),
                    ),
                    'edituserdata' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route' => '/edit-userdata',
                            'defaults' => array(
                                'controller' => 'zfcuser',
                                'action'     => 'edituserdata',
                            ),
                        ),
                    ),
                    'edituserprofile' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route' => '/edit-profile',
                            'defaults' => array(
                                'controller' => 'zfcuser',
                                'action'     => 'edituserprofile',
                            ),
                        ),
                    ),
                ),
    ),
    ),
    ),
        
    'view_manager' => array(
    'template_map' => array(
    'mails/userconfirm_html'    => __DIR__ . '/../view/mails/userconfirm_html.phtml',
    'mails/userconfirm_txt'        => __DIR__ . '/../view/mails/userconfirm_txt.phtml',
    'mails/useractivate_html'    => __DIR__ . '/../view/mails/useractivate_html.phtml',
    'mails/useractivate_txt'    => __DIR__ . '/../view/mails/useractivate_txt.phtml',
    ),
    'template_path_stack' => array(
    'admin' => __DIR__ . '/../view',
    ),
    'strategies' => array(
    'ViewJsonStrategy'
    )
    ),
        
    'navigation' => array(
    'default' => array(
    'account' => array(
                'label' => 'account',
                'icon'    => 'user',
                'route' => 'zfcuser',
                'order'    => 99901,
                'badge' => array('type' => 'warning', 'value' => '!!!', 'title' => 'Remember to change your password after registration!'),
                'pages'            => array(
                    array(
                        'label'            => 'login',
                        'icon'            => 'power-off',
                        'route'            => 'zfcuser/login',
                        'resource'        => 'mvc:nouser',
                    ),
                    array(
                        'label'            => 'register',
                        'icon'            => 'link',
                        'route'            => 'zfcuser/register',
                        'resource'        => 'mvc:nouser',
                    ),
                    array(
                        'label'            => 'user profile',
                        'icon'            => 'photo',
                        'route'            => 'zfcuser',
                        'resource'        => 'mvc:user',
                    ),
                    array(
                        'label'            => 'edit profile',
                        'icon'            => 'edit',
                        'route'            => 'zfcuser/edituserprofile',
                        'resource'        => 'mvc:user',
                    ),
                    array(
                        'label'            => 'edit userdata',
                        'icon'            => 'user',
                        'route'            => 'zfcuser/edituserdata',
                        'resource'        => 'mvc:user',
                    ),
                    array(
                        'label'            => 'change email',
                        'icon'            => 'envelope',
                        'route'            => 'zfcuser/changeemail',
                        'resource'        => 'mvc:user',
                    ),
                    array(
                        'label'            => 'change password',
                        'icon'            => 'lock',
                        'route'            => 'zfcuser/changepassword',
                        'resource'        => 'mvc:user',
                    ),
                    array(
                        'label'            => 'logout',
                        'icon'            => 'power-off',
                        'route'            => 'zfcuser/logout',
                        'resource'        => 'mvc:user',
                    ),
                    array(
                        'label'            => 'reset password',
                        'icon'            => 'life-ring',
                        'route'            => 'userrequestpasswordreset',
                        'resource'        => 'mvc:nouser',
                    ),
                    array(
                        'label'            => 'reset password',
                        'route'            => 'userresetpassword',
                        'visible'        => false,
                    ),
                    array(
                        'label'            => 'user confirmation',
                        'route'            => 'userconfirmation',
                        'visible'        => false,
                    ),
                    array(
                        'label'            => 'user activation',
                        'route'            => 'useractivation',
                        'resource'        => 'mvc:admin',
                        'visible'        => false,
                    ),
                ),
    ), // user/profile
                
    'system' => array(
                'label'            => 'system',
                'icon'            => 'desktop',
                'route'            => 'admin',
                'resource'        => 'mvc:admin',
                'order'            => 99903,
                'pages'            => array(
                    array(
                        'label'            => 'info',
                        'icon'            => 'info-circle',
                        'route'            => 'system',
                        'action'         => 'info',
                        'resource'        => 'mvc:admin',
                        'visible'        => true,
                    ),
                    array(
                        'label'            => 'backup',
                        'icon'            => 'copy',
                        'route'            => 'system',
                        'action'         => 'backup',
                        'resource'        => 'mvc:admin',
                        'visible'        => true,
                    ),
                    array(
                        'label'            => 'setup',
                        'icon'            => 'wrench',
                        'route'            => 'setup',
                        'action'         => 'index',
                        //'resource'		=> 'mvc:admin',
                        'visible'        => true,
                        'pages' => array(
                            array(
                                'label'         => 'install',
                                'icon'            => 'magic',
                                'route'         => 'setup',
                                //'resource'		=> 'mvc:admin',
                                'action'         => 'install',
                            ),
                            array(
                                'label'         => 'update',
                                'icon'            => 'refresh',
                                'route'         => 'setup',
                                'resource'        => 'mvc:admin',
                                'action'         => 'update',
                            ),
                        ),
                    ),
                        
                ),
    ), // user/profile
                
    'admin' => array(
                'label'            => 'admin',
                'icon'            => 'cogs',
                'route'            => 'admin',
                'resource'        => 'mvc:admin',
                'order'            => 99902,
                'pages'            => array(
                    array(
                        'label'            => 'users',
                        'icon'            => 'user',
                        'route'            => 'admin/default',
                        'controller'    => 'users',
                        'resource'        => 'mvc:admin',
                        'pages'            => array(
                            array(
                                'label'            => 'add',
                                'route'            => 'admin/default',
                                'controller'    => 'users',
                                'action'         => 'add',
                                'resource'        => 'mvc:admin',
                                'visible'        => true,
                            ),
                            array(
                                'label'            => 'edit',
                                'route'            => 'admin/default',
                                'controller'    => 'users',
                                'action'         => 'edit',
                                'resource'        => 'mvc:admin',
                                'visible'        => false,
                            ),
                            array(
                                'label'            => 'delete',
                                'route'            => 'admin/default',
                                'controller'    => 'users',
                                'action'         => 'delete',
                                'resource'        => 'mvc:admin',
                                'visible'        => false,
                            ),
                        ),
                    ), // admin > users
                    
                    array(
                        'label'            => 'clients',
                        'icon'            => 'building-o',
                        'route'            => 'admin/clientsedit',
                        'action'        => 'index',
                        'resource'        => 'mvc:admin',
                        'pages'            => array(
                            array(
                                'label'            => 'add',
                                'route'            => 'admin/clientsedit',
                                'action'         => 'add',
                                'resource'        => 'mvc:admin',
                                'visible'        => true,
                            ),
                            array(
                                'label'            => 'edit',
                                'route'            => 'admin/clientsedit',
                                'action'         => 'edit',
                                'resource'        => 'mvc:admin',
                                'visible'        => false,
                            ),
                            array(
                                'label'            => 'delete',
                                'route'            => 'admin/clientsedit',
                                'action'         => 'delete',
                                'resource'        => 'mvc:admin',
                                'visible'        => false,
                            ),
                        ),
                    ), // admin > clients
                    
                    array(
                        'label'            => 'applications',
                        'icon'            => 'building-o',
                        'route'            => 'admin/applicationsedit',
                        'action'        => 'index',
                        'resource'        => 'mvc:admin',
                        'pages'            => array(
                            array(
                                'label'            => 'add',
                                'route'            => 'admin/applicationsedit',
                                'action'         => 'add',
                                'resource'        => 'mvc:admin',
                                'visible'        => true,
                            ),
                            array(
                                'label'            => 'edit',
                                'route'            => 'admin/applicationsedit',
                                'action'         => 'edit',
                                'resource'        => 'mvc:admin',
                                'visible'        => false,
                            ),
                            array(
                                'label'            => 'delete',
                                'route'            => 'admin/applicationsedit',
                                'action'         => 'delete',
                                'resource'        => 'mvc:admin',
                                'visible'        => false,
                            ),
                        ),
                    ), // admin > clients
                    
                    array(
                        'label'         => 'permissions',
                        'icon'            => 'lock',
                        'route'            => 'admin/acledit',
                        'action'         => 'index',
                        'resource'        => 'mvc:admin',
                        'pages'            => array(
                            array(
                                'label'            => 'ACL',
                                'icon'            => 'asterisk',
                                'route'            => 'admin/acledit',
                                'action'         => 'index',
                                'resource'        => 'mvc:admin',
                                'visible'        => true,
                            ),
                            array(
                                'label'            => 'roles',
                                'icon'            => 'user',
                                'route'            => 'admin/acledit',
                                'action'         => 'roles',
                                'resource'        => 'mvc:admin',
                                'visible'        => true,
                                'pages' => array(
                                    array(
                                        'label'         => 'add',
                                        'route'         => 'admin/acledit',
                                        'resource'        => 'mvc:admin',
                                        'action'         => 'addrole',
                                        'visible'        => true,
                                    ),
                                    array(
                                        'label'         => 'edit',
                                        'route'         => 'admin/acledit',
                                        'resource'        => 'mvc:admin',
                                        'action'         => 'editrole',
                                        'visible'        => false,
                                    ),
                                    array(
                                        'label'         => 'delete',
                                        'route'         => 'admin/acledit',
                                        'resource'        => 'mvc:admin',
                                        'action'         => 'deleterole',
                                        'visible'        => false,
                                    ),
                                ),
                            ),
                            array(
                                'label'            => 'resources',
                                'icon'            => 'list-alt',
                                'route'            => 'admin/acledit',
                                'action'         => 'resources',
                                'resource'        => 'mvc:admin',
                                'visible'        => true,
                                'pages' => array(
                                    array(
                                        'label'         => 'add',
                                        'route'         => 'admin/acledit',
                                        'resource'        => 'mvc:admin',
                                        'action'         => 'addresource',
                                        'visible'        => true,
                                    ),
                                    array(
                                        'label'         => 'edit',
                                        'route'         => 'admin/acledit',
                                        'resource'        => 'mvc:admin',
                                        'action'         => 'editresource',
                                        'visible'        => false,
                                    ),
                                    array(
                                        'label'         => 'delete',
                                        'route'         => 'admin/acledit',
                                        'resource'        => 'mvc:admin',
                                        'action'         => 'deleteresource',
                                        'visible'        => false,
                                    ),
                                ),
                            ),
                        ),
                    ),
                        
                    array(
                        'label'         => 'settings',
                        'icon'            => 'cog',
                        'route'            => 'admin/settingsedit',
                        'action'         => 'index',
                        'resource'        => 'mvc:user',
                        'visible'        => true,
                        'pages'            => array(
                            array(
                                'label'            => 'add',
                                'route'            => 'admin/settingsedit',
                                'action'         => 'add',
                                'resource'        => 'mvc:user',
                                'visible'        => true,
                            ),
                            array(
                                'label'            => 'edit',
                                'route'            => 'admin/settingsedit',
                                'action'         => 'edit',
                                'resource'        => 'mvc:user',
                                'visible'        => false,
                            ),
                            array(
                                'label'            => 'delete',
                                'route'            => 'admin/settingsedit',
                                'action'         => 'delete',
                                'resource'        => 'mvc:user',
                                'visible'        => false,
                            ),
                        ),
                    ),
                ),
    ),
    ),
    ),
                
);
