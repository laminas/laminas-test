<?php

/**
 * @see       https://github.com/laminas/laminas-test for the canonical source repository
 */

namespace LaminasTest\Test\PHPUnit\Controller;

use Laminas\Test\PHPUnit\Controller\AbstractControllerTestCase;

use function array_fill;
use function memory_get_usage;

class MemoryLeakTest extends AbstractControllerTestCase
{
    /** @var int|null */
    public static $memStart;

    public static function setUpBeforeClass(): void
    {
        self::$memStart = memory_get_usage(true);
    }

    public static function dataForMultipleTests(): array
    {
        return array_fill(0, 100, [null]);
    }

    /**
     * @dataProvider dataForMultipleTests
     * @param null $null
     * @return void
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
