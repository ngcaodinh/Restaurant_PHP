<?php

namespace Core;

/**
 * Class Router - Xử lý định tuyến cho ứng dụng
 *
 * Class này quản lý các routes (đường dẫn) của ứng dụng và điều hướng
 * các yêu cầu HTTP đến controller/method tương ứng.
 * Hỗ trợ cả routes tĩnh và động, cũng như tương thích ngược với các tệp PHP cũ.
 */
class Router
{
    /**
     * Mảng lưu trữ tất cả các routes đã đăng ký
     * @var array
     */
    private array $routes = [];

    /**
     * Đăng ký một route với phương thức GET
     *
     * @param string $path Đường dẫn URL
     * @param callable|array $handler Hàm xử lý hoặc mảng [Controller::class, 'method']
     * @return void
     */
    public function get(string $path, callable|array $handler): void
    {
        $this->map('GET', $path, $handler);
    }

    /**
     * Đăng ký một route với phương thức POST
     *
     * @param string $path Đường dẫn URL
     * @param callable|array $handler Hàm xử lý hoặc mảng [Controller::class, 'method']
     * @return void
     */
    public function post(string $path, callable|array $handler): void
    {
        $this->map('POST', $path, $handler);
    }

    /**
     * Đăng ký một route với phương thức HTTP tùy chỉnh
     *
     * @param string $method Phương thức HTTP (GET, POST, PUT, DELETE, etc.)
     * @param string $path Đường dẫn URL
     * @param callable|array $handler Hàm xử lý hoặc mảng [Controller::class, 'method']
     * @return void
     */
    public function map(string $method, string $path, callable|array $handler): void
    {
        $this->routes[$method][$this->normalize($path)] = $handler;
    }

    /**
     * Chuẩn hóa đường dẫn URL
     *
     * Thêm dấu / ở đầu và loại bỏ dấu / ở cuối (trừ root path)
     *
     * @param string $path Đường dẫn cần chuẩn hóa
     * @return string Đường dẫn đã được chuẩn hóa
     */
    private function normalize(string $path): string
    {
        // Thêm dấu / ở đầu nếu chưa có
        $path = '/' . ltrim($path, '/');
        // Loại bỏ dấu / ở cuối, trừ khi là root path
        return rtrim($path, '/') ?: '/';
    }

    /**
     * Điều phối yêu cầu HTTP đến handler tương ứng
     *
     * Phương thức này xử lý yêu cầu HTTP, tìm route phù hợp và gọi handler tương ứng.
     * Hỗ trợ cả routes động (với tham số) và routes tĩnh.
     * Nếu không tìm thấy route, sẽ thử tìm tệp PHP cũ để tương thích ngược.
     *
     * @param string|null $uri URI của yêu cầu (mặc định lấy từ $_SERVER)
     * @param string|null $method Phương thức HTTP (mặc định lấy từ $_SERVER)
     * @return mixed Kết quả từ handler
     */
    public function dispatch(?string $uri = null, ?string $method = null)
    {
        // Lấy URI và method từ request nếu không được cung cấp
        $uri = $uri ?? parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $method = $method ?? $_SERVER['REQUEST_METHOD'];

        // Chuẩn hóa đường dẫn và loại bỏ base path nếu có
        $path = $this->normalize($this->stripBase($uri));
        $handler = null;
        $params = [];

        // Kiểm tra các routes động (có tham số như {id})
        foreach ($this->routes[$method] ?? [] as $route => $h) {
            // Chuyển đổi route pattern thành regex
            $route_regex = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $route);
            if (preg_match('#^' . $route_regex . '$#', $path, $matches)) {
                $handler = $h;
                // Lưu các tham số từ URL
                foreach ($matches as $key => $value) {
                    if (is_string($key)) {
                        $params[$key] = $value;
                    }
                }
                break;
            }
        }

        // Nếu không tìm thấy route động, kiểm tra routes tĩnh
        if (!$handler) {
            $handler = $this->routes[$method][$path] ?? null;
        }

        // Thử tìm route không có đuôi .php để tương thích với URL cũ
        if (!$handler) {
            if (str_ends_with($path, '.php')) {
                $alt = $this->normalize(str_replace('.php', '', $path));
                $handler = $this->routes[$method][$alt] ?? null;
            }
        }

        // Tương thích ngược: nếu tồn tại tệp PHP cũ, include trực tiếp
        if (!$handler) {
            $legacyIncluded = $this->tryIncludeLegacy($path, $method);
            if ($legacyIncluded) {
                return;
            }
        }

        // Nếu không tìm thấy handler, trả về lỗi 404
        if (!$handler) {
            http_response_code(404);
            echo '404 Not Found';
            return;
        }

        // Nếu handler là mảng [Controller, method], khởi tạo controller và gọi method
        if (is_array($handler)) {
            [$class, $methodName] = $handler;
            // Một số controller cần database instance
            if ($class === 'App\Controllers\CartController' || $class === 'App\Controllers\OrderController') {
                $db = \Database::getInstance();
                $controller = new $class($db);
            } else {
                $controller = new $class();
            }
            // Lưu các tham số route vào $_GET để controller có thể truy cập
            $_GET = array_merge($_GET, $params);
            return $controller->$methodName();
        }

        // Nếu handler là callable, gọi trực tiếp
        return call_user_func($handler);
    }

    /**
     * Loại bỏ base path khỏi URI
     *
     * Nếu BASE_URL được định nghĩa, phương thức này sẽ loại bỏ phần base path
     * khỏi URI để lấy đường dẫn tương đối.
     *
     * @param string $uri URI đầy đủ
     * @return string URI sau khi loại bỏ base path
     */
    private function stripBase(string $uri): string
    {
        // Giữ tương thích với BASE_URL nếu được định nghĩa
        if (defined('BASE_URL')) {
            $basePath = parse_url(BASE_URL, PHP_URL_PATH) ?: '/';
            $basePath = rtrim($basePath, '/');
            // Nếu URI bắt đầu với base path, loại bỏ nó
            if ($basePath !== '' && str_starts_with($uri, $basePath)) {
                return substr($uri, strlen($basePath)) ?: '/';
            }
        }
        return $uri;
    }

    /**
     * Thử include tệp PHP cũ để tương thích ngược
     *
     * Phương thức này tìm kiếm và include các tệp PHP cũ nếu không tìm thấy route.
     * Có cơ chế bảo vệ chống directory traversal attack.
     *
     * @param string $path Đường dẫn cần tìm
     * @param string $method Phương thức HTTP
     * @return bool Trả về true nếu tìm thấy và include thành công, false nếu không
     */
    private function tryIncludeLegacy(string $path, string $method): bool
    {
        // Chỉ cho phép include các tệp .php trong thư mục được biết hoặc tệp root
        $candidates = [];
        $clean = ltrim($path, '/');

        // Nếu đường dẫn đã có đuôi .php
        if (str_ends_with($clean, '.php')) {
            $candidates[] = $clean;
        }
        // Thử thêm đuôi .php
        $candidates[] = $clean . '.php';

        // Ngăn chặn directory traversal attack
        $base = realpath(__DIR__ . '/..');
        foreach ($candidates as $rel) {
            $full = realpath($base . DIRECTORY_SEPARATOR . $rel);
            // Kiểm tra tệp có tồn tại và nằm trong thư mục base
            if ($full && str_starts_with($full, $base) && is_file($full)) {
                // Include tệp và dừng routing
                require $full;
                return true;
            }
        }
        return false;
    }
}
