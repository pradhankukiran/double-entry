<?php

/**
 * Route definitions.
 *
 * Each route: ['method' => ..., 'path' => ..., 'controller' => ..., 'action' => ..., 'middleware' => [...]]
 */
return [
    // Home
    ['method' => 'GET', 'path' => '/', 'controller' => 'HomeController', 'action' => 'index', 'middleware' => ['AuthMiddleware']],

    // Auth
    ['method' => 'GET', 'path' => '/login', 'controller' => 'AuthController', 'action' => 'showLogin'],
    ['method' => 'POST', 'path' => '/login', 'controller' => 'AuthController', 'action' => 'login'],
    ['method' => 'POST', 'path' => '/logout', 'controller' => 'AuthController', 'action' => 'logout'],

    // Users
    ['method' => 'GET', 'path' => '/users', 'controller' => 'UserController', 'action' => 'index', 'middleware' => ['AuthMiddleware']],
    ['method' => 'GET', 'path' => '/profile', 'controller' => 'UserController', 'action' => 'profile', 'middleware' => ['AuthMiddleware']],
    ['method' => 'POST', 'path' => '/profile', 'controller' => 'UserController', 'action' => 'updateProfile', 'middleware' => ['AuthMiddleware']],

    // Chart of Accounts
    ['method' => 'GET', 'path' => '/accounts', 'controller' => 'AccountController', 'action' => 'index', 'middleware' => ['AuthMiddleware']],
    ['method' => 'GET', 'path' => '/accounts/create', 'controller' => 'AccountController', 'action' => 'create', 'middleware' => ['AuthMiddleware']],
    ['method' => 'POST', 'path' => '/accounts', 'controller' => 'AccountController', 'action' => 'store', 'middleware' => ['AuthMiddleware']],
    ['method' => 'GET', 'path' => '/accounts/{id}', 'controller' => 'AccountController', 'action' => 'show', 'middleware' => ['AuthMiddleware']],
    ['method' => 'GET', 'path' => '/accounts/{id}/edit', 'controller' => 'AccountController', 'action' => 'edit', 'middleware' => ['AuthMiddleware']],
    ['method' => 'POST', 'path' => '/accounts/{id}', 'controller' => 'AccountController', 'action' => 'update', 'middleware' => ['AuthMiddleware']],
    ['method' => 'POST', 'path' => '/accounts/{id}/toggle', 'controller' => 'AccountController', 'action' => 'toggleActive', 'middleware' => ['AuthMiddleware']],

    // Fiscal Years
    ['method' => 'GET', 'path' => '/fiscal-years', 'controller' => 'FiscalYearController', 'action' => 'index', 'middleware' => ['AuthMiddleware']],
    ['method' => 'GET', 'path' => '/fiscal-years/create', 'controller' => 'FiscalYearController', 'action' => 'create', 'middleware' => ['AuthMiddleware']],
    ['method' => 'POST', 'path' => '/fiscal-years', 'controller' => 'FiscalYearController', 'action' => 'store', 'middleware' => ['AuthMiddleware']],
];
