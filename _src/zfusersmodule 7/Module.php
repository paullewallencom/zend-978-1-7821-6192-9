<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonModule for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Users;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;

use Users\Model\User;
use Users\Model\UserTable;
use Users\Model\Upload;
use Users\Model\UploadTable;
use Users\Model\ImageUpload;
use Users\Model\ImageUploadTable;

use Users\Model\StoreOrder;
use Users\Model\StoreProduct;

use Users\Model\StoreOrderTable;
use Users\Model\StoreProductTable;

use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;

use Zend\Authentication\Adapter\DbTable as DbTableAuthAdapter;
use Zend\Authentication\AuthenticationService;

class Module implements AutoloaderProviderInterface
{
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
		    // if we're in a namespace deeper than one level we need to fix the \ in the path
                    __NAMESPACE__ => __DIR__ . '/src/' . str_replace('\\', '/' , __NAMESPACE__),
                ),
            ),
        );
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function onBootstrap($e)
    {
        // You may not need to do this if you're doing it elsewhere in your
        // application
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
        
        $sharedEventManager = $eventManager->getSharedManager(); // The shared event manager
        
        $sharedEventManager->attach(__NAMESPACE__, MvcEvent::EVENT_DISPATCH, function($e) {
        	$controller = $e->getTarget(); // The controller which is dispatched
        	$controllerName = $controller->getEvent()->getRouteMatch()->getParam('controller');
        	 
        	if (!in_array($controllerName, array(	'Users\Controller\Index', 
        											'Users\Controller\Register', 
        											'Users\Controller\Login',
        											'Users\Controller\Store' ))) {
        		$controller->layout('layout/myaccount');
        	}
        });
    }
    
    public function getServiceConfig()
    {
    	return array(
    			'abstract_factories' => array(),
    			'aliases' => array(),
    			'factories' => array(
    				// SERVICES
    				'AuthService' => function($sm) {
    						$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
    						$dbTableAuthAdapter = new DbTableAuthAdapter($dbAdapter, 'user','email','password', 'MD5(?)');
    							
    						$authService = new AuthenticationService();
    						$authService->setAdapter($dbTableAuthAdapter);
    						return $authService;
    				},
    				
    				// DB
    				'UserTable' =>  function($sm) {
    					$tableGateway = $sm->get('UserTableGateway');
    					$table = new UserTable($tableGateway);
    					return $table;
    				},
    				'UserTableGateway' => function ($sm) {
    					$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
    					$resultSetPrototype = new ResultSet();
    					$resultSetPrototype->setArrayObjectPrototype(new User());
    					return new TableGateway('user', $dbAdapter, null, $resultSetPrototype);
    				},
    				
    				'UploadTable' =>  function($sm) {
    					$tableGateway = $sm->get('UploadTableGateway');
    					$uploadSharingTableGateway = $sm->get('UploadSharingTableGateway');
    					$table = new UploadTable($tableGateway, $uploadSharingTableGateway);
    					return $table;
    				},
    				'UploadTableGateway' => function ($sm) {
    					$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
    					$resultSetPrototype = new ResultSet();
    					$resultSetPrototype->setArrayObjectPrototype(new Upload());
    					return new TableGateway('uploads', $dbAdapter, null, $resultSetPrototype);
    				},

    				'UploadSharingTableGateway' => function ($sm) {
    					$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
    					return new TableGateway('uploads_sharing', $dbAdapter);
    				},
    				
    				'ChatMessagesTableGateway' => function ($sm) {
    					$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
    					return new TableGateway('chat_messages', $dbAdapter);
    				},
    				    
    				'ImageUploadTable' =>  function($sm) {
    					$tableGateway = $sm->get('ImageUploadTableGateway');
    					$table = new ImageUploadTable($tableGateway);
    					return $table;
    				},
    				'ImageUploadTableGateway' => function ($sm) {
    					$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
    					$resultSetPrototype = new ResultSet();
    					$resultSetPrototype->setArrayObjectPrototype(new ImageUpload());
    					return new TableGateway('image_uploads', $dbAdapter, null, $resultSetPrototype);
    				},
    				
    				
    				// DB Store Objects
    				'StoreProductsTable' =>  function($sm) {
    					$tableGateway = $sm->get('StoreProductsTableGateway');
    					$table = new StoreProductTable($tableGateway);
    					return $table;
    				},    				
    				'StoreProductsTableGateway' => function ($sm) {
    					$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
    					$resultSetPrototype = new ResultSet();
    					$resultSetPrototype->setArrayObjectPrototype(new StoreProduct());    					
    					return new TableGateway('store_products', $dbAdapter, null, $resultSetPrototype);
    				},
    				'StoreOrdersTable' =>  function($sm) {
    					$tableGateway = $sm->get('StoreOrdersTableGateway');
    					$productTableGateway = $sm->get('StoreProductsTableGateway');
    					$table = new StoreOrderTable($tableGateway, $productTableGateway);
    					return $table;
    				},    				
    				'StoreOrdersTableGateway' => function ($sm) {
    					$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
    					$resultSetPrototype = new ResultSet();
    					$resultSetPrototype->setArrayObjectPrototype(new StoreOrder());
    					return new TableGateway('store_orders', $dbAdapter, null, $resultSetPrototype);
    				},
				    				    				

    				// FORMS
    				'LoginForm' => function ($sm) {
    					$form = new \Users\Form\LoginForm();
    					$form->setInputFilter($sm->get('LoginFilter'));
    					return $form;
    				},
    				'RegisterForm' => function ($sm) {
    					$form = new \Users\Form\RegisterForm();
    					$form->setInputFilter($sm->get('RegisterFilter'));
    					return $form;
    				},
    				'UserEditForm' => function ($sm) {
    					$form = new \Users\Form\UserEditForm();
    					$form->setInputFilter($sm->get('UserEditFilter'));
    					return $form;
    				},
    				'UploadForm' => function ($sm) {
    					$form = new \Users\Form\UploadForm();
    					return $form;
    				},
    				'UploadEditForm' => function ($sm) {
    					$form = new \Users\Form\UploadEditForm();
    					return $form;
    				},
    				'UploadShareForm' => function ($sm) {
    					$form = new \Users\Form\UploadShareForm();
    					return $form;
    				},
    				'ImageUploadForm' => function ($sm) {
    					$form = new \Users\Form\ImageUploadForm();
    					$form->setInputFilter($sm->get('ImageUploadFilter'));
    					return $form;
    				},    				
    				
    				
    				// FILTERS
    				'LoginFilter' => function ($sm) {
    					return new \Users\Form\LoginFilter();
    				},
    				'RegisterFilter' => function ($sm) {
    					return new \Users\Form\RegisterFilter();
    					
    				},
    				'UserEditFilter' => function ($sm) {
    					return new \Users\Form\UserEditFilter();
    						
    				},
    				'ImageUploadFilter' => function ($sm) {
    					return new \Users\Form\ImageUploadFilter();
    				
    				},    				
    				
    			),
    			'invokables' => array(),
    			'services' => array(),
    			'shared' => array(),
    	);
    }  
    
}
