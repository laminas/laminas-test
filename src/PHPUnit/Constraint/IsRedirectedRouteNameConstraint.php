<?php

declare(strict_types=1);

namespace Laminas\Test\PHPUnit\Constraint;

use Laminas\Http\Header\Location;
use Laminas\Mvc\Controller\AbstractController;
use Laminas\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use PHPUnit\Framework\Constraint\Constraint;

final class IsRedirectedRouteNameConstraint extends Constraint
{
    /**
     * @var AbstractHttpControllerTestCase
     */
    private $activeTestCase;

    public function __construct(AbstractHttpControllerTestCase $activeTestCase)
    {
        $this->activeTestCase = $activeTestCase;
    }

    public function toString(): string
    {
        return 'is the redirected route name';
    }

    public function matches($other): bool
    {
        if (! is_string($other)) {
            return false;
        }

        $httpResponse   = $this->activeTestCase->getResponse();
        $headerLocation = $httpResponse->getHeaders()->get('Location');

        if (! $headerLocation instanceof Location) {
            return false;
        }

        $controllerClass  = $this->getControllerFullClass();
        $urlPlugin        = $controllerClass->plugin('url');

        return $headerLocation->getFieldValue() === $urlPlugin->fromRoute($other);
    }

    private function getControllerFullClass(): AbstractController
    {
        $routeMatch           = $this->activeTestCase->getApplication()->getMvcEvent()->getRouteMatch();
        $controllerIdentifier = $routeMatch->getParam('controller');

        $controllerManager    = $this->activeTestCase->getApplicationServiceLocator()->get('ControllerManager');

        return $controllerManager->get($controllerIdentifier);
    }
}
