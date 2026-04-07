<?php

declare(strict_types=1);

define('ROOT_PATH', dirname(__DIR__));
define('APP_START', microtime(true));

require ROOT_PATH . '/vendor/autoload.php';

use DoubleE\Core\Application;
use DoubleE\Core\Session;

// Boot application
$app = new Application(ROOT_PATH);

// Register error handler
$app->registerErrorHandler();

// Start session
Session::start();

// Load routes
$routes = require ROOT_PATH . '/config/routes.php';
$app->router()->loadRoutes($routes);

// Dispatch
$app->run();
