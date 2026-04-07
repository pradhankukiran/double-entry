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
];
