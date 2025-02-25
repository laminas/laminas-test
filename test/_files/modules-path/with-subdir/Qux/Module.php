<?php

declare(strict_types=1);

namespace Qux;

use Laminas\ModuleManager\Feature\InitProviderInterface;
use Laminas\ModuleManager\ModuleManagerInterface;
use Override;
use stdClass;

/** @psalm-suppress UnusedClass */
class Module implements InitProviderInterface
{
    public function getConfig(): array
    {
        return [
            'service_manager' => [
                'factories' => [
                    'QuxObject' => static fn(): stdClass => new stdClass(),
                ],
            ],
        ];
    }

    #[Override]
    public function init(ModuleManagerInterface $manager): void
    {
        $manager->loadModule('Foo');
    }
}
