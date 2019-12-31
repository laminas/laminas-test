<?php

/**
 * @see       https://github.com/laminas/laminas-test for the canonical source repository
 * @copyright https://github.com/laminas/laminas-test/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-test/blob/master/LICENSE.md New BSD License
 */

namespace Baz\Controller;

use Laminas\Http\Response;
use Laminas\Mvc\Controller\AbstractActionController;

class IndexController extends AbstractActionController
{
    public function unittestsAction()
    {
        $this->getResponse()
            ->getHeaders()
            ->addHeaderLine('Content-Type: text/html')
            ->addHeaderLine('WWW-Authenticate: Basic realm="Laminas"');

        $num_get = $this->getRequest()->getQuery()->get('num_get', 0);
        $num_post = $this->getRequest()->getPost()->get('num_post', 0);

        return array('num_get' => $num_get, 'num_post' => $num_post);
    }

    public function consoleAction()
    {
        return 'foo, bar';
    }

    public function persistencetestAction()
    {
        $this->flashMessenger()->addMessage('test');
    }

    public function redirectAction()
    {
        return $this->redirect()->toUrl('https://www.zend.com');
    }

    public function exceptionAction()
    {
        throw new \RuntimeException('Foo error !');
    }

    public function customResponseAction()
    {
        $response = new Response();
        $response->setCustomStatusCode(999);

        return $response;
    }
}
