<?php
declare(strict_types=1);

namespace Core;

/**
 * Modular Core PHP Router
 * Supports parameterized routes ({slug}, {id}), HTTP method dispatch, and middleware chains.
 */
class Router
{
    private array $routes = [];

    public function get(string $path, callable|array $handler, array $middleware = []): void
    {
        $this->addRoute('GET', $path, $handler, $middleware);
    }

    public function post(string $path, callable|array $handler, array $middleware = []): void
    {
        $this->addRoute('POST', $path, $handler, $middleware);
    }

    public function addRoute(string $method, string $path, callable|array $handler, array $middleware = []): void
    {
        $normalized = '/' . trim($path, '/');
        if ($normalized !== '/') {
            $normalized = rtrim($normalized, '/');
        }

        // Convert {param} into named capture groups
        $pattern = preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', '(?P<$1>[^/]+)', $normalized);
        $regex = '#^' . $pattern . '$#';

        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $normalized,
            'regex' => $regex,
            'handler' => $handler,
            'middleware' => $middleware,
        ];
    }

    public function dispatch(string $requestUri, string $requestMethod): void
    {
        $path = parse_url($requestUri, PHP_URL_PATH) ?? '/';

        // Strip base path when hosted inside an XAMPP subfolder (e.g. /travelsite)
        $basePath = base_path();
        if ($basePath !== '' && str_starts_with($path, $basePath)) {
            $path = substr($path, strlen($basePath));
        }

        $path = '/' . trim($path, '/');
        if ($path !== '/') {
            $path = rtrim($path, '/');
        }

        $method = strtoupper($requestMethod);

        // Support _method override for HTML forms if needed
        if ($method === 'POST' && isset($_POST['_method'])) {
            $override = strtoupper((string) $_POST['_method']);
            if (in_array($override, ['PUT', 'PATCH', 'DELETE'], true)) {
                $method = $override;
            }
        }

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            if (preg_match($route['regex'], $path, $matches)) {
                $params = [];
                foreach ($matches as $key => $value) {
                    if (is_string($key)) {
                        $params[$key] = urldecode($value);
                    }
                }

                // Execute middleware pipeline before handler
                foreach ($route['middleware'] as $mw) {
                    if (is_callable($mw)) {
                        $mw($params);
                    } elseif (is_string($mw) && class_exists($mw)) {
                        (new $mw())->handle($params);
                    } elseif (is_array($mw) && isset($mw[0]) && class_exists($mw[0])) {
                        $instance = new $mw[0]();
                        $args = array_slice($mw, 1);
                        $instance->handle($params, ...$args);
                    }
                }

                $handler = $route['handler'];
                if (is_array($handler)) {
                    [$controllerClass, $action] = $handler;
                    $controller = new $controllerClass();
                    $controller->$action($params);
                    return;
                }

                $handler($params);
                return;
            }
        }

        http_response_code(404);
        View::render('frontend/404', ['pageTitle' => 'Page Not Found'], 'frontend');
    }
}
