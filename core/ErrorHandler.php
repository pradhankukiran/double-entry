<?php

declare(strict_types=1);

namespace DoubleE\Core;

class ErrorHandler
{
    private bool $debug;
    private string $logPath;

    public function __construct(bool $debug, string $logPath)
    {
        $this->debug = $debug;
        $this->logPath = $logPath;
    }

    public function register(): void
    {
        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);
        register_shutdown_function([$this, 'handleShutdown']);
    }

    public function handleError(int $level, string $message, string $file, int $line): bool
    {
        if (!(error_reporting() & $level)) {
            return false;
        }

        throw new \ErrorException($message, 0, $level, $file, $line);
    }

    public function handleException(\Throwable $e): void
    {
        $this->log($e);

        $code = $this->getHttpCode($e);
        http_response_code($code);

        if (php_sapi_name() === 'cli') {
            fwrite(STDERR, $e->getMessage() . "\n" . $e->getTraceAsString() . "\n");
            return;
        }

        if ($this->debug) {
            $this->renderDebugPage($e);
        } else {
            $this->renderErrorPage($code);
        }
    }

    public function handleShutdown(): void
    {
        $error = error_get_last();

        if ($error !== null && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE])) {
            $this->handleException(
                new \ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line'])
            );
        }
    }

    private function log(\Throwable $e): void
    {
        $logFile = $this->logPath . '/error.log';
        $timestamp = date('Y-m-d H:i:s');
        $entry = "[{$timestamp}] {$e->getMessage()} in {$e->getFile()}:{$e->getLine()}\n";
        $entry .= $e->getTraceAsString() . "\n\n";

        @file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX);
    }

    private function getHttpCode(\Throwable $e): int
    {
        if ($e instanceof \RuntimeException && str_contains($e->getMessage(), 'CSRF')) {
            return 403;
        }

        return match (true) {
            $e->getCode() >= 400 && $e->getCode() < 600 => $e->getCode(),
            default => 500,
        };
    }

    private function renderDebugPage(\Throwable $e): void
    {
        $class = get_class($e);
        $message = htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        $file = htmlspecialchars($e->getFile(), ENT_QUOTES, 'UTF-8');
        $line = $e->getLine();
        $trace = htmlspecialchars($e->getTraceAsString(), ENT_QUOTES, 'UTF-8');

        echo <<<HTML
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Error — Double-E</title>
            <style>
                body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; margin: 0; padding: 40px; background: #fff; }
                .error-box { max-width: 900px; margin: 0 auto; }
                h1 { color: #dc3545; font-size: 1.4rem; margin-bottom: 0.5rem; }
                .message { font-size: 1.1rem; color: #333; margin-bottom: 1rem; }
                .file { color: #666; font-size: 0.9rem; margin-bottom: 1.5rem; }
                pre { background: #f8f9fa; padding: 20px; border: 1px solid #dee2e6; border-radius: 4px; overflow-x: auto; font-size: 0.85rem; line-height: 1.6; }
            </style>
        </head>
        <body>
            <div class="error-box">
                <h1>{$class}</h1>
                <p class="message">{$message}</p>
                <p class="file">{$file}:{$line}</p>
                <pre>{$trace}</pre>
            </div>
        </body>
        </html>
        HTML;
    }

    private function renderErrorPage(int $code): void
    {
        $app = Application::getInstance();
        $viewsPath = $app ? $app->rootPath() . '/views' : dirname(__DIR__) . '/views';
        $errorView = $viewsPath . "/errors/{$code}.php";

        if (file_exists($errorView)) {
            $view = new View($viewsPath);
            echo $view->render("errors/{$code}", [], 'layouts/auth');
        } else {
            echo "<h1>Error {$code}</h1><p>An unexpected error occurred.</p>";
        }
    }
}
