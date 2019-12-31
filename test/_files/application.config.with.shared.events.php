<?php
return array(
    'modules' => array(
        'Laminas\Router',
        'Laminas\Validator',
        'Baz',
        'ModuleWithEvents',
    ),
    'module_listener_options' => array(
        'config_static_paths' => array(),
        'module_paths'        => array(
            'Baz' => __DIR__ . '/Baz/',
            'ModuleWithEvents' => __DIR__ . '/ModuleWithEvents/',
        ),
    ),
);
