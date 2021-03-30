<?php
return [
    'router' => [
        'routes' => [
            'namespace_route' => [
                'type' => 'literal',
                'options' => [
                    'route'    => '/namespace-test',
                    'defaults' => [
                        'controller' => 'namespace_index',
                        'action'     => 'unittests',
                    ],
                ],
            ],
        ],
    ],
    'controllers' => [
        'invokables' => [
            'namespace_index' => 'ModuleWithNamespace\TestModule\Controller\IndexController',
        ],
    ],
];
