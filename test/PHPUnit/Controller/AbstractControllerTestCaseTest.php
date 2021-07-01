<?php

/**
 * @see       https://github.com/laminas/laminas-test for the canonical source repository
 */

namespace LaminasTest\Test\PHPUnit\Controller;

use Generator;
use Laminas\Mvc\Application;
use Laminas\Mvc\MvcEvent;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Stdlib\Exception\LogicException;
use Laminas\Stdlib\RequestInterface;
use Laminas\Stdlib\ResponseInterface;
use Laminas\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use LaminasTest\Test\ExpectedExceptionTrait;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamWrapper;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\ExpectationFailedException;
use RuntimeException;

use function array_diff;
use function array_key_exists;
use function array_merge_recursive;
use function count;
use function extension_loaded;
use function get_class;
use function glob;
use function is_dir;
use function method_exists;
use function rmdir;
use function scandir;
use function sprintf;
use function unlink;
use function urldecode;

/**
 * @group      Laminas_Test
 */
class AbstractControllerTestCaseTest extends AbstractHttpControllerTestCase
{
    use ExpectedExceptionTrait;

    /** @var bool */
    protected $traceError = true;
    /** @var bool */
    protected $traceErrorCache = true;

    /** @return void */
    public function tearDownCacheDir()
    {
        vfsStreamWrapper::register();
        $cacheDir = vfsStream::url('laminas-module-test');
        if (is_dir($cacheDir)) {
            static::rmdir($cacheDir);
        }
    }

    public static function rmdir(string $dir): bool
    {
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            is_dir("$dir/$file") ? static::rmdir("$dir/$file") : unlink("$dir/$file");
        }

