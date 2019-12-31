<?php

/**
 * @see       https://github.com/laminas/laminas-test for the canonical source repository
 * @copyright https://github.com/laminas/laminas-test/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-test/blob/master/LICENSE.md New BSD License
 */
namespace Laminas\Test\PHPUnit\Controller;

use PHPUnit\Framework\ExpectationFailedException;

abstract class AbstractConsoleControllerTestCase extends AbstractControllerTestCase
{
    /**
     * HTTP controller must use the console request
     * @var bool
     */
    protected $useConsoleRequest = true;

    /**
     * Assert console output contain content (insensible case)
     *
     * @param  string $match content that should be contained in matched nodes
     * @return void
     */
    public function assertConsoleOutputContains($match)
    {
        $response = $this->getResponse();
        if (false === stripos($response->getContent(), $match)) {
            throw new ExpectationFailedException($this->createFailureMessage(
                sprintf(
                    'Failed asserting output CONTAINS content "%s", actual content is "%s"',
                    $match,
                    $response->getContent()
                )
            ));
        }
        $this->assertNotSame(false, stripos($response->getContent(), $match));
    }

    /**
     * Assert console output not contain content
     *
     * @param  string $match content that should be contained in matched nodes
     * @return void
     */
    public function assertNotConsoleOutputContains($match)
    {
        $response = $this->getResponse();
        if (false !== stripos($response->getContent(), $match)) {
            throw new ExpectationFailedException($this->createFailureMessage(sprintf(
                'Failed asserting output DOES NOT CONTAIN content "%s"',
                $match
            )));
        }
        $this->assertSame(false, stripos($response->getContent(), $match));
    }
}
