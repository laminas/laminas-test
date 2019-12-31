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
        if (! isset($configuration['modules'])) {
            $modules = $configuration;
            $configuration = [
                'module_listener_options' => [
                    'module_paths' => [],
                ],
                'modules' => [
                    'Laminas\Router',
                    'Laminas\Validator',
                ],
            ];
            foreach ($modules as $key => $module) {
                if (is_numeric($key)) {
                    $configuration['modules'][] = $module;
                    continue;
                }
                $configuration['modules'][] = $key;
                $configuration['module_listener_options']['module_paths'][$key] = $module;
            }
        }

        $smConfig = isset($configuration['service_manager']) ? $configuration['service_manager'] : [];
        $this->serviceManager = new ServiceManager();
        (new Service\ServiceManagerConfig($smConfig))->configureServiceManager($this->serviceManager);
        $this->serviceManager->setService('ApplicationConfig', $configuration);
        $this->serviceManager->get('ModuleManager')->loadModules();
    }

    /**
     * Get the application
     *
     * @return \Laminas\Mvc\Application
     */
    public function getApplication()
    {
        return $this->getServiceManager()->get('Application');
    }

    /**
     * Get the module manager
     *
     * @return \Laminas\ModuleManager\ModuleManager
     */
    public function getModuleManager()
    {
        return $this->getServiceManager()->get('ModuleManager');
    }

    /**
     * Get module by name
     *
     * @param $moduleName
     * @return mixed
     */
    public function getModule($moduleName)
    {
        return $this->getModuleManager()->getModule($moduleName);
    }

    /**
     * Get the service manager
     *
     * @return ServiceManager
     */
    public function getServiceManager()
    {
        return $this->serviceManager;
    }
}
