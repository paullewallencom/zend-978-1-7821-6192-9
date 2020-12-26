<?php
set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__ . '/../src');
define('APPLICATION_PATH', realpath(__DIR__ . '/../'));
chdir(APPLICATION_PATH);

if (getenv('ZF2_PATH')) { // Support for ZF2_PATH environment variable or git submodule
    $zf2Path = getenv('ZF2_PATH');
} elseif (get_cfg_var('zf2_path')) { // Support for zf2_path directive value
    $zf2Path = get_cfg_var('zf2_path');
} elseif (is_dir(__DIR__ . 'vendor/ZF2/library')) {
    $zf2Path = __DIR__ . 'vendor/ZF2/library';
}

if (getenv('ZEND_SERVER_MODULES_PATH')) {
    $zsModsPath = getenv('ZEND_SERVER_MODULES_PATH');
} elseif (get_cfg_var('zend_server_modules_path')) {
    $zsModsPath = get_cfg_var('zend_server_modules_path');
} elseif (is_dir(__DIR__ . '/vendor/ZendServerGateway')) {
    $zsModsPath = __DIR__ . '/vendor/';
}

if (! isset($zf2Path) || ! isset($zsModsPath)) {
    header('HTTP/1.0 500 Internal Server Error');
    echo 'The Zend Server environment was not set up correctly, please ensure ZF2_PATH and ZEND_SERVER_MODULES_PATH are correctly setup.';
    exit(1);
}

include $zf2Path . '/Zend/Loader/AutoloaderFactory.php';
Zend\Loader\AutoloaderFactory::factory(array(
    'Zend\Loader\StandardAutoloader' => array(
        'autoregister_zf' => true,
        'fallback_autoloader' => true
    )
));

$appConfig = require 'config/application.config.php';
$appConfig['module_listener_options']['module_paths'][] = $zsModsPath;

Zend\Mvc\Application::init($appConfig)->run();

