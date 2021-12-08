<?php

declare(strict_types=1);

return [
    'router'      => [
        'routes' => [
            'similar_name_route' => [
                'type'    => 'literal',
                'options' => [
                    'route'    => '/similar-name-test',
                    'defaults' => [
                        'controller' => 'similar_name_index',
                        'action'     => 'unittests',
                    ],
                ],
            ],
        ],
    ],
    'controllers' => [
        'invokables' => [
            'similar_name_index' => 'ModuleWithSimilarName\Test\Controller\IndexController',
        ],
    ],
];
