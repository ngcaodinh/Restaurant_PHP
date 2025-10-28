<?php

namespace Core;

class Router
{
    private array $routes = [];

    public function get(string $path, callable|array $handler): void
    {
        $this->map('GET', $path, $handler);
    }

    public function post(string $path, callable|array $handler): void
    {
        $this->map('POST', $path, $handler);
    }

    public function map(string $method, string $path, callable|array $handler): void
    {
        $this->routes[$method][$this->normalize($path)] = $handler;
    }

    private function normalize(string $path): string
    {
        $path = '/' . ltrim($path, '/');
        return rtrim($path, '/') ?: '/';
    }

    public function dispatch(?string $uri = null, ?string $method = null)
    {
        $uri = $uri ?? parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $method = $method ?? $_SERVER['REQUEST_METHOD'];

        $path = $this->normalize($this->stripBase($uri));
        $handler = null;
        $params = [];

        // Check for dynamic routes
        foreach ($this->routes[$method] ?? [] as $route => $h) {
            $route_regex = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $route);
            if (preg_match('#^' . $route_regex . '$#', $path, $matches)) {
                $handler = $h;
                foreach ($matches as $key => $value) {
                    if (is_string($key)) {
                        $params[$key] = $value;
                    }
                }
                break;
            }
        }

        // Check for static routes if no dynamic route matched
        if (!$handler) {
            $handler = $this->routes[$method][$path] ?? null;
        }

        if (!$handler) {
            // Try fallback for .php paths to keep old URLs working
            if (str_ends_with($path, '.php')) {
                $alt = $this->normalize(str_replace('.php', '', $path));
                $handler = $this->routes[$method][$alt] ?? null;
            }
        }

        // Legacy compatibility: if a matching PHP file exists, include it directly
        if (!$handler) {
            $legacyIncluded = $this->tryIncludeLegacy($path, $method);
            if ($legacyIncluded) {
                return;
            }
        }

        if (!$handler) {
            http_response_code(404);
            echo '404 Not Found';
            return;
        }

        if (is_array($handler)) {
            [$class, $methodName] = $handler;
            if ($class === 'App\Controllers\CartController') {
                $db = \Database::getInstance();
                $controller = new $class($db);
            } else {
                $controller = new $class();
            }
            // Store route parameters in $_GET for controller access
            $_GET = array_merge($_GET, $params);
            return $controller->$methodName();
        }

        return call_user_func($handler);
    }

    private function stripBase(string $uri): string
    {
        // Keep compatibility with BASE_URL if defined
        if (defined('BASE_URL')) {
            $basePath = parse_url(BASE_URL, PHP_URL_PATH) ?: '/';
            $basePath = rtrim($basePath, '/');
            if ($basePath !== '' && str_starts_with($uri, $basePath)) {
                return substr($uri, strlen($basePath)) ?: '/';
            }
        }
        return $uri;
    }

    private function tryIncludeLegacy(string $path, string $method): bool
    {
        // Only allow .php under known folders or root files to be included
        $candidates = [];
        $clean = ltrim($path, '/');

        // Direct .php path
        if (str_ends_with($clean, '.php')) {
            $candidates[] = $clean;
        }
        // Try with .php appended
        $candidates[] = $clean . '.php';

        // Prevent directory traversal
        $base = realpath(__DIR__ . '/..');
        foreach ($candidates as $rel) {
            $full = realpath($base . DIRECTORY_SEPARATOR . $rel);
            if ($full && str_starts_with($full, $base) && is_file($full)) {
                // Include and stop routing
                require $full;
                return true;
            }
        }
        return false;
    }
}
