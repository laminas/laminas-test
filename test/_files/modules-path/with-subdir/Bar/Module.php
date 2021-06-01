<?php

namespace Bar;

class Module
{
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return [
            'Laminas\Loader\StandardAutoloader' => [
                'namespaces' => [
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ],
            ],
        ];
    }

    public function getServiceConfig()
    {
        return [
            // Legacy Zend Framework aliases
            'aliases' => [
            ],
            'factories' => [
                'BarObject' => function ($sm) {
                    $foo      = $sm->get('FooObject');
                    $foo->bar = 'baz';

                    return $foo;
                }
            ],
        ];
    }
}
