<?php
namespace App\Core;

class Router {
    private array $routes = [];

    /**
     * Add a GET route
     */
    public function get($uri, $action) {
        $this->add('GET', $uri, $action);
    }

    /**
     * Add a POST route
     */
    public function post($uri, $action) {
        $this->add('POST', $uri, $action);
    }

    private function add($method, $uri, $action) {
        $this->routes[] = [
            'method' => $method,
            'uri' => '/' . trim($uri, '/'),
            'action' => $action
        ];
    }

    /**
     * Dispatch the route
     */
    public function dispatch($uri, $method) {
        // Xóa dấu / ở đầu và cuối
        $uri = trim(parse_url($uri, PHP_URL_PATH), '/');
        
        // Remove subfolder path if exists (e.g. qnu-student-management/)
        $base = basename(ROOT);
        if (strpos($uri, $base) === 0) {
            $uri = trim(substr($uri, strlen($base)), '/');
        }
        
        // Đảm bảo URI luôn bắt đầu bằng /
        $uri = '/' . $uri;

        foreach ($this->routes as $route) {
            if ($route['uri'] === $uri && $route['method'] === $method) {
                $action = $route['action'];

                // Handle Class@method
                if (is_string($action)) {
                    list($controller, $methodName) = explode('@', $action);
                    $controller = "App\\Controllers\\{$controller}";
                    
                    if (class_exists($controller)) {
                        $controllerInstance = new $controller();
                        if (method_exists($controllerInstance, $methodName)) {
                            return $controllerInstance->$methodName();
                        }
                    }
                }
                
                // Handle callback
                if (is_callable($action)) {
                    return call_user_func($action);
                }
            }
        }

        // 404
        $this->abort();
    }

    protected function abort($code = 404) {
        http_response_code($code);
        echo "<h1>{$code} Not Found</h1>";
        exit;
    }
}
