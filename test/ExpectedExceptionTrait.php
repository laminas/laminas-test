<?php

declare(strict_types=1);

namespace LaminasTest\Test;

use Throwable;

trait ExpectedExceptionTrait
{
    /**
     * @param class-string<Throwable> $exceptionClass Expected exception class
     * @param string $message String expected within exception message, if any
     * @return void
     */
    public function expectedException($exceptionClass, $message = '')
    {
        $this->expectException($exceptionClass);

        if (! empty($message)) {
            $this->expectExceptionMessage($message);
        }
    }
}
