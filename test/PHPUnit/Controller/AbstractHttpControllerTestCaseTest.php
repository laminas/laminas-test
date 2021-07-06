<?php

/**
 * @see       https://github.com/laminas/laminas-test for the canonical source repository
 */

namespace LaminasTest\Test\PHPUnit\Controller;

use Exception;
use Laminas\Mvc\MvcEvent;
use Laminas\Router\Http\RouteMatch;
use Laminas\Stdlib\Parameters;
use Laminas\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Laminas\View\Model\ViewModel;
use LaminasTest\Test\ExpectedExceptionTrait;
use PHPUnit\Framework\ExpectationFailedException;

use function current;
use function extension_loaded;

/**
 * @group      Laminas_Test
 */
class AbstractHttpControllerTestCaseTest extends AbstractHttpControllerTestCase
{
    use ExpectedExceptionTrait;

    protected function setUp(): void
    {
        $this->setApplicationConfig(
            include __DIR__ . '/../../_files/application.config.php'
        );
        parent::setUp();
    }

    public function testAssertResponseStatusCode(): void
    {
        $this->dispatch('/tests');
        $this->assertResponseStatusCode(200);

        $this->expectedException(
            ExpectationFailedException::class,
            'actual status code is "200"' // check actual code is display
        );
        $this->assertResponseStatusCode(302);
    }

    public function testAssertNotResponseStatusCode(): void
    {
        $this->dispatch('/tests');
        $this->assertNotResponseStatusCode(302);

        $this->expectedException(ExpectationFailedException::class);
        $this->assertNotResponseStatusCode(200);
    }

    public function testAssertHasResponseHeader(): void
    {
        $this->dispatch('/tests');
        $this->assertHasResponseHeader('Content-Type');

        $this->expectedException(ExpectationFailedException::class);
        $this->assertHasResponseHeader('Unknow-header');
    }

    public function testAssertNotHasResponseHeader(): void
    {
        $this->dispatch('/tests');
        $this->assertNotHasResponseHeader('Unknow-header');

        $this->expectedException(ExpectationFailedException::class);
        $this->assertNotHasResponseHeader('Content-Type');
    }

    public function testAssertResponseHeaderContains(): void
    {
        $this->dispatch('/tests');
        $this->assertResponseHeaderContains('Content-Type', 'text/html');

        $this->expectedException(
            ExpectationFailedException::class,
            'actual content is "text/html"' // check actual content is display
        );
        $this->assertResponseHeaderContains('Content-Type', 'text/json');
    }

    public function testAssertResponseHeaderContainsMultipleHeaderInterface(): void
    {
        $this->dispatch('/tests');
        $this->assertResponseHeaderContains('WWW-Authenticate', 'Basic realm="Laminas"');
    }

    public function testAssertNotResponseHeaderContains(): void
    {
        $this->dispatch('/tests');
        $this->assertNotResponseHeaderContains('Content-Type', 'text/json');

        $this->expectedException(ExpectationFailedException::class);
        $this->assertNotResponseHeaderContains('Content-Type', 'text/html');
    }

    public function testAssertNotResponseHeaderContainsMultipleHeaderInterface(): void
    {
        $this->dispatch('/tests');
        $this->assertNotResponseHeaderContains('WWW-Authenticate', 'Basic realm="LaminasProject"');
    }

    public function testAssertResponseHeaderRegex(): void
    {
        $this->dispatch('/tests');
        $this->assertResponseHeaderRegex('Content-Type', '#html$#');

        $this->expectedException(
            ExpectationFailedException::class,
            'actual content is "text/html"' // check actual content is display
        );
        $this->assertResponseHeaderRegex('Content-Type', '#json#');
    }

    public function testAssertResponseHeaderRegexMultipleHeaderInterface(): void
    {
        $this->dispatch('/tests');
        $this->assertResponseHeaderRegex('WWW-Authenticate', '#"Laminas"$#');
    }

    public function testAssertNotResponseHeaderRegex(): void
    {
        $this->dispatch('/tests');
        $this->assertNotResponseHeaderRegex('Content-Type', '#json#');

        $this->expectedException(ExpectationFailedException::class);
        $this->assertNotResponseHeaderRegex('Content-Type', '#html$#');
    }

