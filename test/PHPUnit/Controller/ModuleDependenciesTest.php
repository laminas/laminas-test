<?php

/**
 * @see       https://github.com/laminas/laminas-test for the canonical source repository
 * @copyright https://github.com/laminas/laminas-test/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-test/blob/master/LICENSE.md New BSD License
 */
namespace LaminasTest\Test\PHPUnit\Controller;

use Laminas\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

/**
 * @category   Laminas
 * @package    Laminas_Test
 * @subpackage UnitTests
 * @group      Laminas_Test
 */
class ModuleDependenciesTest extends AbstractHttpControllerTestCase
{
    public function testDependenciesModules()
    {
        $this->setApplicationConfig(
            include __DIR__ . '/../../_files/application.config.with.dependencies.php'
        );
        $sm = $this->getApplicationServiceLocator();
        $this->assertEquals(true, $sm->has('FooObject'));
        $this->assertEquals(true, $sm->has('BarObject'));

        $this->assertModulesLoaded(array('Foo', 'Bar'));
        $this->setExpectedException('PHPUnit_Framework_ExpectationFailedException');
        $this->assertModulesLoaded(array('Foo', 'Bar', 'Unknow'));
    }

    public function testBadDependenciesModules()
    {
        $this->setApplicationConfig(
            include __DIR__ . '/../../_files/application.config.with.dependencies.disabled.php'
        );
        $sm = $this->getApplicationServiceLocator();
        $this->assertEquals(false, $sm->has('FooObject'));
        $this->assertEquals(true, $sm->has('BarObject'));

        $this->assertNotModulesLoaded(array('Foo'));
        $this->setExpectedException('PHPUnit_Framework_ExpectationFailedException');
        $this->assertNotModulesLoaded(array('Foo', 'Bar'));
    }
}
