<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Users\Controller\Index' => 'Users\Controller\IndexController',
            'Users\Controller\Register' => 'Users\Controller\RegisterController',
            'Users\Controller\Login' => 'Users\Controller\LoginController',            
        	'Users\Controller\UserManager' => 'Users\Controller\UserManagerController',
        	'Users\Controller\UploadManager' => 'Users\Controller\UploadManagerController',
        	'Users\Controller\GroupChat' => 'Users\Controller\GroupChatController',
        	'Users\Controller\MediaManager' => 'Users\Controller\MediaManagerController',
        	'Users\Controller\Search' => 'Users\Controller\SearchController',        	
        	'Users\Controller\Store' => 'Users\Controller\StoreController',        	
        	'Users\Controller\StoreAdmin' => 'Users\Controller\StoreAdminController',
        ),
    ),
    'router' => array(
        'routes' => array(
            'users' => array(
                'type'    => 'Literal',
                'options' => array(
                    // Change this to something specific to your module
                    'route'    => '/users',
                    'defaults' => array(
                        // Change this value to reflect the namespace in which
                        // the controllers for your module are found
                        '__NAMESPACE__' => 'Users\Controller',
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
                    'login' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/login[/:action]',
                            'constraints' => array(
                                'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ),
                        	'defaults' => array(
                        		'controller' => 'Users\Controller\Login',
                        		'action'     => 'index',
                        	),                        		
                        ),
                    ),
                	'register' => array(
               			'type'    => 'Segment',
               			'options' => array(
               				'route'    => '/register[/:action]',
               				'constraints' => array(
               					'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
               				),
               				'defaults' => array(
               					'controller' => 'Users\Controller\Register',
               					'action'     => 'index',
               				),
               			),
                	), 
                	'user-manager' => array(
                		'type'    => 'Segment',
                		'options' => array(
                			'route'    => '/user-manager[/:action[/:id]]',
                			'constraints' => array(
                				'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                				'id'     => '[a-zA-Z0-9_-]*',
                			),
                			'defaults' => array(
                				'controller' => 'Users\Controller\UserManager',
                				'action'     => 'index',
                			),
                		),
                	),
                	'upload-manager' => array(
                		'type'    => 'Segment',
                		'options' => array(
                			'route'    => '/upload-manager[/:action[/:id]]',
                			'constraints' => array(
                				'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                				'id'     => '[a-zA-Z0-9_-]*',                				
                			),
                			'defaults' => array(
                				'controller' => 'Users\Controller\UploadManager',
                				'action'     => 'index',
                			),
                		),
                	),
                	'group-chat' => array(
                		'type'    => 'Segment',
                		'options' => array(
                			'route'    => '/group-chat[/:action[/:id]]',
                			'constraints' => array(
                				'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                				'id'     => '[a-zA-Z0-9_-]*',
                			),
                			'defaults' => array(
                				'controller' => 'Users\Controller\GroupChat',
                				'action'     => 'index',
                			),
                		),
                	),
                	'media' => array(
                		'type'    => 'Segment',
                		'options' => array(
                			'route'    => '/media[/:action[/:id[/:subaction]]]',
                			'constraints' => array(
                				'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                				'id'     => '[a-zA-Z0-9_-]*',
                				'subaction'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                				
                			),
                			'defaults' => array(
                				'controller' => 'Users\Controller\MediaManager',
                				'action'     => 'index',
                			),
                		),
                	),
                	'search' => array(
                		'type'    => 'Segment',
                		'options' => array(
                			'route'    => '/search[/:action]',
                			'constraints' => array(
                				'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                			),
                			'defaults' => array(
                				'controller' => 'Users\Controller\Search',
                				'action'     => 'index',
                			),
                		),
                	),
                	'store' => array(
                		'type'    => 'Segment',
                		'options' => array(
                			'route'    => '/store[/:action[/:id[/:subaction]]]',
                			'constraints' => array(
                				'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                				'id'     => '[a-zA-Z0-9_-]*',
                				'subaction'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                	
                			),
                			'defaults' => array(
                				'controller' => 'Users\Controller\Store',
                				'action'     => 'index',
                			),
                		),
                	),
                	'store-admin' => array(
                		'type'    => 'Segment',
                		'options' => array(
                			'route'    => '/store-admin[/:action[/:id[/:subaction]]]',
                			'constraints' => array(
                				'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                				'id'     => '[a-zA-Z0-9_-]*',
                				'subaction'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                				 
                			),
                			'defaults' => array(
                				'controller' => 'Users\Controller\StoreAdmin',
                				'action'     => 'index',
                			),
                		),
                	),
                ),
            ),
        ),
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            'users' => __DIR__ . '/../view',
        ),
    	'template_map' => array(
    		'layout/layout'           => __DIR__ . '/../view/layout/default-layout.phtml',
    		'layout/myaccount'           => __DIR__ . '/../view/layout/myaccount-layout.phtml',
    	),
    ),
	// MODULE CONFIGURATIONS
	'module_config' => array(
		'upload_location'           => __DIR__ . '/../data/uploads',
		'image_upload_location'		=> __DIR__ . '/../data/images',
		'search_index'		=> __DIR__ . '/../data/search_index'		
	),
	
	'speck-paypal-api' => array(
		'username' 				=> '<USERNAME>',
		'password' 				=> '<PASSWORD>',
		'signature'				=> '<SIGNATURE>',
		'endpoint'               => 'https://api-3t.sandbox.paypal.com/nvp'
	)
);
