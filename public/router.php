<?php

/**
 * Router for PHP's built-in development server.
 * Serves static files directly, routes everything else to index.php.
 */

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$file = __DIR__ . $path;

// Serve static files directly if they exist
if ($path !== '/' && is_file($file)) {
    // Set correct MIME types
    $mimeTypes = [
        'css'  => 'text/css',
        'js'   => 'application/javascript',
        'svg'  => 'image/svg+xml',
        'png'  => 'image/png',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif'  => 'image/gif',
        'ico'  => 'image/x-icon',
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf'  => 'font/ttf',
        'json' => 'application/json',
    ];

    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    if (isset($mimeTypes[$ext])) {
        header('Content-Type: ' . $mimeTypes[$ext]);
    }

    readfile($file);
    return true;
}

// Route everything else to front controller
require __DIR__ . '/index.php';
