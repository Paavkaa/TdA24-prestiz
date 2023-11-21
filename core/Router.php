<?php

namespace Core;

use DI\Container;

class Router
{
    private $routes = [];
    private readonly Container $container;

    public function __construct()
    {
        $this->container = new Container();
    }

    public function addRoute(string $method, string $path, string $handler): void
    {
        $this->routes[$method][$path] = $handler;

    }

    public function handleRequest(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        if (isset($this->routes[$method])) {
            foreach ($this->routes[$method] as $routePath => $handler) {
                $pattern = $this->getRouteRegex($routePath);

                if (preg_match($pattern, $path, $matches)) {
                    // Remove the full match from the beginning of the array
                    array_shift($matches);

                    // Create an associative array of parameter names and values
                    $params = (object)array_combine($this->getParameterNames($routePath), $matches);

                    // Call the handler with the matched parameters
                    $this->callHandler($handler, $params);
                    return;
                }
            }
        }

        // Handle 404 Not Found
        http_response_code(404);
        echo "Not Found";
    }

    private function getRouteRegex($routePath): string
    {
        // Convert route path to a regex pattern
        $pattern = preg_replace_callback('/{([^\/]+)}/', function ($matches) {
            return '([^\/]+)';
        }, $routePath);

        // Add delimiters and make it case-insensitive
        return '@^' . $pattern . '$@i';
    }

    private function getParameterNames($routePath): array
    {
        preg_match_all('/{([^\/]+)}/', $routePath, $matches);
        return $matches[1];
    }

    private function callHandler($handler): void
    {
        if (is_callable($handler)) {
            // If the handler is a callable function, call it
            call_user_func($handler);
        } elseif (is_string($handler) && str_contains($handler, '@')) {
            // If the handler is in the "Controller@action" format
            list($controller, $action) = explode('@', $handler);

            $controllerClassName = "App\\Controllers\\" . $controller;
            try {
                $controllerInstance = $this->container->get($controllerClassName);
            } catch (\Exception $e) {
                http_response_code(500);
                echo "Internal Server Error " . $e->getMessage();
                return;
            }

            if (method_exists($controllerInstance, $action)) {
                // Call the controller action method
                $controllerInstance->$action();
            } else {
                // Handle 404 Not Found for missing action
                http_response_code(404);
                echo "Not Found";
            }
        } else {
            // Handle invalid handler format
            http_response_code(500);
            echo "Internal Server Error";
        }
    }
}
