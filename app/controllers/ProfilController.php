<?php

class ProfilController extends Controller
{
    public function index()
    {
        AuthMiddleware::handle();

        $userModel = $this->model('User');
        $karyawanModel = $this->model('Karyawan');
        $logModel = $this->model('LogAktivitas');

        $user = $userModel->find(AuthHelper::id());
        $karyawan = $karyawanModel->findByUserId(AuthHelper::id());
        $aktivitas = $logModel->byUser(AuthHelper::id(), 8);

        $this->view('profil/index', [
            'title'     => 'Profil Saya',
            'user'      => $user,
            'karyawan'  => $karyawan,
            'aktivitas' => $aktivitas,
        ]);
    }

    public function update()
    {
        AuthMiddleware::handle();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('profil');
            return;
        }

        $userModel = $this->model('User');
        $userId = AuthHelper::id();

        $data = [
            'name'   => ValidationHelper::clean($this->input('name', '')),
            'no_hp'  => ValidationHelper::clean($this->input('no_hp', '')),
            'alamat' => ValidationHelper::clean($this->input('alamat', '')),
        ];

        // Upload foto profil jika ada
        if (!empty($_FILES['foto']['name'])) {
            $uploadDir = UPLOAD_PATH . 'foto_profil/';
            $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png'], true)) {
                $filename = 'foto_' . $userId . '_' . time() . '.' . $ext;
                if (move_uploaded_file($_FILES['foto']['tmp_name'], $uploadDir . $filename)) {
                    $data['foto'] = $filename;
                }
            }
        }

        $userModel->update($userId, $data);
        SessionHelper::set('name', $data['name']);
        if (isset($data['foto'])) {
            SessionHelper::set('foto', $data['foto']);
        }

        SessionHelper::flash('success', 'Profil berhasil diperbarui.');
        $this->redirect('profil');
    }

    public function gantiPassword()
    {
        AuthMiddleware::handle();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('profil');
            return;
        }

        $userModel = $this->model('User');
        $userId = AuthHelper::id();
        $user = $userModel->find($userId);

        $passwordLama = $this->input('password_lama', '');
        $passwordBaru = $this->input('password_baru', '');
        $konfirmasi = $this->input('konfirmasi_password', '');

        if (!password_verify($passwordLama, $user['password_hash'])) {
            SessionHelper::flash('error', 'Password lama tidak sesuai.');
            $this->redirect('profil');
            return;
        }
        if (!ValidationHelper::minLength($passwordBaru, 6)) {
            SessionHelper::flash('error', 'Password baru minimal 6 karakter.');
            $this->redirect('profil');
            return;
        }
        if ($passwordBaru !== $konfirmasi) {
            SessionHelper::flash('error', 'Konfirmasi password baru tidak cocok.');
            $this->redirect('profil');
            return;
        }

        $userModel->update($userId, ['password_hash' => password_hash($passwordBaru, PASSWORD_DEFAULT)]);
        SessionHelper::flash('success', 'Password berhasil diubah.');
        $this->redirect('profil');
    }
}
