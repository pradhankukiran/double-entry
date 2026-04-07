<?php

declare(strict_types=1);

/**
 * Database setup script — runs migrations and seeds.
 * Safe to run multiple times (uses IF NOT EXISTS / INSERT IGNORE patterns).
 */

$host = $_ENV['DB_HOST'] ?? '127.0.0.1';
$port = $_ENV['DB_PORT'] ?? '3306';
$db   = $_ENV['DB_DATABASE'] ?? 'double_e';
$user = $_ENV['DB_USERNAME'] ?? 'root';
$pass = $_ENV['DB_PASSWORD'] ?? '';

$maxRetries = 10;
$pdo = null;

for ($i = 1; $i <= $maxRetries; $i++) {
    try {
        $pdo = new PDO("mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
        echo "  Connected to MySQL.\n";
        break;
    } catch (PDOException $e) {
        echo "  Waiting for MySQL... ({$i}/{$maxRetries})\n";
        sleep(2);
    }
}

if ($pdo === null) {
    echo "  ERROR: Could not connect to MySQL after {$maxRetries} retries.\n";
    exit(1);
}

// Run migrations
$migrationDir = __DIR__ . '/../database/migrations';
$files = glob($migrationDir . '/*.sql');
sort($files);

foreach ($files as $file) {
    $name = basename($file);
    echo "  Migration: {$name}\n";
    $sql = file_get_contents($file);
    try {
        $pdo->exec($sql);
    } catch (PDOException $e) {
        // Table already exists or duplicate — safe to skip
        if (str_contains($e->getMessage(), 'already exists')) {
            echo "    (skipped — already exists)\n";
        } else {
            echo "    WARNING: {$e->getMessage()}\n";
        }
    }
}

// Run seeds
$seeds = [
    'default_roles.sql',
    'seed_account_types.sql',
    'seed_default_chart_of_accounts.sql',
    'default_admin.sql',
    'demo_data.sql',
];

$seedDir = __DIR__ . '/../database/seeds';

// Re-connect with multi-statement support for seeds
$pdoMulti = new PDO("mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4", $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::MYSQL_ATTR_MULTI_STATEMENTS => true,
]);

foreach ($seeds as $seed) {
    $file = $seedDir . '/' . $seed;
    if (!file_exists($file)) {
        continue;
    }
    echo "  Seed: {$seed}\n";
    $sql = file_get_contents($file);
    try {
        $pdoMulti->exec($sql);
        // Consume remaining result sets from multi-statement
        while ($pdoMulti->nextRowset()) {}
    } catch (PDOException $e) {
        if (str_contains($e->getMessage(), 'Duplicate entry') || str_contains($e->getMessage(), 'already exists')) {
            echo "    (skipped — already seeded)\n";
        } else {
            echo "    WARNING: {$e->getMessage()}\n";
        }
    }
}

echo "  Setup complete.\n";
