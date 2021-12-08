<?php

declare(strict_types=1);

namespace ModuleWithSimilarName\TestModule\Controller;

use Laminas\Mvc\Controller\AbstractActionController;

class IndexController extends AbstractActionController
{
    public function unittestsAction(): string
    {
        return 'unittest';
    }
}
