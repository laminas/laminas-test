<?php

declare(strict_types=1);

namespace Laminas\Test\PHPUnit\Controller;

use Exception;
use Laminas\EventManager\ResponseCollection;
use Laminas\EventManager\StaticEventManager;
use Laminas\Http\Request as HttpRequest;
use Laminas\Http\Response;
use Laminas\Mvc\Application;
use Laminas\Mvc\ApplicationInterface;
use Laminas\Mvc\Controller\ControllerManager;
use Laminas\Mvc\MvcEvent;
use Laminas\Router\RouteMatch;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\Stdlib\Exception\LogicException;
use Laminas\Stdlib\Parameters;
use Laminas\Stdlib\RequestInterface;
use Laminas\Stdlib\ResponseInterface;
use Laminas\Test\PHPUnit\Constraint\IsCurrentModuleNameConstraint;
use Laminas\Uri\Http as HttpUri;
use Laminas\View\Model\ModelInterface;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Throwable;

use function array_diff;
use function array_intersect;
use function array_key_exists;
use function array_merge;
use function assert;
use function class_exists;
use function count;
use function get_class;
use function http_build_query;
use function implode;
use function method_exists;
use function parse_str;
use function sprintf;
use function strrpos;
use function strtolower;
use function substr;
use function trigger_error;

use const E_USER_NOTICE;

abstract class AbstractControllerTestCase extends TestCase
{
    /** @var ApplicationInterface */
    protected $application;

    protected array $applicationConfig;

    protected bool $traceError = true;

    /**
     * Reset the application for isolation
     */
    protected function setUp(): void
    {
        $this->reset();
    }

    /**
     * Restore params
     */
    protected function tearDown(): void
    {
        // Prevent memory leak
        $this->reset();
    }

    /**
     * Create a failure message.
     *
     * If $traceError is true, appends exception details, if any.
     *
     * @deprecated (use LaminasContraint instead)
     */
    protected function createFailureMessage(string $message): string
    {
        if (! $this->traceError) {
            return $message;
        }

        $exception = $this->getApplication()->getMvcEvent()->getParam('exception');
        if (! $exception instanceof Throwable && ! $exception instanceof Exception) {
            return $message;
        }

        $messages = [];
        do {
            $messages[] = sprintf(
                "Exception '%s' with message '%s' in %s:%d",
                $exception::class,
                $exception->getMessage(),
                $exception->getFile(),
                $exception->getLine()
            );
        } while ($exception = $exception->getPrevious());

        return sprintf("%s\n\nExceptions raised:\n%s\n", $message, implode("\n\n", $messages));
    }

    /**
     * Get the trace error flag
     */
    public function getTraceError(): bool
    {
        return $this->traceError;
    }

    /**
     * Set the trace error flag
     */
    public function setTraceError(bool $traceError): AbstractControllerTestCase
    {
        $this->traceError = $traceError;

        return $this;
    }

    /**
     * Get the application config
     */
    public function getApplicationConfig(): array
    {
        return $this->applicationConfig;
    }

    /**
     * Set the application config
     *
     * @throws LogicException
     */
    public function setApplicationConfig(array $applicationConfig): AbstractControllerTestCase
    {
        if (null !== $this->application && null !== $this->applicationConfig) {
            throw new LogicException(
                'Application config can not be set, the application is already built'
            );
        }

        // do not cache module config on testing environment
        if (isset($applicationConfig['module_listener_options']['config_cache_enabled'])) {
            $applicationConfig['module_listener_options']['config_cache_enabled'] = false;
        }
        $this->applicationConfig = $applicationConfig;

        return $this;
    }

    /**
     * Get the application object
     */
    public function getApplication(): ApplicationInterface
    {
        if ($this->application) {
            return $this->application;
        }
        $appConfig         = $this->applicationConfig;
        $this->application = Application::init($appConfig);

        $events = $this->application->getEventManager();
        $this->application->getServiceManager()->get('SendResponseListener')->detach($events);

        return $this->application;
    }

