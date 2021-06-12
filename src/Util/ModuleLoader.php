<?php

declare(strict_types=1);

namespace Laminas\Test\Util;

use Laminas\ModuleManager\ModuleManager;
use Laminas\Mvc\Application;
use Laminas\Mvc\Service;
use Laminas\ServiceManager\ServiceManager;

use function is_numeric;

class ModuleLoader
{
    /** @var ServiceManager */
    protected $serviceManager;

    /**
     * Load list of modules or application configuration
     *
     * @param array $configuration
     */
    public function __construct(array $configuration)
    {
        if (! isset($configuration['modules'])) {
            $modules       = $configuration;
            $configuration = [
                'module_listener_options' => [
                    'module_paths' => [],
                ],
                'modules'                 => [
                    'Laminas\Router',
                    'Laminas\Validator',
                ],
            ];
            foreach ($modules as $key => $module) {
                if (is_numeric($key)) {
                    $configuration['modules'][] = $module;
                    continue;
                }
                $configuration['modules'][]                                     = $key;
                $configuration['module_listener_options']['module_paths'][$key] = $module;
            }
        }

        $smConfig             = $configuration['service_manager'] ?? [];
        $this->serviceManager = new ServiceManager();
        (new Service\ServiceManagerConfig($smConfig))->configureServiceManager($this->serviceManager);
        $this->serviceManager->setService('ApplicationConfig', $configuration);
        $this->serviceManager->get('ModuleManager')->loadModules();
    }

    /**
     * Get the application
     */
    public function getApplication(): Application
    {
        return $this->getServiceManager()->get('Application');
    }

    /**
     * Get the module manager
     */
    public function getModuleManager(): ModuleManager
    {
        return $this->getServiceManager()->get('ModuleManager');
    }

    /**
     * Get module by name
     *
     * @return mixed
     */
    public function getModule(string $moduleName)
    {
        return $this->getModuleManager()->getModule($moduleName);
    }

    /**
     * Get the service manager
     */
    public function getServiceManager(): ServiceManager
    {
        return $this->serviceManager;
    }
}
