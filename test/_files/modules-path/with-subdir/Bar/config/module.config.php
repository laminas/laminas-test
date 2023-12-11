<?php

declare(strict_types=1);

use Bar\Controller\IndexController;

return [
    'router'       => [
        'routes' => [
            'barroute' => [
                'type'    => 'literal',
                'options' => [
                    'route'    => '/bar-test',
                    'defaults' => [
                        'controller' => 'bar_index',
                        'action'     => 'unittests',
                    ],
                ],
            ],
        ],
    ],
    'controllers'  => [
        'invokables' => [
            'bar_index' => IndexController::class,
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
];
