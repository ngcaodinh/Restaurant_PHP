<?php
class Router {
    private $routes = [];

    public function addRoute($path, $controller, $method) {
        $this->routes[$path] = ['controller' => $controller, 'method' => $method];
    }

    public function route() {
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $path = rtrim(str_replace('/Restaurant_CTUT_PHP', '', $path), '/');
        $path = $path === '' ? '/' : $path;

        if (isset($this->routes[$path])) {
            $route = $this->routes[$path];
            $controller = new $route['controller'](); // Dòng 16: Khởi tạo controller
            $method = $route['method'];
            $controller->$method();
        } else {
            header('HTTP/1.0 404 Not Found');
            echo '<h1>404 - Trang không tìm thấy</h1>';
            exit;
        }
    }
}
?>