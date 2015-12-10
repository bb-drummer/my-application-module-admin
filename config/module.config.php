<?php
return array(
	'settings' => array(
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
    ),
);
