<?php
class Router {
    private $routes = [];
    public function addRoute($method, $path, $handler) {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler
        ];
    }
public function dispatch($requestMethod, $requestUri) {
        $parsedUrl = parse_url($requestUri);
        $path = $parsedUrl['path'];
        
        $basePath = str_replace('\\', '/', dirname(dirname($_SERVER['SCRIPT_NAME'])));
        if (strpos($path, $basePath) === 0) {
            $path = substr($path, strlen($basePath));
        }

        $path = rtrim($path, '/') ?: '/';

        foreach ($this->routes as $route) {
            if ($route['method'] === $requestMethod && $route['path'] === $path) {
                call_user_func($route['handler']);
                return;
            }
        }
        http_response_code(404);
        echo json_encode(["error" => "not_found", "message" => "Endpoint no encontrado."]);
    }
}