    /**
     * Get the service manager of the application object
     */
    public function getApplicationServiceLocator(): ServiceLocatorInterface
    {
        return $this->getApplication()->getServiceManager();
    }

    /**
     * Get the application request object
     */
    public function getRequest(): RequestInterface
    {
        return $this->getApplication()->getRequest();
    }

    /**
     * Get the application response object
     */
    public function getResponse(): Response
    {
        $response = $this->getApplication()->getMvcEvent()->getResponse();

        assert($response instanceof Response);

        return $response;
    }

    /**
     * Set the request URL
     */
    public function url(string $url, ? string $method = HttpRequest::METHOD_GET, ? array $params = []): AbstractControllerTestCase
    {
        $request     = $this->getRequest();
        $query       = $request->getQuery()->toArray();
        $post        = $request->getPost()->toArray();
        $uri         = new HttpUri($url);
        $queryString = $uri->getQuery();

        if ($queryString) {
            parse_str($queryString, $query);
        }

        if ($params) {
            switch ($method) {
                case HttpRequest::METHOD_POST:
                    $post = $params;
                    break;
                case HttpRequest::METHOD_GET:
                case HttpRequest::METHOD_DELETE:
                    $query = array_merge($query, $params);
                    break;
                case HttpRequest::METHOD_PUT:
                case HttpRequest::METHOD_PATCH:
                    $content = http_build_query($params);
                    $request->setContent($content);
                    break;
                default:
                    trigger_error(
                        'Additional params is only supported by GET, POST, PUT and PATCH HTTP method',
                        E_USER_NOTICE
                    );
            }
        }

        $request->setMethod($method);
        $request->setQuery(new Parameters($query));
        $request->setPost(new Parameters($post));
        $request->setUri($uri);
        $request->setRequestUri($uri->getPath());

        return $this;
    }

    /**
     * Dispatch the MVC with a URL
     * Accept a HTTP (simulate a customer action)
     *
     * The URL provided set the request URI in the request object.
     *
     * @throws Exception
     */
    public function dispatch(string $url, ? string $method = null, ? array $params = [], bool $isXmlHttpRequest = false): void
    {
        if (
            ! $method
            && $this->getRequest() instanceof HttpRequest
        ) {
            $method = $this->getRequest()->getMethod();
        } elseif (! $method) {
            $method = HttpRequest::METHOD_GET;
        }

        if ($isXmlHttpRequest) {
            $headers = $this->getRequest()->getHeaders();
            $headers->addHeaderLine('X_REQUESTED_WITH', 'XMLHttpRequest');
        }

        $this->url($url, $method, $params);
        $this->getApplication()->run();
    }

    /**
     * Reset the request
     */
    public function reset(bool $keepPersistence = false): AbstractControllerTestCase
    {
        // force to re-create all components
        $this->application = null;

        // reset server data
        if (! $keepPersistence) {
            // Do not create a global session variable if it doesn't already
            // exist. Otherwise calling this function could mark tests risky,
            // as it changes global state.
            if (array_key_exists('_SESSION', $GLOBALS)) {
                $_SESSION = [];
            }
            $_COOKIE = [];
        }

        $_GET  = [];
        $_POST = [];

        // reset singleton
        if (class_exists(StaticEventManager::class)) {
            StaticEventManager::resetInstance();
        }

        return $this;
    }

    /**
     * Trigger an application event
     */
    public function triggerApplicationEvent(string $eventName): ResponseCollection
    {
        $events = $this->getApplication()->getEventManager();
        $event  = $this->getApplication()->getMvcEvent();

        if ($eventName !== MvcEvent::EVENT_ROUTE && $eventName !== MvcEvent::EVENT_DISPATCH) {
            return $events->trigger($eventName, $event);
        }

        $shortCircuit = static function ($r) use ($event): bool {
            if ($r instanceof ResponseInterface) {
                return true;
            }

            if ($event->getError()) {
                return true;
            }

            return false;
        };

        $event->setName($eventName);
        return $events->triggerEventUntil($shortCircuit, $event);
    }

