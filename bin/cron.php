#!/usr/bin/env php
<?php

/**
 * Recurring Transaction Cron Job
 *
 * Processes all due recurring transaction templates and creates
 * their corresponding transactions (journal entries, invoices, bills).
 *
 * Usage:
 *   php bin/cron.php
 *
 * Recommended crontab entry (run daily at midnight):
 *   0 0 * * * /usr/bin/php /path/to/Double-E/bin/cron.php >> /path/to/Double-E/storage/logs/cron.log 2>&1
 */

declare(strict_types=1);

define('ROOT_PATH', dirname(__DIR__));

require ROOT_PATH . '/vendor/autoload.php';

use DoubleE\Core\Application;
use DoubleE\Services\RecurringTransactionService;

// Ensure we are running from CLI
if (php_sapi_name() !== 'cli') {
    echo "This script must be run from the command line.\n";
    exit(1);
}

try {
    // Boot application (loads config, database connection, etc.)
    $app = new Application(ROOT_PATH);

    echo '[' . date('Y-m-d H:i:s') . "] Starting recurring transaction processing...\n";

    $service = new RecurringTransactionService();
    $results = $service->processDue();

    $processed = $results['processed'] ?? 0;
    $errors    = $results['errors'] ?? [];

    echo '[' . date('Y-m-d H:i:s') . "] Processed: {$processed} template(s)\n";

    if (!empty($errors)) {
        echo '[' . date('Y-m-d H:i:s') . "] Errors:\n";
        foreach ($errors as $error) {
            echo "  - {$error}\n";
        }
    }

    echo '[' . date('Y-m-d H:i:s') . "] Done.\n";

    exit(empty($errors) ? 0 : 1);
} catch (\Throwable $e) {
    echo '[' . date('Y-m-d H:i:s') . "] FATAL: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(2);
}