    public function testAssertNotResponseHeaderRegexMultipleHeaderInterface(): void
    {
        $this->dispatch('/tests');
        $this->assertNotResponseHeaderRegex('WWW-Authenticate', '#"LaminasProject"$#');
    }

    public function testAssertRedirect(): void
    {
        $this->dispatch('/redirect');
        $this->assertRedirect();

        $this->expectedException(
            ExpectationFailedException::class,
            'actual redirection is "https://www.zend.com"' // check actual redirection is display
        );
        $this->assertNotRedirect();
    }

    public function testAssertNotRedirect(): void
    {
        $this->dispatch('/test');
        $this->assertNotRedirect();

        $this->expectedException(ExpectationFailedException::class);
        $this->assertRedirect();
    }

    public function testAssertRedirectTo(): void
    {
        $this->dispatch('/redirect');
        $this->assertRedirectTo('https://www.zend.com');

        $this->expectedException(
            ExpectationFailedException::class,
            'actual redirection is "https://www.zend.com"' // check actual redirection is display
        );
        $this->assertRedirectTo('http://www.laminas.fr');
    }

    public function testAssertNotRedirectTo(): void
    {
        $this->dispatch('/redirect');
        $this->assertNotRedirectTo('http://www.laminas.fr');

        $this->expectedException(ExpectationFailedException::class);
        $this->assertNotRedirectTo('https://www.zend.com');
    }

    public function testAssertRedirectToRoute(): void
    {
        $this->dispatch('/redirect-to-route');
        $this->assertRedirectToRoute('myroute');

        $this->expectedException(ExpectationFailedException::class);
        $this->assertRedirectToRoute('exception');
    }

    public function testAssertNotRedirectToRoute(): void
    {
        $this->dispatch('/redirect-to-route');
        $this->assertNotRedirectToRoute('exception');

        $this->expectedException(ExpectationFailedException::class);
        $this->assertNotRedirectToRoute('myroute');
    }

    public function testAssertRedirectRegex(): void
    {
        $this->dispatch('/redirect');
        $this->assertRedirectRegex('#zend\.com$#');

        $this->expectedException(
            ExpectationFailedException::class,
            'actual redirection is "https://www.zend.com"' // check actual redirection is display
        );
        $this->assertRedirectRegex('#laminas\.fr$#');
    }

    public function testAssertNotRedirectRegex(): void
    {
        $this->dispatch('/redirect');
        $this->assertNotRedirectRegex('#laminas\.fr#');

        $this->expectedException(ExpectationFailedException::class);
        $this->assertNotRedirectRegex('#zend\.com$#');
    }

    public function testAssertQuery(): void
    {
        $this->dispatch('/tests');
        $this->assertQuery('form#myform');

        $this->expectedException(ExpectationFailedException::class);
        $this->assertQuery('form#id');
    }

    public function testAssertXpathQuery(): void
    {
        $this->dispatch('/tests');
        $this->assertXpathQuery('//form[@id="myform"]');

        $this->expectedException(ExpectationFailedException::class);
        $this->assertXpathQuery('//form[@id="id"]');
    }

    public function testAssertXpathQueryWithBadXpathUsage(): void
    {
        $this->dispatch('/tests');

        $this->expectedException('ErrorException');
        $this->assertXpathQuery('form#myform');
    }

    public function testAssertNotQuery(): void
    {
        $this->dispatch('/tests');
        $this->assertNotQuery('form#id');

        $this->expectedException(ExpectationFailedException::class);
        $this->assertNotQuery('form#myform');
    }

    public function testAssertNotXpathQuery(): void
    {
        $this->dispatch('/tests');
        $this->assertNotXpathQuery('//form[@id="id"]');

        $this->expectedException(ExpectationFailedException::class);
        $this->assertNotXpathQuery('//form[@id="myform"]');
    }

    public function testAssertQueryCount(): void
    {
        $this->dispatch('/tests');
        $this->assertQueryCount('div.top', 3);

        $this->expectedException(
            ExpectationFailedException::class,
            'actually occurs 3 times' // check actual occurs is display
        );
        $this->assertQueryCount('div.top', 2);
    }

