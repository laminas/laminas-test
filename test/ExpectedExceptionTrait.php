<?php

namespace LaminasTest\Test;

trait ExpectedExceptionTrait
{
    /**
     * @param string $exceptionClass Expected exception class
     * @param string $message String expected within exception message, if any
     * @return void
     */
    public function expectedException($exceptionClass, $message = '')
    {
        if (! method_exists($this, 'expectException')) {
            // For old PHPUnit 4
            $this->setExpectedException($exceptionClass, $message);
            return;
        }

        $this->expectException($exceptionClass);

        if (! empty($message)) {
            $this->expectExceptionMessage($message);
        }
    }
}
