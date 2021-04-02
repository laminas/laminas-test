<?php

declare(strict_types=1);

namespace Laminas\Test\PHPUnit\Constraint;

use Laminas\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use PHPUnit\Framework\Constraint\Constraint;
use Laminas\Http\PhpEnvironment\Response;
use Laminas\Http\Header\Location;

final class HasRedirectConstraint extends Constraint
{
    private $activeTestCase;

    public function __construct(AbstractHttpControllerTestCase $activeTestCase)
    {
        $this->activeTestCase = $activeTestCase;
    }

    public function toString(): string
    {
        return 'has a redirect';
    }

    public function matches($other): bool
    {
        if (! $this->activeTestCase->getResponse() instanceof Response) {
            return false;
        }

        return ($this->activeTestCase->getResponse()->getHeaders()->get('Location') instanceof Location);
    }
}