    public function testAssertXpathQueryCount(): void
    {
        $this->dispatch('/tests');
        $this->assertXpathQueryCount('//div[@class="top"]', 3);

        $this->expectedException(
            ExpectationFailedException::class,
            'actually occurs 3 times' // check actual occurs is display
        );
        $this->assertXpathQueryCount('//div[@class="top"]', 2);
    }

    public function testAssertXpathQueryCountWithBadXpathUsage(): void
    {
        $this->dispatch('/tests');
        $this->assertXpathQueryCount('div.top', 0);
    }

    public function testAssertNotQueryCount(): void
    {
        $this->dispatch('/tests');
        $this->assertNotQueryCount('div.top', 1);
        $this->assertNotQueryCount('div.top', 2);

        $this->expectedException(ExpectationFailedException::class);
        $this->assertNotQueryCount('div.top', 3);
    }

    public function testAssertNotXpathQueryCount(): void
    {
        $this->dispatch('/tests');
        $this->assertNotXpathQueryCount('//div[@class="top"]', 1);
        $this->assertNotXpathQueryCount('//div[@class="top"]', 2);

        $this->expectedException(ExpectationFailedException::class);
        $this->assertNotXpathQueryCount('//div[@class="top"]', 3);
    }

    public function testAssertQueryCountMin(): void
    {
        $this->dispatch('/tests');
        $this->assertQueryCountMin('div.top', 1);
        $this->assertQueryCountMin('div.top', 2);
        $this->assertQueryCountMin('div.top', 3);

        $this->expectedException(
            ExpectationFailedException::class,
            'actually occurs 3 times' // check actual occurs is display
        );
        $this->assertQueryCountMin('div.top', 4);
    }

    public function testAssertXpathQueryCountMin(): void
    {
        $this->dispatch('/tests');
        $this->assertXpathQueryCountMin('//div[@class="top"]', 1);
        $this->assertXpathQueryCountMin('//div[@class="top"]', 2);
        $this->assertXpathQueryCountMin('//div[@class="top"]', 3);

        $this->expectedException(
            ExpectationFailedException::class,
            'actually occurs 3 times' // check actual occurs is display
        );
        $this->assertXpathQueryCountMin('//div[@class="top"]', 4);
    }

    public function testAssertQueryCountMax(): void
    {
        $this->dispatch('/tests');
        $this->assertQueryCountMax('div.top', 5);
        $this->assertQueryCountMax('div.top', 4);
        $this->assertQueryCountMax('div.top', 3);

        $this->expectedException(
            ExpectationFailedException::class,
            'actually occurs 3 times' // check actual occurs is display
        );
        $this->assertQueryCountMax('div.top', 2);
    }

    public function testAssertXpathQueryCountMax(): void
    {
        $this->dispatch('/tests');
        $this->assertXpathQueryCountMax('//div[@class="top"]', 5);
        $this->assertXpathQueryCountMax('//div[@class="top"]', 4);
        $this->assertXpathQueryCountMax('//div[@class="top"]', 3);

        $this->expectedException(
            ExpectationFailedException::class,
            'actually occurs 3 times' // check actual occurs is display
        );
        $this->assertXpathQueryCountMax('//div[@class="top"]', 2);
    }

    public function testAssertQueryContentContains(): void
    {
        $this->dispatch('/tests');
        $this->assertQueryContentContains('div#content', 'foo');

        $this->expectedException(
            ExpectationFailedException::class
        );
        $this->assertQueryContentContains('div#content', 'bar');
    }

    public function testAssertQueryContentContainsWithSecondElement(): void
    {
        $this->dispatch('/tests');
        $this->assertQueryContentContains('div#content', 'foo');

        $this->expectedException(
            ExpectationFailedException::class
        );
        $this->assertQueryContentContains('div.top', 'bar');
    }

    public function testAssertXpathQueryContentContains(): void
    {
        $this->dispatch('/tests');
        $this->assertXpathQueryContentContains('//div[@class="top"]', 'foo');

        $this->expectedException(
            ExpectationFailedException::class
        );
        $this->assertXpathQueryContentContains('//div[@class="top"]', 'bar');
    }

