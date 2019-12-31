<?php

/**
 * @see       https://github.com/laminas/laminas-test for the canonical source repository
 * @copyright https://github.com/laminas/laminas-test/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-test/blob/master/LICENSE.md New BSD License
 */
namespace Laminas\Test\PHPUnit\Mvc\Service;

use Laminas\Mvc\Service\ServiceListenerFactory as BaseServiceListenerFactory;

class ServiceListenerFactory extends BaseServiceListenerFactory
{
    /**
     * Create default service configuration
     */
    public function __construct()
    {
        // merge basee config with specific tests config
        $this->defaultServiceConfig = array_replace_recursive(
            $this->defaultServiceConfig,
            array('factories' => array(
                'Request' => function($sm) {
                    return new \Laminas\Http\PhpEnvironment\Request();
                },
                'Response' => function($sm) {
                    return new \Laminas\Http\PhpEnvironment\Response();
                },
                'Router' => 'Laminas\Test\PHPUnit\Mvc\Service\RouterFactory',
                'ViewManager' => function($sm) {
                    return new \Laminas\Mvc\View\Http\ViewManager();
                },
            ))
        );
    }
}
