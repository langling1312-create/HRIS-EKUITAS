<?php

class Router
{
    private string $controller = 'HomeController';
    private string $action = 'index';
    private array $params = [];

    public function __construct()
    {
        $url = trim($_GET['url'] ?? '', '/');

        if ($url !== '') {
            $segments = explode('/', $url);

            if (!empty($segments[0])) {
                $controllerName = ucfirst(strtolower($segments[0])) . 'Controller';
                if (file_exists(APP_ROOT . '/controllers/' . $controllerName . '.php')) {
                    $this->controller = $controllerName;
                    array_shift($segments);
                }
            }

            if (!empty($segments[0])) {
                // Dukung URL kebab-case, contoh: ganti-password -> gantiPassword
                $this->action = lcfirst(str_replace(' ', '', ucwords(str_replace('-', ' ', $segments[0]))));
                array_shift($segments);
            }

            $this->params = $segments;
        }
    }

    public function dispatch(): void
    {
        $controllerFile = APP_ROOT . '/controllers/' . $this->controller . '.php';

        if (!file_exists($controllerFile)) {
            $this->notFound();
            return;
        }

        require_once $controllerFile;
        $controllerInstance = new $this->controller();

        if (!method_exists($controllerInstance, $this->action)) {
            $this->notFound();
            return;
        }

        call_user_func_array([$controllerInstance, $this->action], $this->params);
    }

    private function notFound(): void
    {
        // Halaman/menu yang tidak ditemukan tidak lagi menampilkan error 404.
        // Sistem otomatis mengarahkan kembali ke halaman awal (landing/dashboard).
        header('Location: ' . BASE_URL);
        exit;
    }
}