    public function testAssertNotQueryContentContains(): void
    {
        $this->dispatch('/tests');
        $this->assertNotQueryContentContains('div#content', 'bar');

        $this->expectedException(ExpectationFailedException::class);
        $this->assertNotQueryContentContains('div#content', 'foo');
    }

    public function testAssertNotXpathQueryContentContains(): void
    {
        $this->dispatch('/tests');
        $this->assertNotXpathQueryContentContains('//div[@id="content"]', 'bar');

        $this->expectedException(ExpectationFailedException::class);
        $this->assertNotXpathQueryContentContains('//div[@id="content"]', 'foo');
    }

    public function testAssertQueryContentRegex(): void
    {
        $this->dispatch('/tests');
        $this->assertQueryContentRegex('div#content', '#o{2}#');

        $this->expectedException(
            ExpectationFailedException::class,
            'actual content is "foo"' // check actual content is display
        );
        $this->assertQueryContentRegex('div#content', '#o{3,}#');
    }

    public function testAssertQueryContentRegexMultipleMatches(): void
    {
        $this->dispatch('/tests');
        $this->assertQueryContentRegex('div.top', '#o{2}#');

        $this->expectedException(
            ExpectationFailedException::class,
            'actual content is "foo"' // check actual content is display
        );
        $this->assertQueryContentRegex('div.top', '#o{3,}#');
    }

    public function testAssertQueryContentRegexMultipleMatchesNoFalsePositive(): void
    {
        $this->dispatch('/tests');

        $this->expectedException(
            ExpectationFailedException::class,
            'actual content is "foofoofoobar"' // check actual content is display
        );
        $this->assertQueryContentRegex('div', '/foobar/');
    }

    public function testAssertXpathQueryContentRegex(): void
    {
        $this->dispatch('/tests');
        $this->assertXpathQueryContentRegex('//div[@id="content"]', '#o{2}#');

        $this->expectedException(
            ExpectationFailedException::class,
            'actual content is "foo"' // check actual content is display
        );
        $this->assertXpathQueryContentRegex('//div[@id="content"]', '#o{3,}#');
    }

    public function testAssertNotQueryContentRegex(): void
    {
        $this->dispatch('/tests');
        $this->assertNotQueryContentRegex('div#content', '#o{3,}#');

        $this->expectedException(ExpectationFailedException::class);
        $this->assertNotQueryContentRegex('div#content', '#o{2}#');
    }

    public function testAssertNotXpathQueryContentRegex(): void
    {
        $this->dispatch('/tests');
        $this->assertNotXpathQueryContentRegex('//div[@id="content"]', '#o{3,}#');

        $this->expectedException(ExpectationFailedException::class);
        $this->assertNotXpathQueryContentRegex('//div[@id="content"]', '#o{2}#');
    }

    public function testAssertQueryWithDynamicQueryParams(): void
    {
        $this->getRequest()
            ->setMethod('GET')
            ->setQuery(new Parameters(['num_get' => 5]));
        $this->dispatch('/tests');
        $this->assertQueryCount('div.get', 5);
        $this->assertXpathQueryCount('//div[@class="get"]', 5);
        $this->assertQueryCount('div.post', 0);
        $this->assertXpathQueryCount('//div[@class="post"]', 0);
    }

    public function testAssertQueryWithDynamicQueryParamsInDispatchMethod(): void
    {
        $this->dispatch('/tests', 'GET', ['num_get' => 5]);
        $this->assertQueryCount('div.get', 5);
        $this->assertXpathQueryCount('//div[@class="get"]', 5);
        $this->assertQueryCount('div.post', 0);
        $this->assertXpathQueryCount('//div[@class="post"]', 0);
    }

    public function testAssertQueryWithDynamicQueryParamsInUrl(): void
    {
        $this->dispatch('/tests?foo=bar&num_get=5');
        $this->assertQueryCount('div.get', 5);
        $this->assertXpathQueryCount('//div[@class="get"]', 5);
        $this->assertQueryCount('div.post', 0);
        $this->assertXpathQueryCount('//div[@class="post"]', 0);
    }

