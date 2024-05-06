<?php

namespace App\Http\Controller;

use Psr\Http\Message\ResponseInterface;

/**
 * Controller for UI Kit examples
 */
class InternalController extends AbstractController
{
    public function index(): ResponseInterface
    {
        return $this->view->render('internal/ui-kit.blade.php');
    }
}
