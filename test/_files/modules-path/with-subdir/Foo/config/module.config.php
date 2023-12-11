<?php

declare(strict_types=1);

use Foo\Controller\IndexController;

return [
    'router'       => [
        'routes' => [
            'fooroute' => [
                'type'    => 'literal',
                'options' => [
                    'route'    => '/foo-test',
                    'defaults' => [
                        'controller' => 'foo_index',
                        'action'     => 'unittests',
                    ],
                ],
            ],
        ],
    ],
    'controllers'  => [
        'invokables' => [
            'foo_index' => IndexController::class,
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
];
