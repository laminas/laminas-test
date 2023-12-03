<?php

declare(strict_types=1);

namespace LaminasTest\Test\PHPUnit\Util;

use Laminas\ModuleManager\Exception\RuntimeException;
use Laminas\ModuleManager\ModuleManager;
use Laminas\Mvc\ApplicationInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\Test\Util\ModuleLoader;
use LaminasTest\Test\ExpectedExceptionTrait;
use ModuleWithNamespace\TestModule\Module;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

use function array_diff;
use function dirname;
use function is_dir;
use function rmdir;
use function scandir;
use function sys_get_temp_dir;
use function unlink;

final class ModuleLoaderTest extends TestCase
{
    use ExpectedExceptionTrait;

    public function tearDownCacheDir(): void
    {
        $cacheDir = sys_get_temp_dir() . '/laminas-module-test';
        if (is_dir($cacheDir)) {
            static::rmdir($cacheDir);
        }
    }

    public static function rmdir(string $dir): bool
    {
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            is_dir("$dir/$file") ? static::rmdir("$dir/$file") : unlink("$dir/$file");
        }

        return rmdir($dir);
    }

    protected function setUp(): void
    {
        $this->tearDownCacheDir();
    }

    protected function tearDown(): void
    {
        $this->tearDownCacheDir();
    }

    public function testCanLoadModule(): void
    {
        require_once dirname(__DIR__, 2) . '/_files/Baz/Module.php';

        $loader = new ModuleLoader(['Baz']);
        $baz    = $loader->getModule('Baz');

        $this->assertInstanceOf(\Baz\Module::class, $baz);
    }

    public function testCanLoadModuleWithNamespace(): void
    {
        $loader = new ModuleLoader([
            'ModuleWithNamespace\TestModule' => dirname(__DIR__, 2) . '/_files/ModuleWithNamespace/TestModule',
        ]);

        /** @var Module $testModule */
        $testModule = $loader->getModule('ModuleWithNamespace\TestModule');

        // phpcs:ignore
        $this->assertInstanceOf('ModuleWithNamespace\TestModule\Module', $testModule);
        $this->assertInstanceOf(Module::class, $testModule);
    }

    public function testCanNotLoadModule(): void
    {
        $this->expectedException(RuntimeException::class, 'could not be initialized');
        new ModuleLoader(['FooBaz']);
    }

    public function testCanLoadModuleWithPath(): void
    {
        $loader = new ModuleLoader(['Baz' => dirname(__DIR__, 2) . '/_files/Baz']);

        /** @var \Baz\Module $baz */
        $baz = $loader->getModule('Baz');

        $this->assertInstanceOf(\Baz\Module::class, $baz);
    }

    public function testCanLoadModules(): void
    {
        require_once dirname(__DIR__, 2) . '/_files/Baz/Module.php';
        require_once dirname(__DIR__, 2) . '/_files/modules-path/with-subdir/Foo/Module.php';

        $loader = new ModuleLoader(['Baz', 'Foo']);

        $baz = $loader->getModule('Baz');
        $foo = $loader->getModule('Foo');

        // phpcs:ignore
        $this->assertInstanceOf('Baz\Module', $baz);
        $this->assertInstanceOf(\Baz\Module::class, $baz);
        $this->assertInstanceOf('Foo\Module', $foo);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testCanLoadModulesWithPath(): void
    {
        $loader = new ModuleLoader([
            'Baz' => dirname(__DIR__, 2) . '/_files/Baz',
            'Foo' => dirname(__DIR__, 2) . '/_files/modules-path/with-subdir/Foo',
        ]);

        $fooObject = $loader->getServiceManager()->get('FooObject');

        $this->assertInstanceOf('stdClass', $fooObject);
    }

    public function testCanLoadModulesFromConfig(): void
    {
        $config = include dirname(__DIR__, 2) . '/_files/application.config.php';
        $loader = new ModuleLoader($config);
        $baz    = $loader->getModule('Baz');

        $this->assertInstanceOf(\Baz\Module::class, $baz);
    }

    public function testCanGetService(): void
    {
        $loader = new ModuleLoader(['Baz' => dirname(__DIR__, 2) . '/_files/Baz']);

        $this->assertInstanceOf(ServiceLocatorInterface::class, $loader->getServiceManager());
        $this->assertInstanceOf(ModuleManager::class, $loader->getModuleManager());
        $this->assertInstanceOf(ApplicationInterface::class, $loader->getApplication());
    }
}