    /**
     * Assert modules were loaded with the module manager
     *
     * @throws ContainerExceptionInterface
     *
     * @throws ExpectationFailedException
     */
    public function assertModulesLoaded(array $modules): void
    {
        $moduleManager = $this->getApplicationServiceLocator()->get('ModuleManager');
        $modulesLoaded = $moduleManager->getModules();
        $list          = array_diff($modules, $modulesLoaded);
        if ($list) {
            throw new ExpectationFailedException($this->createFailureMessage(
                sprintf('Several modules are not loaded "%s"', implode(', ', $list))
            ));
        }
        $this->assertEquals(count($list), 0);
    }

    /**
     * Assert modules were not loaded with the module manager
     * @throws ContainerExceptionInterface
     *
     * @throws NotFoundExceptionInterface
     */
    public function assertNotModulesLoaded(array $modules): void
    {
        $moduleManager = $this->getApplicationServiceLocator()->get('ModuleManager');
        $modulesLoaded = $moduleManager->getModules();
        $list          = array_intersect($modules, $modulesLoaded);
        if ($list) {
            throw new ExpectationFailedException($this->createFailureMessage(
                sprintf('Several modules WAS not loaded "%s"', implode(', ', $list))
            ));
        }
        $this->assertEquals(count($list), 0);
    }

    /**
     * Retrieve the response status code
     */
    protected function getResponseStatusCode(): int
    {
        return $this->getResponse()->getStatusCode();
    }

    /**
     * Assert response status code
     *
     * @throws ExpectationFailedException
     */
    public function assertResponseStatusCode(int $code): void
    {
        $match = $this->getResponseStatusCode();
        if ($code !== $match) {
            throw new ExpectationFailedException($this->createFailureMessage(
                sprintf('Failed asserting response code "%s", actual status code is "%s"', $code, $match)
            ));
        }
        $this->assertEquals($code, $match);
    }

    /**
     * Assert not response status code
     *
     * @throws ExpectationFailedException
     */
    public function assertNotResponseStatusCode(int $code): void
    {
        $match = $this->getResponseStatusCode();
        if ($code === $match) {
            throw new ExpectationFailedException($this->createFailureMessage(
                sprintf('Failed asserting response code was NOT "%s"', $code)
            ));
        }
        $this->assertNotEquals($code, $match);
    }

    /**
     * Assert the application exception and message
     *
     * @param string $type application exception type
     * @param string|null $message application exception message
     * @psalm-return never
     */
    public function assertApplicationException(string $type, ? string $message = null)
    {
        $exception = $this->getApplication()->getMvcEvent()->getParam('exception');
        if (! $exception) {
            throw new ExpectationFailedException($this->createFailureMessage(
                'Failed asserting application exception, param "exception" does not exist'
            ));
        }
        if (true === $this->traceError) {
            // set exception as null because we know and have assert the exception
            $this->getApplication()->getMvcEvent()->setParam('exception', null);
        }

        if (! method_exists($this, 'expectException')) {
            // For old PHPUnit 4
            $this->setExpectedException($type, $message);
        } else {
            $this->expectException($type);
            if (! empty($message)) {
                $this->expectExceptionMessage($message);
            }
        }

        throw $exception;
    }

    /**
     * Get the full current controller class name
     */
    protected function getControllerFullClassName(): string
    {
        return get_class($this->getControllerFullClass());
    }

    /**
     * Get the current controller class
     */
    protected function getControllerFullClass(): object
    {
        $routeMatch = $this->getApplication()->getMvcEvent()->getRouteMatch();
        if (! $routeMatch) {
            Assert::fail('No route matched');
        }

        $controllerIdentifier = $routeMatch->getParam('controller');
        Assert::assertIsString($controllerIdentifier, 'No string controller identifier discovered in route match');

        $controllerManager = $this->getApplicationServiceLocator()->get('ControllerManager');
        if (! $controllerManager instanceof ControllerManager) {
            Assert::fail('Invalid ControllerManager instance in ServiceManager');
        }

        $controller = $controllerManager->get($controllerIdentifier);
        Assert::assertIsObject(
            $controller,
            sprintf('Did not receive an object back for the controller %s', $controllerIdentifier)
        );

        return $controller;
    }

