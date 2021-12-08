<?php

declare(strict_types=1);

namespace Laminas\Test\PHPUnit\Controller;

use PHPUnit\Framework\ExpectationFailedException;

use function sprintf;
use function stripos;

abstract class AbstractConsoleControllerTestCase extends AbstractControllerTestCase
{
    /**
     * HTTP controller must use the console request
     *
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
