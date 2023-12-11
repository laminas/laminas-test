<?php

declare(strict_types=1);

use ModuleWithNamespace\TestModule\Controller\IndexController;

return [
    'router'      => [
        'routes' => [
            'namespace_route' => [
                'type'    => 'literal',
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
            'namespace_index' => IndexController::class,
        ],
    ],
];
