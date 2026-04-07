<?php

declare(strict_types=1);

namespace DoubleE\Core;

class Application
{
    private static ?Application $instance = null;
    private string $rootPath;
    private Router $router;
    private Request $request;
    private Response $response;
    private array $config = [];

    public function __construct(string $rootPath)
    {
        self::$instance = $this;
        $this->rootPath = $rootPath;
        $this->router = new Router();
        $this->request = new Request();
        $this->response = new Response();

        $this->loadEnvironment();
        $this->loadConfig();
    }

    public static function getInstance(): ?Application
    {
        return self::$instance;
    }

    private function loadEnvironment(): void
    {
        $envFile = $this->rootPath . '/.env';
        if (!file_exists($envFile)) {
            return;
        }

        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            if (str_contains($line, '=')) {
                [$key, $value] = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value, " \t\n\r\0\x0B\"'");
                $_ENV[$key] = $value;
                putenv("{$key}={$value}");
            }
        }
    }

    private function loadConfig(): void
    {
        $configPath = $this->rootPath . '/config';
        foreach (glob($configPath . '/*.php') as $file) {
            $name = basename($file, '.php');
            $this->config[$name] = require $file;
        }
    }

    public function config(string $key, mixed $default = null): mixed
    {
        $parts = explode('.', $key);
        $value = $this->config;

        foreach ($parts as $part) {
            if (!is_array($value) || !array_key_exists($part, $value)) {
                return $default;
            }
            $value = $value[$part];
        }

        return $value;
    }

    public function router(): Router
    {
        return $this->router;
    }

    public function request(): Request
    {
        return $this->request;
    }

    public function response(): Response
    {
        return $this->response;
    }

    public function rootPath(): string
    {
        return $this->rootPath;
    }

    public function registerErrorHandler(): void
    {
        $handler = new ErrorHandler(
            $this->config('app.debug', false),
            $this->rootPath . '/storage/logs'
        );
        $handler->register();
    }

    public function run(): void
    {
        $route = $this->router->resolve(
            $this->request->method(),
            $this->request->uri()
        );

        if ($route === null) {
            $this->response->setStatusCode(404);
            $view = new View($this->rootPath . '/views');
            $this->response->setBody($view->render('errors/404', [], 'layouts/auth'));
            $this->response->send();
            return;
        }

        // Run middleware
        foreach ($route['middleware'] as $middlewareClass) {
            $fqcn = "DoubleE\\Middleware\\{$middlewareClass}";
            if (class_exists($fqcn)) {
                $middleware = new $fqcn();
                $middleware->handle($this->request, $this->response);
            }
        }

        // Instantiate controller and call action
        $controllerClass = "DoubleE\\Controllers\\{$route['controller']}";
        if (!class_exists($controllerClass)) {
            throw new \RuntimeException("Controller {$controllerClass} not found");
        }

        $controller = new $controllerClass($this->request, $this->response);
        $action = $route['action'];

        if (!method_exists($controller, $action)) {
            throw new \RuntimeException("Action {$action} not found on {$controllerClass}");
        }

        $result = $controller->$action(...array_values($route['params']));

        if ($result instanceof Response) {
            $result->send();
        } elseif (is_string($result)) {
            $this->response->setBody($result);
            $this->response->send();
        }
    }
}
