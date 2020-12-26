<?php
return array(
    'modules' => array(
        'ZendServerGateway'
    ),
    'automodules' => array(
        'MyCompany' => __DIR__ . '/../src/MyCompany',
    ),
    'module_listener_options' => array(
        'config_glob_paths'    => array(
            __DIR__ . '/autoload/{,*.}{global,local}.php',
            __DIR__ . '/{,*.}{global,local}.php',
        ),
        'module_paths' => array(
            './',
            './vendor',
        ),
    ),
);
