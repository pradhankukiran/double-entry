<?php

return [
    'name' => $_ENV['APP_NAME'] ?? 'Double-E Accounting',
    'env' => $_ENV['APP_ENV'] ?? 'development',
    'debug' => filter_var($_ENV['APP_DEBUG'] ?? true, FILTER_VALIDATE_BOOLEAN),
    'url' => $_ENV['APP_URL'] ?? 'http://localhost:8080',
    'timezone' => $_ENV['APP_TIMEZONE'] ?? 'UTC',
];
