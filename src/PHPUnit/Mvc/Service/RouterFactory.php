<?php

/**
 * @see       https://github.com/laminas/laminas-test for the canonical source repository
 * @copyright https://github.com/laminas/laminas-test/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-test/blob/master/LICENSE.md New BSD License
 */
namespace Laminas\Test\PHPUnit\Mvc\Service;

use Laminas\Mvc\Router\Http\TreeRouteStack as HttpRouter;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class RouterFactory implements FactoryInterface
{
    /**
     * Create and return router
     *
     * @param  ServiceLocatorInterface $serviceLocator
     * @param  string|null $cName
     * @param  string|null $rName
     * @return HttpRouter
     */
    public function createService(ServiceLocatorInterface $serviceLocator, $cName = null, $rName = null)
    {
        $config       = $serviceLocator->get('Config');
        $routerConfig = isset($config['router']) ? $config['router'] : array();
        return HttpRouter::factory($routerConfig);
    }
}
