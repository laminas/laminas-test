<?php

/**
 * @see       https://github.com/laminas/laminas-test for the canonical source repository
 * @copyright https://github.com/laminas/laminas-test/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-test/blob/master/LICENSE.md New BSD License
 */

use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\Version;

if (! class_exists(ExpectationFailedException::class)) {
    class_alias(\PHPUnit_Framework_ExpectationFailedException::class, ExpectationFailedException::class);
}

if (! class_exists(TestCase::class)) {
    class_alias(\PHPUnit_Framework_TestCase::class, TestCase::class);
}

// Compatibility with PHPUnit 8.0
// We need to use "magic" trait \Laminas\Test\PHPUnit\TestCaseTrait
// and instead of setUp/tearDown method in test case
// we should have setUpCompat/tearDownCompat.
if (class_exists(Version::class)
    && version_compare(Version::id(), '8.0.0') >= 0
) {
    class_alias(\Laminas\Test\PHPUnit\TestCaseTypeHintTrait::class, \Laminas\Test\PHPUnit\TestCaseTrait::class);
} else {
    class_alias(\Laminas\Test\PHPUnit\TestCaseNoTypeHintTrait::class, \Laminas\Test\PHPUnit\TestCaseTrait::class);
}
