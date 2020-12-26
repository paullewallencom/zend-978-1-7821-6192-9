# Users Module - README #
 
Configuration Instructions
====
1. Setup Zend Server
2. Setup MySQL Server

		MySQL Credentials:
			DB Name: 		zf_app
			DB User:	 	zf_user
			DB Password:	zf_pass

3. Point vHost comm-app.local to `<Application>/public`
4. Copy Users Module to `<Application>/modules/`
5. Enable users module in `<Application>/config/application.config.php`


SQL Setup
====
* Run all the SQL files in `<Application>/modules/Users/sql/` folders


DB Adapter Setup
====
Add the following configuration to `<Application>/config/autoload/global.php`


	# DB ADAPTER - START

	return array(
		'db' => array(
			'driver'         => 'Pdo',
			'dsn'            => 'mysql:dbname=zf_app;host=localhost',
			'username'         => 'zf_user',
			'password'         => 'zf_pass',
			'driver_options' => array(
				PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''
			),
		),
		'service_manager' => array(
			'factories' => array(
				'Zend\Db\Adapter\Adapter'
						=> 'Zend\Db\Adapter\AdapterServiceFactory',
			),
		),
	);
	
	# DB ADAPTER - END

