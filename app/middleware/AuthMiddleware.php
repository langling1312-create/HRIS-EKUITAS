<?php

class AuthMiddleware
{
    /**
     * Pastikan user sudah login, jika belum redirect ke halaman login.
     */
    public static function handle(): void
    {
        if (!AuthHelper::check()) {
            SessionHelper::flash('error', 'Silakan login terlebih dahulu.');
            header('Location: ' . BASE_URL . 'auth/login');
            exit;
        }
    }

    /**
     * Batasi akses hanya untuk role tertentu.
     * Contoh: AuthMiddleware::role(['admin', 'hr']);
     */
    public static function role(array $allowedRoles): void
    {
        self::handle();
        if (!in_array(AuthHelper::role(), $allowedRoles, true)) {
            http_response_code(403);
            require APP_ROOT . '/views/errors/403.php';
            exit;
        }
    }
}
