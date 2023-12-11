<?php

declare(strict_types=1);

namespace Laminas\Test\PHPUnit\Constraint;

use Laminas\Http\Header\Location;
use Laminas\Http\PhpEnvironment\Response;
use Laminas\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use PHPUnit\Framework\Constraint\Constraint;

final class HasRedirectConstraint extends Constraint
{
    public function __construct(private readonly AbstractHttpControllerTestCase $activeTestCase)
    {
    }

    public function toString(): string
    {
        return 'has a redirect';
    }

    /** @param mixed $other */
    public function matches($other): bool
    {
        $response = $this->activeTestCase->getResponse();

        if (! $response instanceof Response) {
            return false;
        }

        return $response->getHeaders()->get('Location') instanceof Location;
    }
}