    /**
     * Assert that the application route match used the given module
     */
    public function assertModuleName(string $module): void
    {
        self::assertThat($module, new IsCurrentModuleNameConstraint($this));
    }

    /**
     * Assert that the application route match used NOT the given module
     */
    public function assertNotModuleName(string $module): void
    {
        self::assertThat(
            $module,
            self::logicalNot(new IsCurrentModuleNameConstraint($this))
        );
    }

    /**
     * Assert that the application route match used the given controller class
     *
     * @throws ExpectationFailedException
     */
    public function assertControllerClass(string $controller): void
    {
        $controllerClass = $this->getControllerFullClassName();
        $match           = substr($controllerClass, strrpos($controllerClass, '\\') + 1);
        $match           = strtolower($match);
        $controller      = strtolower($controller);
        if ($controller !== $match) {
            throw new ExpectationFailedException($this->createFailureMessage(
                sprintf('Failed asserting controller class "%s", actual controller class is "%s"', $controller, $match)
            ));
        }
        $this->assertEquals($controller, $match);
    }

    /**
     * Assert that the application route match used NOT the given controller class
     *
     * @throws ExpectationFailedException
     */
    public function assertNotControllerClass(string $controller): void
    {
        $controllerClass = $this->getControllerFullClassName();
        $match           = substr($controllerClass, strrpos($controllerClass, '\\') + 1);
        $match           = strtolower($match);
        $controller      = strtolower($controller);
        if ($controller === $match) {
            throw new ExpectationFailedException($this->createFailureMessage(
                sprintf('Failed asserting controller class was NOT "%s"', $controller)
            ));
        }
        $this->assertNotEquals($controller, $match);
    }

    /**
     * Assert that the application route match used the given controller name
     *
     * @throws ExpectationFailedException
     */
    public function assertControllerName(string $controller): void
    {
        $routeMatch = $this->getApplication()->getMvcEvent()->getRouteMatch();
        if (! $routeMatch) {
            throw new ExpectationFailedException($this->createFailureMessage('No route matched'));
        }
        $match      = $routeMatch->getParam('controller');
        $match      = strtolower($match);
        $controller = strtolower($controller);
        if ($controller !== $match) {
            throw new ExpectationFailedException($this->createFailureMessage(
                sprintf('Failed asserting controller name "%s", actual controller name is "%s"', $controller, $match)
            ));
        }
        $this->assertEquals($controller, $match);
    }

    /**
     * Assert that the application route match used NOT the given controller name
     *
     * @throws ExpectationFailedException
     */
    public function assertNotControllerName(string $controller): void
    {
        $routeMatch = $this->getApplication()->getMvcEvent()->getRouteMatch();
        if (! $routeMatch) {
            throw new ExpectationFailedException($this->createFailureMessage('No route matched'));
        }
        $match      = $routeMatch->getParam('controller');
        $match      = strtolower($match);
        $controller = strtolower($controller);
        if ($controller === $match) {
            throw new ExpectationFailedException($this->createFailureMessage(
                sprintf('Failed asserting controller name was NOT "%s"', $controller)
            ));
        }
        $this->assertNotEquals($controller, $match);
    }

    /**
     * Assert that the application route match used the given action
     *
     * @throws ExpectationFailedException
     */
    public function assertActionName(string $action): void
    {
        $routeMatch = $this->getApplication()->getMvcEvent()->getRouteMatch();
        if (! $routeMatch) {
            throw new ExpectationFailedException($this->createFailureMessage('No route matched'));
        }
        $match  = $routeMatch->getParam('action');
        $match  = strtolower($match);
        $action = strtolower($action);
        if ($action !== $match) {
            throw new ExpectationFailedException($this->createFailureMessage(
                sprintf('Failed asserting action name "%s", actual action name is "%s"', $action, $match)
            ));
        }
        $this->assertEquals($action, $match);
    }

