<?php

/**
 * @see       https://github.com/laminas/laminas-test for the canonical source repository
 * @copyright https://github.com/laminas/laminas-test/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-test/blob/master/LICENSE.md New BSD License
 */

use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

if (! class_exists(ExpectationFailedException::class)) {
    class_alias(\PHPUnit_Framework_ExpectationFailedException::class, ExpectationFailedException::class);
}

if (! class_exists(TestCase::class)) {
    class_alias(\PHPUnit_Framework_TestCase::class, TestCase::class);
}
