<?php

/**
 * @see       https://github.com/laminas/laminas-test for the canonical source repository
 * @copyright https://github.com/laminas/laminas-test/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-test/blob/master/LICENSE.md New BSD License
 */
namespace LaminasTest\Test\PHPUnit\Controller;

use Laminas\Test\PHPUnit\Controller\AbstractControllerTestCase;

class MemoryLeakTest extends AbstractControllerTestCase
{
    public static $memStart;

    protected static function setUpBeforeClassCompat()
    {
        self::$memStart = memory_get_usage(true);
    }

    public static function dataForMultipleTests()
    {
        return array_fill(0, 100, [null]);
    }

    /**
     * @dataProvider dataForMultipleTests
     * @param null $null
     */
    public function testMemoryConsumptionNotGrowing($null)
    {
        $this->setApplicationConfig(
            include __DIR__ . '/../../_files/application.config.view.php'
        );
        $app = $this->getApplication();
        $app->run();

        $this->assertNull($null);

        // Test memory consumption is limited to 5 MB for 100 tests
        $this->assertLessThan(5242880, memory_get_usage(true) - self::$memStart);
    }
}
