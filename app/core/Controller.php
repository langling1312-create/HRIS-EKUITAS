<?php

class Controller
{
    /**
     * Render view dengan layout dashboard (sidebar + topnav) atau tanpa layout.
     */
    protected function view(string $view, array $data = [], string $layout = 'layouts/main'): void
    {
        extract($data);
        $viewFile = APP_ROOT . '/views/' . $view . '.php';
        if (!file_exists($viewFile)) {
            die("View tidak ditemukan: {$view}");
        }

        if ($layout === false || $layout === '') {
            require $viewFile;
            return;
        }

        // Konten view di-buffer lalu disuntikkan ke dalam layout
        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        require APP_ROOT . '/views/' . $layout . '.php';
    }

    protected function model(string $model): object
    {
        require_once APP_ROOT . '/models/' . $model . '.php';
        return new $model();
    }

    protected function redirect(string $path = ''): void
    {
        header('Location: ' . BASE_URL . ltrim($path, '/'));
        exit;
    }

    protected function json($data): void
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function input(string $key, $default = null)
    {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }
}
