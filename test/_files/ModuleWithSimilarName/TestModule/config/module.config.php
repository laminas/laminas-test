<?php

declare(strict_types=1);

use ModuleWithSimilarName\TestModule\Controller\IndexController;

return [
    'router'      => [
        'routes' => [
            'similar_name_2_route' => [
                'type'    => 'literal',
                'options' => [
                    'route'    => '/similar-name-2-test',
                    'defaults' => [
                        'controller' => 'similar_name_2_index',
                        'action'     => 'unittests',
                    ],
                ],
            ],
        ],
    ],
    'controllers' => [
        'invokables' => [
            'similar_name_2_index' => IndexController::class,
        ],
    ],
];
