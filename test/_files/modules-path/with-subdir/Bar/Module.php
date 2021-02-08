<?php

namespace Bar;

use Laminas\Loader\StandardAutoloader;

class Module
{
    /** @return array */
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    /** @return array */
    public function getAutoloaderConfig()
    {
        return [
            StandardAutoloader::class => [
                'namespaces' => [
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ],
            ],
        ];
    }

    /** @return array */
    public function getServiceConfig()
    {
        return [
            // Legacy Zend Framework aliases
            'aliases'   => [],
            'factories' => [
                'BarObject' => function ($sm) {
                    $foo      = $sm->get('FooObject');
                    $foo->bar = 'baz';

                    return $foo;
                },
            ],
        ];
    }
}
