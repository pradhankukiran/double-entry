<?php

declare(strict_types=1);

namespace DoubleE\Middleware;

use DoubleE\Core\Request;
use DoubleE\Core\Response;
use DoubleE\Services\Auth;

class AuthMiddleware
{
    /**
     * Ensure the user is authenticated. Redirects to /login if not.
     */
    public function handle(Request $request, Response $response): void
    {
        if (!Auth::getInstance()->check()) {
            $response->redirect('/login');
            $response->send();
            exit;
        }
    }
}
