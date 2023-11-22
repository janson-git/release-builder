<?php

namespace App\Http\Controller;

use Admin\App;
use Admin\View;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;

abstract class AbstractController
{
    protected App $app;
    protected View $view;
    protected Request $request;
    protected Response $response;
    protected Container $container;

    public function __construct(
        Container $container,
        Request $request,
        Response $response
    ) {
        $this->container = $container;

        $this->app = App::getInstance();
        $this->view = $this->app->getContainer()->get('view');

        $this->request = $request;
        $this->response = $response;
    }

    /**
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function p(string $name, $default = null)
    {
        return $this->request->getParam($name, $default);
    }
    
    public function setTitle(string $title): void
    {
        $this->view->setHeader($title);
    }
    
    public function setSubTitle(string $subTitle): void
    {
        $this->view->setTitle($subTitle);
    }
}
