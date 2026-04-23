<?php
/**
 * AeroCanada v2 — Simple Router
 * Maps clean URLs to module controllers.
 * Falls back to legacy page loading for backward compatibility.
 */

namespace AeroCanada\Core;

class Router
{
    private array $routes = [];

    /**
     * Register a route.
     */
    public function add(string $method, string $path, callable $handler): self
    {
        $this->routes[] = [
            'method'  => strtoupper($method),
            'path'    => $path,
            'handler' => $handler,
        ];
        return $this;
    }

    public function get(string $path, callable $handler): self
    {
        return $this->add('GET', $path, $handler);
    }

    public function post(string $path, callable $handler): self
    {
        return $this->add('POST', $path, $handler);
    }

    /**
     * Dispatch the current request.
     */
    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        // Strip base path if needed
        $basePath = dirname($_SERVER['SCRIPT_NAME']);
        if ($basePath !== '/' && $basePath !== '\\') {
            $uri = substr($uri, strlen($basePath));
        }
        $uri = '/' . ltrim($uri, '/');

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method && $route['method'] !== 'ANY') {
                continue;
            }

            $params = $this->match($route['path'], $uri);
            if ($params !== false) {
                call_user_func($route['handler'], $params);
                return;
            }
        }

        // No route matched — 404
        http_response_code(404);
        echo '404 — Page not found';
    }

    /**
     * Match a route pattern against a URI.
     * Supports {param} placeholders.
     */
    private function match(string $pattern, string $uri): array|false
    {
        // Convert {param} to regex groups
        $regex = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $pattern);
        $regex = '#^' . $regex . '$#';

        if (preg_match($regex, $uri, $matches)) {
            return array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
        }

        return false;
    }
}