    public function testAssertQueryWithDynamicQueryParamsInUrlAnsPostInParams(): void
    {
        $this->dispatch('/tests?foo=bar&num_get=5', 'POST', ['num_post' => 5]);
        $this->assertQueryCount('div.get', 5);
        $this->assertXpathQueryCount('//div[@class="get"]', 5);
        $this->assertQueryCount('div.post', 5);
        $this->assertXpathQueryCount('//div[@class="post"]', 5);
    }

    public function testAssertQueryWithDynamicPostParams(): void
    {
        $this->getRequest()
            ->setMethod('POST')
            ->setPost(new Parameters(['num_post' => 5]));
        $this->dispatch('/tests');
        $this->assertQueryCount('div.post', 5);
        $this->assertXpathQueryCount('//div[@class="post"]', 5);
        $this->assertQueryCount('div.get', 0);
        $this->assertXpathQueryCount('//div[@class="get"]', 0);
    }

    public function testAssertQueryWithDynamicPostParamsInDispatchMethod(): void
    {
        $this->dispatch('/tests', 'POST', ['num_post' => 5]);
        $request = $this->getRequest();
        $this->assertEquals($request->getMethod(), 'POST');
        $this->assertQueryCount('div.post', 5);
        $this->assertXpathQueryCount('//div[@class="post"]', 5);
        $this->assertQueryCount('div.get', 0);
        $this->assertXpathQueryCount('//div[@class="get"]', 0);
    }

    public function testAssertQueryWithDynamicPutParamsInDispatchMethod(): void
    {
        $this->dispatch('/tests', 'PUT', ['num_post' => 5, 'foo' => 'bar']);
        $request = $this->getRequest();
        $this->assertEquals($request->getMethod(), 'PUT');
        $this->assertEquals('num_post=5&foo=bar', $request->getContent());
    }

    public function testAssertUriWithHostname(): void
    {
        $this->dispatch('http://my.domain.tld:443');
        $routeMatch = $this->getApplication()->getMvcEvent()->getRouteMatch();
        $this->assertEquals($routeMatch->getParam('subdomain'), 'my');
        $this->assertEquals($this->getRequest()->getUri()->getPort(), 443);
    }

    public function testAssertWithMultiDispatch(): void
    {
        $this->dispatch('/tests');
        $this->assertQueryCount('div.get', 0);
        $this->assertXpathQueryCount('//div[@class="get"]', 0);
        $this->assertQueryCount('div.post', 0);
        $this->assertXpathQueryCount('//div[@class="post"]', 0);

        $this->reset();

        $this->dispatch('/tests?foo=bar&num_get=3');
        $this->assertQueryCount('div.get', 3);
        $this->assertXpathQueryCount('//div[@class="get"]', 3);
        $this->assertQueryCount('div.post', 0);
        $this->assertXpathQueryCount('//div[@class="post"]', 0);

        $this->reset();

        $this->dispatch('/tests');
        $this->assertQueryCount('div.get', 0);
        $this->assertXpathQueryCount('//div[@class="get"]', 0);
        $this->assertQueryCount('div.post', 0);
        $this->assertXpathQueryCount('//div[@class="post"]', 0);
    }

    public function testAssertWithMultiDispatchWithoutPersistence(): void
    {
        if (! extension_loaded('session')) {
            $this->markTestSkipped('No session extension loaded');
        }

        $this->dispatch('/tests-persistence');

        $controller     = $this->getApplicationServiceLocator()
                            ->get('ControllerManager')
                            ->get('baz_index');
        $flashMessenger = $controller->flashMessenger();
        $messages       = $flashMessenger->getMessages();
        $this->assertCount(0, $messages);

        $this->reset(false);

        $this->dispatch('/tests');

        $controller     = $this->getApplicationServiceLocator()
                            ->get('ControllerManager')
                            ->get('baz_index');
        $flashMessenger = $controller->flashMessenger();
        $messages       = $flashMessenger->getMessages();

        $this->assertCount(0, $messages);
    }

    public function testAssertWithMultiDispatchWithPersistence(): void
    {
        if (! extension_loaded('session')) {
            $this->markTestSkipped('No session extension loaded');
        }

        $this->dispatch('/tests-persistence');

        $controller     = $this->getApplicationServiceLocator()
                            ->get('ControllerManager')
                            ->get('baz_index');
        $flashMessenger = $controller->flashMessenger();
        $messages       = $flashMessenger->getMessages();
        $this->assertCount(0, $messages);

        $this->reset(true);

        $this->dispatch('/tests');

        $controller     = $this->getApplicationServiceLocator()
                            ->get('ControllerManager')
                            ->get('baz_index');
        $flashMessenger = $controller->flashMessenger();
        $messages       = $flashMessenger->getMessages();

        $this->assertCount(1, $messages);
    }

