<?php

declare(strict_types=1);

namespace DoubleE\Core;

class Router
{
    private array $routes = [];
    private array $middlewareGroups = [];

    public function get(string $path, string $controller, string $action, array $middleware = []): void
    {
        $this->addRoute('GET', $path, $controller, $action, $middleware);
    }

    public function post(string $path, string $controller, string $action, array $middleware = []): void
    {
        $this->addRoute('POST', $path, $controller, $action, $middleware);
    }

    private function addRoute(string $method, string $path, string $controller, string $action, array $middleware): void
    {
        $pattern = $this->pathToRegex($path);

        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'pattern' => $pattern,
            'controller' => $controller,
            'action' => $action,
            'middleware' => $middleware,
        ];
    }

    private function pathToRegex(string $path): string
    {
        $path = '/' . trim($path, '/');
        // Convert {param} to named capture groups
        $pattern = preg_replace('#\{([a-zA-Z_]+)\}#', '(?P<$1>[^/]+)', $path);
        return '#^' . $pattern . '$#';
    }

    public function loadRoutes(array $routes): void
    {
        foreach ($routes as $route) {
            $method = strtoupper($route['method'] ?? 'GET');
            $path = $route['path'];
            $controller = $route['controller'];
            $action = $route['action'];
            $middleware = $route['middleware'] ?? [];

            $this->addRoute($method, $path, $controller, $action, $middleware);
        }
    }

    public function resolve(string $method, string $uri): ?array
    {
        $uri = '/' . trim($uri, '/');

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            if (preg_match($route['pattern'], $uri, $matches)) {
                // Extract named parameters only
                $params = array_filter($matches, fn($key) => is_string($key), ARRAY_FILTER_USE_KEY);

                return [
                    'controller' => $route['controller'],
                    'action' => $route['action'],
                    'params' => $params,
                    'middleware' => $route['middleware'],
                ];
            }
        }

        return null;
    }
}
