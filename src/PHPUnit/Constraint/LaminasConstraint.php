<?php

declare(strict_types=1);

namespace Laminas\Test\PHPUnit\Constraint;

use Laminas\Test\PHPUnit\Controller\AbstractControllerTestCase;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\ExpectationFailedException;
use SebastianBergmann\Comparator\ComparisonFailure;

abstract class LaminasConstraint extends Constraint
{
    protected $activeTestCase;

    public function __construct(AbstractControllerTestCase $activeTestCase)
    {
        $this->activeTestCase = $activeTestCase;
    }

    final public function getTestCase(): AbstractControllerTestCase
    {
        return $this->activeTestCase;
    }

    protected function fail($other, $description, ComparisonFailure $comparisonFailure = null): void
    {
        $failureDescription = sprintf(
            'Failed asserting that %s.',
            $this->failureDescription($other)
        );

        $additionalFailureDescription = $this->additionalFailureDescription($other);

        if ($additionalFailureDescription) {
            $failureDescription .= "\n" . $additionalFailureDescription;
        }

        if (! empty($description)) {
            $failureDescription = $description . "\n" . $failureDescription;
        }

        throw new ExpectationFailedException(
            $this->createFailureMessage($failureDescription),
            $comparisonFailure
        );
    }

    final protected function getControllerFullClassName(): string
    {
        $routeMatch           = $this->activeTestCase->getApplication()->getMvcEvent()->getRouteMatch();
        $controllerIdentifier = $routeMatch->getParam('controller');

        $controllerManager    = $this->activeTestCase->getApplicationServiceLocator()->get('ControllerManager');

        return get_class($controllerManager->get($controllerIdentifier));
    }



    /**
     * Create a failure message.
     *
     * If $traceError is true, appends exception details, if any.
     */
    final private function createFailureMessage(string $message): string
    {
        if (true !== $this->activeTestCase->getTraceError()) {
            return $message;
        }

        $exception = $this->activeTestCase->getApplication()->getMvcEvent()->getParam('exception');
        if (! $exception instanceof \Throwable && ! $exception instanceof \Exception) {
            return $message;
        }

        $messages = [];
        do {
            $messages[] = sprintf(
                "Exception '%s' with message '%s' in %s:%d",
                get_class($exception),
                $exception->getMessage(),
                $exception->getFile(),
                $exception->getLine()
            );
        } while ($exception = $exception->getPrevious());

        return sprintf("%s\n\nExceptions raised:\n%s\n", $message, implode("\n\n", $messages));
    }
}
