<?php

declare(strict_types=1);

namespace DoubleE\Controllers;

use DoubleE\Core\Application;
use DoubleE\Core\Csrf;
use DoubleE\Core\Request;
use DoubleE\Core\Response;
use DoubleE\Core\Session;
use DoubleE\Core\View;

abstract class BaseController
{
    protected Request $request;
    protected Response $response;
    protected View $view;

    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
        $this->view = new View();
    }

    protected function render(string $template, array $data = [], string $layout = 'layouts/main'): Response
    {
        $html = $this->view->render($template, $data, $layout);
        $this->response->setBody($html);
        return $this->response;
    }

    protected function redirect(string $url, int $code = 302): Response
    {
        $this->response->redirect($url, $code);
        return $this->response;
    }

    protected function json(array $data, int $code = 200): Response
    {
        $this->response->json($data, $code);
        return $this->response;
    }

    protected function flash(string $type, string $message): void
    {
        Session::flash($type, $message);
    }

    protected function validateCsrf(): void
    {
        Csrf::check();
    }
}
