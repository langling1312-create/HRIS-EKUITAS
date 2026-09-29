<?php

class AuthController extends Controller
{
    public function login()
    {
        if (AuthHelper::check()) {
            $this->redirect('dashboard');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = ValidationHelper::clean($this->input('email', ''));
            $password = $this->input('password', '');

            $userModel = $this->model('User');
            $user = $userModel->findByEmail($email);

            if (!$user || !password_verify($password, $user['password_hash'])) {
                SessionHelper::flash('error', 'Email atau password salah.');
                $this->redirect('auth/login');
                return;
            }

            AuthHelper::login($user);

            $logModel = $this->model('LogAktivitas');
            $logModel->catat($user['id'], 'Login ke sistem');

            SessionHelper::flash('success', 'Login berhasil. Selamat datang, ' . $user['name'] . '!');
            $this->redirect('dashboard');
            return;
        }

        $this->view('auth/login', ['title' => 'Login'], false);
    }

    public function register()
    {
        if (AuthHelper::check()) {
            $this->redirect('dashboard');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = ValidationHelper::clean($this->input('name', ''));
            $email = ValidationHelper::clean($this->input('email', ''));
            $password = $this->input('password', '');
            $confirmPassword = $this->input('confirm_password', '');

            $errors = [];

            if (empty($name)) {
                $errors[] = 'Nama wajib diisi.';
            }
            if (!ValidationHelper::isEmail($email)) {
                $errors[] = 'Format email tidak valid.';
            }
            if (!ValidationHelper::minLength($password, 6)) {
                $errors[] = 'Password minimal 6 karakter.';
            }
            if ($password !== $confirmPassword) {
                $errors[] = 'Konfirmasi password tidak cocok.';
            }

            $userModel = $this->model('User');
            if (empty($errors) && $userModel->emailExists($email)) {
                $errors[] = 'Email sudah terdaftar, silakan gunakan email lain.';
            }

            if (!empty($errors)) {
                SessionHelper::flash('error', implode('<br>', $errors));
                SessionHelper::flash('old_input', ['name' => $name, 'email' => $email]);
                $this->redirect('auth/register');
                return;
            }

            $userId = $userModel->insert([
                'name'          => $name,
                'email'         => $email,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'role'          => 'karyawan',
            ]);

            $logModel = $this->model('LogAktivitas');
            $logModel->catat($userId, 'Registrasi akun baru');

            SessionHelper::flash('success', 'Registrasi berhasil, silakan login.');
            $this->redirect('auth/login');
            return;
        }

        $this->view('auth/register', ['title' => 'Register'], false);
    }

    public function logout()
    {
        if (AuthHelper::check()) {
            $logModel = $this->model('LogAktivitas');
            $logModel->catat(AuthHelper::id(), 'Logout dari sistem');
        }
        AuthHelper::logout();
        $this->redirect('auth/login');
    }
}
