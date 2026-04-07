<?php

declare(strict_types=1);

namespace DoubleE\Controllers;

use DoubleE\Core\Response;

class HomeController extends BaseController
{
    public function index(): Response
    {
        return $this->render('dashboard/index', [
            'pageTitle' => 'Dashboard',
        ]);
    }
}
