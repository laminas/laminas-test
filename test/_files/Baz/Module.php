<?php

declare(strict_types=1);

namespace Baz;

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
}
