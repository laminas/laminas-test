<?php

/**
 * @see       https://github.com/laminas/laminas-test for the canonical source repository
 * @copyright https://github.com/laminas/laminas-test/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-test/blob/master/LICENSE.md New BSD License
 */
namespace Laminas\Test\Util;

use Laminas\Mvc\Service;
use Laminas\ServiceManager\ServiceManager;

class ModuleLoader
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;

    /**
     * Load list of modules or application configuration
     *
     * @param array $configuration
     */
    public function __construct(array $configuration)
    {
        if (!isset($configuration['modules'])) {
            $modules = $configuration;
            $configuration = array(
                'module_listener_options' => array(
                    'module_paths' => array(),
                ),
                'modules' => array(),
            );
            foreach ($modules as $key => $module) {
                if (is_numeric($key)) {
                    $configuration['modules'][] = $module;
                    continue;
                }
                $configuration['modules'][] = $key;
                $configuration['module_listener_options']['module_paths'][$key] = $module;
            }
        }

        $smConfig = isset($configuration['service_manager']) ? $configuration['service_manager'] : array();
        $this->serviceManager = new ServiceManager(new Service\ServiceManagerConfig($smConfig));
        $this->serviceManager->setService('ApplicationConfig', $configuration);
        $this->serviceManager->get('ModuleManager')->loadModules();
    }

    /**
     * Get the application
     *
     * @return Laminas\Mvc\Application
     */
    public function getApplication()
    {
        return $this->getServiceManager()->get('Application');
    }

    /**
     * Get the module manager
     *
     * @return Laminas\ModuleManager\ModuleManager
     */
    public function getModuleManager()
    {
        return $this->getServiceManager()->get('ModuleManager');
    }

    /**
     * Get module
     *
     * @return mixed
     */
    public function getModule($moduleName)
    {
        return $this->getModuleManager()->getModule($moduleName);
    }

    /**
     * Get the service manager
     *
     * @var ServiceManager
     */
    public function getServiceManager()
    {
        return $this->serviceManager;
    }
}