        return rmdir($dir);
    }

    protected function setUp(): void
    {
        $this->traceErrorCache = $this->traceError;
        $this->tearDownCacheDir();
        $this->setApplicationConfig(
            include __DIR__ . '/../../_files/application.config.php'
        );
        parent::setUp();
    }

    protected function tearDown(): void
    {
        $this->traceError = $this->traceErrorCache;
        $this->tearDownCacheDir();
        parent::tearDown();
    }

    /** @return void */
    public function testModuleCacheIsDisabled()
    {
        $config = $this->getApplicationConfig();
        $config = $config['module_listener_options']['cache_dir'];
        $this->assertEquals(0, count(glob($config . '/*.php')));
    }

    /** @return void */
    public function testCanNotDefineApplicationConfigWhenApplicationIsBuilt()
    {
        // cosntruct app
        $this->getApplication();

        $this->expectedException(LogicException::class);
        $this->setApplicationConfig(
            include __DIR__ . '/../../_files/application.config.php'
        );
    }

    /** @return void */
    public function testApplicationClass()
    {
        $applicationClass = get_class($this->getApplication());
        $this->assertEquals($applicationClass, Application::class);
    }

    /** @return void */
    public function testApplicationServiceLocatorClass()
    {
        $smClass = get_class($this->getApplicationServiceLocator());
        $this->assertEquals($smClass, ServiceManager::class);
    }

    /** @return void */
    public function testAssertApplicationRequest()
    {
        $this->assertEquals(true, $this->getRequest() instanceof RequestInterface);
    }

    /** @return void */
    public function testAssertApplicationResponse()
    {
        $this->assertEquals(true, $this->getResponse() instanceof ResponseInterface);
    }

    /** @return void */
    public function testAssertModuleName()
    {
        $this->dispatch('/tests');

        // tests with case insensitive
        $this->assertModuleName('baz');
        $this->assertModuleName('Baz');
        $this->assertModuleName('BAz');

        $this->expectedException(
            ExpectationFailedException::class,
            'actual module name is "baz"' // check actual module is display
        );
        $this->assertModuleName('Application');
    }

    /** @return void */
    public function testAssertModuleNameWithNamespace()
    {
        $applicationConfig = $this->getApplicationConfig();

        $applicationConfig['modules'][] = 'ModuleWithNamespace\TestModule';
        $applicationConfig['module_listener_options']['module_paths']['ModuleWithNamespace\TestModule']
            = __DIR__ . '/../../_files/ModuleWithNamespace/TestModule/';

        $this->setApplicationConfig($applicationConfig);

        $this->dispatch('/namespace-test');
        $this->assertModuleName('TestModule');
    }

    /** @return void */
    public function testAssertModuleWithSimilarName()
    {
        $applicationConfig = $this->getApplicationConfig();

        $testConfig = [
            'modules'                 => [
                'ModuleWithSimilarName\TestModule',
                'ModuleWithSimilarName\Test',
            ],
            'module_listener_options' => [
                'module_paths' => [
                    'ModuleWithSimilarName\TestModule' => __DIR__ . '/../../_files/ModuleWithSimilarName/TestModule/',
                    'ModuleWithSimilarName\Test'       => __DIR__ . '/../../_files/ModuleWithSimilarName/Test/',
                ],
            ],
        ];

        $this->setApplicationConfig(array_merge_recursive($testConfig, $applicationConfig));

        $this->dispatch('/similar-name-2-test');
        $this->assertModuleName('TestModule');
    }

    /** @return void */
    public function testAssertExceptionDetailsPresentWhenTraceErrorIsEnabled()
    {
        $this->traceError = true;
        $this->dispatch('/tests');
        $this->getApplication()->getMvcEvent()->setParam(
            'exception',
            new RuntimeException('Expected exception message')
        );

        $caught = false;
        try {
            $this->assertModuleName('Application');
        } catch (ExpectationFailedException $ex) {
            $caught  = true;
            $message = $ex->getMessage();
        }

        $this->assertTrue($caught, 'Did not catch expected exception!');

        $this->assertContainsCompat('actual module name is "baz"', $message);
        $this->assertContainsCompat("Exception 'RuntimeException' with message 'Expected exception message'", $message);
        $this->assertContainsCompat(__FILE__, $message);
    }

    /** @return void */
    public function testAssertExceptionDetailsNotPresentWhenTraceErrorIsDisabled()
    {
        $this->traceError = false;
        $this->dispatch('/tests');
        $this->getApplication()->getMvcEvent()->setParam(
            'exception',
            new RuntimeException('Expected exception message')
        );

        $caught = false;
        try {
            $this->assertModuleName('Application');
        } catch (ExpectationFailedException $ex) {
            $caught  = true;
            $message = $ex->getMessage();
        }

        $this->assertTrue($caught, 'Did not catch expected exception!');

        $this->assertContainsCompat('actual module name is "baz"', $message);
        $this->assertNotContainsCompat(
            "Exception 'RuntimeException' with message 'Expected exception message'",
            $message
        );
        $this->assertNotContainsCompat(__FILE__, $message);
    }

    /** @return void */
    public function testAssertNotModuleName()
    {
        $this->dispatch('/tests');
        $this->assertNotModuleName('Application');

        $this->expectedException(ExpectationFailedException::class);
        $this->assertNotModuleName('baz');
    }

    /** @return void */
    public function testAssertControllerClass()
    {
        $this->dispatch('/tests');

        // tests with case insensitive
        $this->assertControllerClass('IndexController');
        $this->assertControllerClass('Indexcontroller');
        $this->assertControllerClass('indexcontroller');

        $this->expectedException(
            ExpectationFailedException::class,
            'actual controller class is "indexcontroller"' // check actual controller class is display
        );
        $this->assertControllerClass('Index');
    }

    /** @return void */
    public function testAssertNotControllerClass()
    {
        $this->dispatch('/tests');
        $this->assertNotControllerClass('Index');

        $this->expectedException(ExpectationFailedException::class);
        $this->assertNotControllerClass('IndexController');
    }

    /** @return void */
    public function testAssertControllerName()
    {
        $this->dispatch('/tests');

        // tests with case insensitive
        $this->assertControllerName('baz_index');
        $this->assertControllerName('Baz_index');
        $this->assertControllerName('BAz_index');

        $this->expectedException(
            ExpectationFailedException::class,
            'actual controller name is "baz_index"' // check actual controller name is display
        );
        $this->assertControllerName('baz');
    }

    /** @return void */
    public function testAssertNotControllerName()
    {
        $this->dispatch('/tests');
        $this->assertNotControllerName('baz');

        $this->expectedException(ExpectationFailedException::class);
        $this->assertNotControllerName('baz_index');
    }

    /** @return void */
    public function testAssertActionName()
    {
        $this->dispatch('/tests');

        // tests with case insensitive
        $this->assertActionName('unittests');
        $this->assertActionName('unitTests');
        $this->assertActionName('UnitTests');

        $this->expectedException(
            ExpectationFailedException::class,
            'actual action name is "unittests"' // check actual action name is display
        );
        $this->assertActionName('unit');
    }

    /** @return void */
    public function testAssertNotActionName()
    {
        $this->dispatch('/tests');
        $this->assertNotActionName('unit');

        $this->expectedException(ExpectationFailedException::class);
        $this->assertNotActionName('unittests');
    }

    /** @return void */
    public function testAssertMatchedRouteName()
    {
        $this->dispatch('/tests');

        // tests with case insensitive
        $this->assertMatchedRouteName('myroute');
        $this->assertMatchedRouteName('myRoute');
        $this->assertMatchedRouteName('MyRoute');

        $this->expectedException(
            ExpectationFailedException::class,
            'actual matched route name is "myroute"' // check actual matched route name is display
        );
        $this->assertMatchedRouteName('route');
    }

    /** @return void */
    public function testAssertNotMatchedRouteName()
    {
        $this->dispatch('/tests');
        $this->assertNotMatchedRouteName('route');

        $this->expectedException(AssertionFailedError::class);
        $this->assertNotMatchedRouteName('myroute');
    }

    /** @return void */
    public function testAssertNoMatchedRoute()
    {
        $this->dispatch('/invalid');
        $this->assertNoMatchedRoute();
    }

    /** @return void */
    public function testAssertNoMatchedRouteWithMatchedRoute()
    {
        $this->dispatch('/tests');
        $this->expectedException(AssertionFailedError::class, 'no route matched');
        $this->assertNoMatchedRoute();
    }

    /** @return void */
    public function testControllerNameWithNoRouteMatch()
    {
        $this->dispatch('/invalid');
        $this->expectedException(AssertionFailedError::class, 'No route matched');
        $this->assertControllerName('something');
    }

    /** @return void */
    public function testNotControllerNameWithNoRouteMatch()
    {
        $this->dispatch('/invalid');
        $this->expectedException(ExpectationFailedException::class, 'No route matched');
        $this->assertNotControllerName('something');
    }

    /** @return void */
    public function testActionNameWithNoRouteMatch()
    {
        $this->dispatch('/invalid');
        $this->expectedException(ExpectationFailedException::class, 'No route matched');
        $this->assertActionName('something');
    }

    /** @return void */
    public function testNotActionNameWithNoRouteMatch()
    {
        $this->dispatch('/invalid');
        $this->expectedException(ExpectationFailedException::class, 'No route matched');
        $this->assertNotActionName('something');
    }

    /** @return void */
    public function testMatchedRouteNameWithNoRouteMatch()
    {
        $this->dispatch('/invalid');
        $this->expectedException(ExpectationFailedException::class, 'No route matched');
        $this->assertMatchedRouteName('something');
    }

    /** @return void */
    public function testNotMatchedRouteNameWithNoRouteMatch()
    {
        $this->dispatch('/invalid');
        $this->expectedException(AssertionFailedError::class, 'No route matched');
        $this->assertNotMatchedRouteName('something');
    }

    /** @return void */
    public function testControllerClassWithNoRoutematch()
    {
        $this->dispatch('/invalid');
        $this->expectedException(AssertionFailedError::class, 'No route matched');
        $this->assertControllerClass('something');
    }

    /**
     * Sample tests on Application errors events
     *
     * @return void
     */
    public function testAssertApplicationErrorsEvents()
    {
        $this->url('/bad-url');
        $result = $this->triggerApplicationEvent(MvcEvent::EVENT_ROUTE);
        $this->assertEquals(true, $result->stopped());
        $this->assertEquals(Application::ERROR_ROUTER_NO_MATCH, $this->getApplication()->getMvcEvent()->getError());
    }

    /** @return void */
    public function testDispatchRequestUri()
    {
        $this->dispatch('/tests');
        $this->assertEquals('/tests', $this->getApplication()->getRequest()->getRequestUri());
    }

    /** @return void */
    public function testDefaultDispatchMethod()
    {
        $this->dispatch('/tests');
        $this->assertEquals('GET', $this->getRequest()->getMethod());
    }

    /** @return void */
    public function testDispatchMethodSetOnRequest()
    {
        $this->getRequest()->setMethod('POST');
        $this->dispatch('/tests');
        $this->assertEquals('POST', $this->getRequest()->getMethod());
    }

    /** @return void */
    public function testExplicitDispatchMethodOverrideRequestMethod()
    {
        $this->getRequest()->setMethod('POST');
        $this->dispatch('/tests', 'GET');
        $this->assertEquals('GET', $this->getRequest()->getMethod());
    }

    /** @return void */
    public function testPutRequestParams()
    {
        $this->dispatch('/tests', 'PUT', ['a' => 1]);
        $this->assertEquals('a=1', $this->getRequest()->getContent());
    }

    /** @return void */
    public function testPreserveContentOfPutRequest()
    {
        $this->getRequest()->setMethod('PUT');
        $this->getRequest()->setContent('my content');
        $this->dispatch('/tests');
        $this->assertEquals('my content', $this->getRequest()->getContent());
    }

    /**
     * @group 6399
     */
    public function testPatchRequestParams(): void
    {
        $this->dispatch('/tests', 'PATCH', ['a' => 1]);
        $this->assertEquals('a=1', $this->getRequest()->getContent());
    }

    /**
     * @group 6399
     */
    public function testPreserveContentOfPatchRequest(): void
    {
        $this->getRequest()->setMethod('PATCH');
        $this->getRequest()->setContent('my content');
        $this->dispatch('/tests');
        $this->assertEquals('my content', $this->getRequest()->getContent());
    }

    public function testExplicityPutParamsOverrideRequestContent(): void
    {
        $this->getRequest()->setContent('my content');
        $this->dispatch('/tests', 'PUT', ['a' => 1]);
        $this->assertEquals('a=1', $this->getRequest()->getContent());
    }

    /**
     * @group 6636
     * @group 6637
     */
    public function testCanHandleMultidimensionalParams(): void
    {
        $this->dispatch('/tests', 'PUT', ['a' => ['b' => 1]]);
        $this->assertEquals('a[b]=1', urldecode($this->getRequest()->getContent()));
    }

    public function testAssertTemplateName(): void
    {
        $this->dispatch('/tests');

        $this->assertTemplateName('layout/layout');
        $this->assertTemplateName('baz/index/unittests');
    }

    public function testAssertNotTemplateName(): void
    {
        $this->dispatch('/tests');

        $this->assertNotTemplateName('template/does/not/exist');
    }

    public function testCustomResponseObject(): void
    {
        $this->dispatch('/custom-response');
        $this->assertResponseStatusCode(999);
    }

    public function testResetDoesNotCreateSessionIfNoSessionExists(): void
    {
        if (! extension_loaded('session')) {
            $this->markTestSkipped('No session extension loaded');
        }

        $this->reset();

        $this->assertFalse(array_key_exists('_SESSION', $GLOBALS));
    }

    /**
     * @psalm-return Generator<string, array{0: null|string}, mixed, void>
     */
    public function method(): Generator
    {
        yield 'null' => [null];
        yield 'get' => ['GET'];
        yield 'delete' => ['DELETE'];
        yield 'post' => ['POST'];
        yield 'put' => ['PUT'];
        yield 'patch' => ['PATCH'];
    }

    /**
     * @dataProvider method
     * @param null|string $method
     */
    public function testDispatchWithNullParams($method): void
    {
        $this->dispatch('/custom-response', $method, null);
        $this->assertResponseStatusCode(999);
    }

    public function testQueryParamsDelete(): void
    {
        $this->dispatch('/tests', 'DELETE', ['foo' => 'bar']);
        $this->assertEquals('foo=bar', $this->getRequest()->getQuery()->toString());
    }

    /**
     * @return Generator
     */
    public function routeParam()
    {
        yield 'phpunit' => ['phpunit'];
        yield 'param' => ['param'];
    }

    /**
     * @dataProvider routeParam
     * @param string $param
     */
    public function testRequestWithRouteParam($param): void
    {
        $this->dispatch(sprintf('/with-param/%s', $param));
        $this->assertResponseStatusCode(200);
    }

    private function assertContainsCompat(string $needle, string $haystack): void
    {
        if (method_exists($this, 'assertStringContainsString')) {
            $this->assertStringContainsString($needle, $haystack);
        } else {
            $this->assertContains($needle, $haystack);
        }
    }

    private function assertNotContainsCompat(string $needle, string $haystack): void
    {
        if (method_exists($this, 'assertStringNotContainsString')) {
            $this->assertStringNotContainsString($needle, $haystack);
        } else {
            $this->assertNotContains($needle, $haystack);
        }
    }
}
