<?php

/**
 * Route definitions.
 *
 * Each route: ['method' => ..., 'path' => ..., 'controller' => ..., 'action' => ..., 'middleware' => [...]]
 */
return [
    // Home
    ['method' => 'GET', 'path' => '/', 'controller' => 'HomeController', 'action' => 'index'],
];
