<?php
/**
 * BB's Zend Framework 2 Components
 * 
 * AdminModule
 *
 * @package		[MyApplication]
 * @package		BB's Zend Framework 2 Components
 * @package		AdminModule
 * @author		Björn Bartels [dragon-projects.net] <info@dragon-projects.net>
 * @link		http://gitlab.dragon-projects.de:81/groups/zf2
 * @license		http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @copyright	copyright (c) 2016 Björn Bartels [dragon-projects.net] <info@dragon-projects.net>
 */

return array(
	'settings' => array(
		'scopes' => array(
			'system'		=> 'system',
			'application'	=> 'application',
			'user'			=> 'user',
		),
		'types' => array(
			'system'		=> 'system',
			'application'	=> 'application',
			'module'		=> 'module',
			'user'			=> 'user',
		)
	),
    'controllers' => array(
        'invokables' => array(
            'Admin\Controller\Index'	=> 'Admin\Controller\IndexController',
            'Admin\Controller\Users'	=> 'Admin\Controller\UsersController',
            'Admin\Controller\Zfcuser'	=> 'Admin\Controller\ZfcuserController',
            'Admin\Controller\Acl'		=> 'Admin\Controller\AclController',
            'Admin\Controller\Clients'	=> 'Admin\Controller\ClientsController',
            'Admin\Controller\Settings' => 'Admin\Controller\SettingsController',
        ),
    ),
	'navigation_helpers' => array (
	    'invokables' => array(
	    	// override or add a view helper
	        'isallowed' => 'Admin\View\Helper\Isallowed',
	        'isdenied' => 'Admin\View\Helper\Isdenied',
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
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'user_id'    => '[0-9]*',
                                'token'      => '.*',
                            ),
                            'defaults' => array(
                            	'action'	 => 'index'
                            ),
                        ),
                    ),
                    'acledit' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/acl[/:action[/:acl_id]]',
                            'constraints' => array(
                                'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'acl_id'    => '[0-9]*',
                            ),
                            'defaults' => array(
                            	'controller' => 'Admin\Controller\Acl',
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
                                'client_id'     => '[0-9]*',
                            ),
                            'defaults' => array(
                            	'controller' => 'Admin\Controller\Clients',
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
						'user_id'		=> '[a-zA-Z0-9_-]*',
						'confirmtoken'	=> '.*',
					),
					'defaults' => array(
						'controller'	=> 'Admin\Controller\Users',
						'action'		=> 'confirm'
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
						'user_id'		=> '[a-zA-Z0-9_-]*',
						'activatetoken'	=> '.*',
					),
					'defaults' => array(
						'controller'	=> 'Admin\Controller\Users',
						'action'		=> 'activate'
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
						'controller'	=> 'zfcuser',
						'action'		=> 'requestpasswordreset'
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
						'user_id'		=> '[a-zA-Z0-9_-]*',
						'resettoken'	=> '.*',
					),
					'defaults' => array(
						'controller'	=> 'zfcuser',
						'action'		=> 'resetpassword'
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
			'mails/userconfirm_html'	=> __DIR__ . '/../view/mails/userconfirm_html.phtml',
			'mails/userconfirm_txt'		=> __DIR__ . '/../view/mails/userconfirm_txt.phtml',
			'mails/useractivate_html'	=> __DIR__ . '/../view/mails/useractivate_html.phtml',
			'mails/useractivate_txt'	=> __DIR__ . '/../view/mails/useractivate_txt.phtml',
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
			array(
					'label' => 'home',
					'icon'	=> 'home',
					'route' => 'home',
					'pages'			=> array(
							'testpage' => array(
									'label'			=> 'test page',
									'icon'			=> 'exclamation-triangle',
									'route'			=> 'application/default',
									'controller' 	=> 'index',
									'action' 		=> 'test',
									'resource'		=> 'mvc:admin',
							),
					),
			),
			/*array(
			 'label'			=> 'YourModule',
					'route' 		=> 'yourmodule/route',
					'controller'	=> 'your_index_controller_or_other',
					'action' 		=> 'your_index_action_or_other',
					'resource'		=> 'mvc:yourmodule.resource',
					'pages' => array(
							array(
									'label' 		=> 'YourModule Action/Item',
									'route' 		=> 'yourmodule/route',
									'resource'		=> 'mvc:yourmodule.resource',
									'controller'	=> 'your_controller',
									'action' 		=> 'your_action',
							),
					),
			),*/
			array(
				'label' => 'account',
				'icon'	=> 'user',
				'route' => 'zfcuser',
				'badge' => array('type' => 'warning', 'value' => '!!!', 'title' => 'Remember to change your password after registration!'),
				'pages'			=> array(
					array(
							'label'			=> 'login',
							'icon'			=> 'power-off',
							'route'			=> 'zfcuser/login',
							'resource'		=> 'mvc:nouser',
					),
					array(
							'label'			=> 'register',
							'icon'			=> 'link',
							'route'			=> 'zfcuser/register',
							'resource'		=> 'mvc:nouser',
					),
					array(
							'label'			=> 'user profile',
							'icon'			=> 'photo',
							'route'			=> 'zfcuser',
							'resource'		=> 'mvc:user',
					),
					array(
							'label'			=> 'edit profile',
							'icon'			=> 'edit',
							'route'			=> 'zfcuser/edituserprofile',
							'resource'		=> 'mvc:user',
					),
					array(
							'label'			=> 'edit userdata',
							'icon'			=> 'user',
							'route'			=> 'zfcuser/edituserdata',
							'resource'		=> 'mvc:user',
					),
					array(
							'label'			=> 'change email',
							'icon'			=> 'envelope',
							'route'			=> 'zfcuser/changeemail',
							'resource'		=> 'mvc:user',
					),
					array(
							'label'			=> 'change password',
							'icon'			=> 'lock',
							'route'			=> 'zfcuser/changepassword',
							'resource'		=> 'mvc:user',
					),
					array(
							'label'			=> 'logout',
							'icon'			=> 'power-off',
							'route'			=> 'zfcuser/logout',
							'resource'		=> 'mvc:user',
					),

					array(
							'label'			=> 'reset password',
							'icon'			=> 'life-ring',
							'route'			=> 'userrequestpasswordreset',
							'resource'		=> 'mvc:nouser',
					),

					array(
							'label'			=> 'reset password',
							'route'			=> 'userresetpassword',
							'visible'		=> false,
					),

					array(
							'label'			=> 'user confirmation',
							'route'			=> 'userconfirmation',
							'visible'		=> false,
					),
					array(
							'label'			=> 'user activation',
							'route'			=> 'useractivation',
							'resource'		=> 'mvc:admin',
							'visible'		=> false,
					),
				),
			),
		),
	),
				
);