    /**
     * Assert that the application route match used NOT the given action
     *
     * @throws ExpectationFailedException
     */
    public function assertNotActionName(string $action): void
    {
        $routeMatch = $this->getApplication()->getMvcEvent()->getRouteMatch();
        if (! $routeMatch) {
            throw new ExpectationFailedException($this->createFailureMessage('No route matched'));
        }
        $match  = $routeMatch->getParam('action');
        $match  = strtolower($match);
        $action = strtolower($action);
        if ($action === $match) {
            throw new ExpectationFailedException($this->createFailureMessage(
                sprintf('Failed asserting action name was NOT "%s"', $action)
            ));
        }
        $this->assertNotEquals($action, $match);
    }

    /**
     * Assert that the application route match used the given route name
     *
     * @throws ExpectationFailedException
     */
    public function assertMatchedRouteName(string $route): void
    {
        $routeMatch = $this->getApplication()->getMvcEvent()->getRouteMatch();
        if (! $routeMatch) {
            throw new ExpectationFailedException($this->createFailureMessage('No route matched'));
        }
        $match = $routeMatch->getMatchedRouteName();
        $match = strtolower($match);
        $route = strtolower($route);
        if ($route !== $match) {
            throw new ExpectationFailedException($this->createFailureMessage(
                sprintf(
                    'Failed asserting matched route name was "%s", actual matched route name is "%s"',
                    $route,
                    $match
                )
            ));
        }
        $this->assertEquals($route, $match);
    }

    /**
     * Assert that the application route match used NOT the given route name
     */
    public function assertNotMatchedRouteName(string $route): void
    {
        $application = $this->getApplication();
        if (! $application instanceof Application) {
            Assert::fail(sprintf(
                'Unexpected Application instance composed in test case; must be of type %s',
                Application::class
            ));
        }

        $routeMatch = $application->getMvcEvent()->getRouteMatch();
        if (! $routeMatch) {
            Assert::fail('No route matched');
        }

        $match = $routeMatch->getMatchedRouteName();
        $match = strtolower($match);
        $route = strtolower($route);
        if ($route === $match) {
            Assert::fail(sprintf('Failed asserting route matched was NOT "%s"', $route));
        }

        $this->assertNotEquals($route, $match);
    }

    /**
     * Assert that the application did not match any route
     */
    public function assertNoMatchedRoute(): void
    {
        $application = $this->getApplication();
        if (! $application instanceof Application) {
            Assert::fail(sprintf(
                'Unexpected Application instance composed in test case; must be of type %s',
                Application::class
            ));
        }

        $routeMatch = $application->getMvcEvent()->getRouteMatch();

        if (! $routeMatch instanceof RouteMatch) {
            Assert::assertTrue(true);
            return;
        }

        $match = $routeMatch->getMatchedRouteName();
        $match = strtolower($match);
        Assert::fail(sprintf(
            'Failed asserting that no route matched, actual matched route name is "%s"',
            $match
        ));
    }

    /**
     * Assert template name
     * Assert that a template was used somewhere in the view model tree
     */
    public function assertTemplateName(string $templateName): void
    {
        $application = $this->getApplication();
        if (! $application instanceof Application) {
            $this->fail(sprintf(
                'Unexpected Application instance composed in test case; must be of type %s',
                Application::class
            ));
        }

        $viewModel = $application->getMvcEvent()->getViewModel();
        $this->assertTrue($this->searchTemplates($viewModel, $templateName));
    }

    /**
     * Assert not template name
     * Assert that a template was not used somewhere in the view model tree
     */
    public function assertNotTemplateName(string $templateName): void
    {
        $viewModel = $this->getApplication()->getMvcEvent()->getViewModel();
        $this->assertFalse($this->searchTemplates($viewModel, $templateName));
    }

    /**
     * Recursively search a view model and it's children for the given templateName
     */
    protected function searchTemplates(ModelInterface $viewModel, string $templateName): bool
    {
        if ($viewModel->getTemplate($templateName) === $templateName) {
            return true;
        }
        foreach ($viewModel->getChildren() as $child) {
            return $this->searchTemplates($child, $templateName);
        }

        return false;
    }
}
