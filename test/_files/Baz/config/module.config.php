<?php

declare(strict_types=1);

use Baz\Controller\IndexController;

return [
    'router'       => [
        'routes' => [
            'myroute'                  => [
                'type'    => 'literal',
                'options' => [
                    'route'    => '/tests',
                    'defaults' => [
                        'controller' => 'baz_index',
                        'action'     => 'unittests',
                    ],
                ],
            ],
            'myroutebis'               => [
                'type'    => 'literal',
                'options' => [
                    'route'    => '/tests-bis',
                    'defaults' => [
                        'controller' => 'baz_index',
                        'action'     => 'unittests',
                    ],
                ],
            ],
            'persistence'              => [
                'type'    => 'literal',
                'options' => [
                    'route'    => '/tests-persistence',
                    'defaults' => [
                        'controller' => 'baz_index',
                        'action'     => 'persistencetest',
                    ],
                ],
            ],
            'exception'                => [
                'type'    => 'literal',
                'options' => [
                    'route'    => '/exception',
                    'defaults' => [
                        'controller' => 'baz_index',
                        'action'     => 'exception',
                    ],
                ],
            ],
            'redirect'                 => [
                'type'    => 'literal',
                'options' => [
                    'route'    => '/redirect',
                    'defaults' => [
                        'controller' => 'baz_index',
                        'action'     => 'redirect',
                    ],
                ],
            ],
            'redirectToRoute'          => [
                'type'    => 'literal',
                'options' => [
                    'route'    => '/redirect-to-route',
                    'defaults' => [
                        'controller' => 'baz_index',
                        'action'     => 'redirectToRoute',
                    ],
                ],
            ],
            'dnsroute'                 => [
                'type'    => 'hostname',
                'options' => [
                    'route'       => ':subdomain.domain.tld',
                    'constraints' => [
                        'subdomain' => '\w+',
                    ],
                    'defaults'    => [
                        'controller' => 'baz_index',
                        'action'     => 'unittests',
                    ],
                ],
            ],
            'custom-response'          => [
                'type'    => 'literal',
                'options' => [
                    'route'    => '/custom-response',
                    'defaults' => [
                        'controller' => 'baz_index',
                        'action'     => 'custom-response',
                    ],
                ],
            ],
            'register-xpath-namespace' => [
                'type'    => 'literal',
                'options' => [
                    'route'    => '/register-xpath-namespace',
                    'defaults' => [
                        'controller' => 'baz_index',
                        'action'     => 'registerxpathnamespace',
                    ],
                ],
            ],
            'parametrized'             => [
                'type'    => 'segment',
                'options' => [
                    'route'    => '/with-param/:param',
                    'defaults' => [
                        'controller' => 'baz_index',
                        'action'     => 'unittests',
                    ],
                ],
            ],
        ],
    ],
    'controllers'  => [
        'invokables' => [
            'baz_index' => IndexController::class,
        ],
    ],
    'view_manager' => [
        'template_map'        => [
            '404'   => __DIR__ . '/../view/baz/error/404.phtml',
            'error' => __DIR__ . '/../view/baz/error/error.phtml',
        ],
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
];
