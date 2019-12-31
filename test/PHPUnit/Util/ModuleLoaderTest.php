<?php

/**
 * @see       https://github.com/laminas/laminas-test for the canonical source repository
 * @copyright https://github.com/laminas/laminas-test/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-test/blob/master/LICENSE.md New BSD License
 */
namespace LaminasTest\Test\PHPUnit\Util;

use Laminas\ModuleManager\Exception\RuntimeException;
use Laminas\Test\Util\ModuleLoader;
use LaminasTest\Test\ExpectedExceptionTrait;
use PHPUnit\Framework\TestCase;

class ModuleLoaderTest extends TestCase
{
    use ExpectedExceptionTrait;

    public function tearDownCacheDir()
    {
        $cacheDir = sys_get_temp_dir() . '/laminas-module-test';
        if (is_dir($cacheDir)) {
            static::rmdir($cacheDir);
        }
    }

    public static function rmdir($dir)
    {
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? static::rmdir("$dir/$file") : unlink("$dir/$file");
        }

        return rmdir($dir);
    }

    public function setUp()
    {
        $this->tearDownCacheDir();
    }

    public function tearDown()
    {
        $this->tearDownCacheDir();
    }

    public function testCanLoadModule()
    {
        require_once __DIR__ . '/../../_files/Baz/Module.php';

        $loader = new ModuleLoader(['Baz']);
        $baz = $loader->getModule('Baz');
        $this->assertInstanceOf('Baz\Module', $baz);
    }

    public function testCanNotLoadModule()
    {
        $this->expectedException(RuntimeException::class, 'could not be initialized');
        $loader = new ModuleLoader(['FooBaz']);
    }

    public function testCanLoadModuleWithPath()
    {
        $loader = new ModuleLoader(['Baz' => __DIR__ . '/../../_files/Baz']);
        $baz = $loader->getModule('Baz');
        $this->assertInstanceOf('Baz\Module', $baz);
    }

    public function testCanLoadModules()
    {
        require_once __DIR__ . '/../../_files/Baz/Module.php';
        require_once __DIR__ . '/../../_files/modules-path/with-subdir/Foo/Module.php';

        $loader = new ModuleLoader(['Baz', 'Foo']);
        $baz = $loader->getModule('Baz');
        $this->assertInstanceOf('Baz\Module', $baz);
        $foo = $loader->getModule('Foo');
        $this->assertInstanceOf('Foo\Module', $foo);
    }

    public function testCanLoadModulesWithPath()
    {
        $loader = new ModuleLoader([
            'Baz' => __DIR__ . '/../../_files/Baz',
            'Foo' => __DIR__ . '/../../_files/modules-path/with-subdir/Foo',
        ]);

        $fooObject = $loader->getServiceManager()->get('FooObject');
        $this->assertInstanceOf('stdClass', $fooObject);
    }

    public function testCanLoadModulesFromConfig()
    {
        $config = include __DIR__ . '/../../_files/application.config.php';
        $loader = new ModuleLoader($config);
        $baz = $loader->getModule('Baz');
        $this->assertInstanceOf('Baz\Module', $baz);
    }

    public function testCanGetService()
    {
        $loader = new ModuleLoader(['Baz' => __DIR__ . '/../../_files/Baz']);

        $this->assertInstanceOf(
            'Laminas\ServiceManager\ServiceLocatorInterface',
            $loader->getServiceManager()
        );
        $this->assertInstanceOf(
            'Laminas\ModuleManager\ModuleManager',
            $loader->getModuleManager()
        );
        $this->assertInstanceOf(
            'Laminas\Mvc\ApplicationInterface',
            $loader->getApplication()
        );
    }
}
