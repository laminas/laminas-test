<?php

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
            'similar_name_2_index' => 'ModuleWithSimilarName\TestModule\Controller\IndexController',
        ],
    ],
];
