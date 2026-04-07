<?php

declare(strict_types=1);

namespace DoubleE\Middleware;

use DoubleE\Core\Request;
use DoubleE\Core\Response;
use DoubleE\Core\Auth;

class RoleMiddleware
{
    private string $permission;

    public function __construct(string $permission = '')
    {
        $this->permission = $permission;
    }

    /**
     * Check that the authenticated user has the required permission.
     * Responds with 403 if the permission check fails.
     */
    public function handle(Request $request, Response $response): void
    {
        $auth = Auth::getInstance();

        if (!$auth->check()) {
            $response->redirect('/login');
            $response->send();
            exit;
        }

        if ($this->permission !== '' && !$auth->hasPermission($this->permission)) {
            http_response_code(403);
            throw new \RuntimeException('You do not have permission to access this resource.');
        }
    }
}
