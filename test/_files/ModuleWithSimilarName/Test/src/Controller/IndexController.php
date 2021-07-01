<?php

/**
 * @see       https://github.com/laminas/laminas-test for the canonical source repository
 */

namespace ModuleWithSimilarName\Test\Controller;

use Laminas\Mvc\Controller\AbstractActionController;

class IndexController extends AbstractActionController
{
    public function unittestsAction(): string
    {
        return 'unittest';
    }
}