    public function testAssertExceptionInAction(): void
    {
        $this->setTraceError(true);

        $this->dispatch('/exception');
        $this->assertResponseStatusCode(500);
        $this->assertApplicationException('RuntimeException');
    }

    public function testAssertExceptionAndMessageInAction(): void
    {
        $this->dispatch('/exception');
        $this->assertResponseStatusCode(500);
        $this->assertApplicationException('RuntimeException', 'Foo error');
    }

    public function testTraceErrorEnableByDefault(): void
    {
        $this->dispatch('/exception');
        $this->assertResponseStatusCode(500);

        try {
            // force exception throwing
            parent::tearDown();
        } catch (Exception $e) {
            $this->getApplication()->getMvcEvent()->setParam('exception', null);
            $this->expectedException('RuntimeException', 'Foo error');
            throw $e;
        }
    }

    public function testGetErrorWithTraceErrorEnabled(): void
    {
        $this->dispatch('/exception');
        $this->assertResponseStatusCode(500);

        $exception = $this->getApplication()->getMvcEvent()->getParam('exception');
        $this->assertInstanceOf('RuntimeException', $exception);

        // set to null to avoid the throwing of the exception
        $this->getApplication()->getMvcEvent()->setParam('exception', null);
    }

    /**
     * Sample tests on MvcEvent
     */
    public function testAssertApplicationMvcEvent(): void
    {
        $this->dispatch('/tests');

        // get and assert mvc event
        $mvcEvent = $this->getApplication()->getMvcEvent();
        $this->assertEquals(true, $mvcEvent instanceof MvcEvent);
        $this->assertEquals($mvcEvent->getApplication(), $this->getApplication());

        // get and assert view controller
        $viewModel = $mvcEvent->getResult();
        $this->assertEquals(true, $viewModel instanceof ViewModel);
        $this->assertEquals($viewModel->getTemplate(), 'baz/index/unittests');

        // get and assert view manager layout
        $layout = $mvcEvent->getViewModel();
        $this->assertEquals(true, $layout instanceof ViewModel);
        $this->assertEquals($layout->getTemplate(), 'layout/layout');

        // children layout must be the controller view
        $this->assertEquals($viewModel, current($layout->getChildren()));
    }

    /**
     * Sample tests on Application events
     */
    public function testAssertApplicationEvents(): void
    {
        $this->url('/tests');

        $result     = $this->triggerApplicationEvent(MvcEvent::EVENT_ROUTE);
        $routeMatch = $result->last();
        $this->assertEquals(false, $result->stopped());
        $this->assertEquals(false, $this->getApplication()->getMvcEvent()->getError());
        $this->assertEquals(true, $routeMatch instanceof RouteMatch);
        $this->assertEquals($routeMatch->getParam('controller'), 'baz_index');

        $this->triggerApplicationEvent(MvcEvent::EVENT_DISPATCH);
        $viewModel = $this->getApplication()->getMvcEvent()->getResult();
        $this->assertEquals(true, $viewModel instanceof ViewModel);
        $this->assertEquals($viewModel->getTemplate(), 'baz/index/unittests');
    }

    public function testAssertResponseReasonPhrase(): void
    {
        $this->dispatch('/tests');
        $this->assertResponseReasonPhrase('OK');

        $this->expectedException(ExpectationFailedException::class);
        $this->assertResponseReasonPhrase('NOT OK');
    }

    public function testAssertXmlHttpRequestDispatch(): void
    {
        $request = $this->getRequest();
        $this->assertFalse($request->isXmlHttpRequest());

        $this->dispatch('/test', 'GET', [], true);

        $request = $this->getRequest();
        $this->assertTrue($request->isXmlHttpRequest());

        $this->reset();

        $request = $this->getRequest();
        $this->assertFalse($request->isXmlHttpRequest());
    }
}
