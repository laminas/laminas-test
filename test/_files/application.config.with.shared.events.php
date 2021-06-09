<?php

return [
    'modules'                 => [
        'Laminas\Router',
        'Laminas\Validator',
        'Baz',
        'ModuleWithEvents',
    ],
    'module_listener_options' => [
        'config_static_paths' => [],
        'module_paths'        => [
            'Baz'              => __DIR__ . '/Baz/',
            'ModuleWithEvents' => __DIR__ . '/ModuleWithEvents/',
        ],
    ],
];
