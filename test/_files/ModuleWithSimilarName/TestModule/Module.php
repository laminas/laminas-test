<?php

/**
 * @see       https://github.com/laminas/laminas-test for the canonical source repository
 */

namespace ModuleWithSimilarName\TestModule;

use Laminas\Loader\StandardAutoloader;

class Module
{
    /**
     * @psalm-return array<string, mixed>
     */
    public function getConfig(): array
    {
        return include __DIR__ . '/config/module.config.php';
    }

    /**
     * @return string[][][]
     * @psalm-return array<string, array<string, array<string, string>>>
     */
    public function getAutoloaderConfig(): array
    {
        return [
            StandardAutoloader::class => [
                'namespaces' => [
                    __NAMESPACE__ => __DIR__ . '/src/',
                ],
            ],
        ];
    }
}
