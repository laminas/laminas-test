<?php

declare(strict_types=1);

namespace Baz\Controller;

use Laminas\Http\Response;
use Laminas\Mvc\Controller\AbstractActionController;
use RuntimeException;

class IndexController extends AbstractActionController
{
    /** @return array<string, string> */
    public function unittestsAction(): array
    {
        $this->getResponse()
            ->getHeaders()
            ->addHeaderLine('Content-Type: text/html')
            ->addHeaderLine('WWW-Authenticate: Basic realm="Laminas"');

        $numGet  = $this->getRequest()->getQuery()->get('num_get', 0);
        $numPost = $this->getRequest()->getPost()->get('num_post', 0);

        return ['num_get' => $numGet, 'num_post' => $numPost];
    }

    public function persistencetestAction(): void
    {
        $this->flashMessenger()->addMessage('test');
    }

    public function redirectAction(): Response
    {
        return $this->redirect()->toUrl('https://www.zend.com');
    }

    public function redirectToRouteAction(): Response
    {
        return $this->redirect()->toRoute('myroute');
    }

    public function exceptionAction(): never
    {
        throw new RuntimeException('Foo error !');
    }

    public function customResponseAction(): Response
    {
        $response = new Response();
        $response->setCustomStatusCode(999);

        return $response;
    }

    public function registerxpathnamespaceAction(): void
    {
    }
}
