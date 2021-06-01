<?php

namespace Foo;

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
                'FooObject' => function ($sm) {
                    return new \stdClass();
                }
            ],
        ];
    }
}
