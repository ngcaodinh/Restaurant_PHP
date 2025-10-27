<?php
namespace Core;

class BaseController
{
    protected function view(string $view, array $data = []): void
    {
        extract($data, EXTR_OVERWRITE);
        $viewFile = __DIR__ . '/../app/views/' . trim($view, '/') . '.php';
        if (!file_exists($viewFile)) {
            http_response_code(500);
            echo 'View not found: ' . htmlspecialchars($view);
            return;
        }
        include $viewFile;
    }
}

