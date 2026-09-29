<?php

class KaryawanController extends Controller
{
    public function index()
    {
        AuthMiddleware::role(['admin', 'hr']);

        $karyawanModel = $this->model('Karyawan');
        $departemenModel = $this->model('Departemen');

        $keyword = trim($this->input('q', ''));
        $daftarKaryawan = $keyword !== '' ? $karyawanModel->search($keyword) : $karyawanModel->allWithUser();
        $daftarDepartemen = $departemenModel->all('nama ASC');

        $totalKaryawan = count($karyawanModel->allWithUser());
        $karyawanAktif = 0;
        $karyawanNonAktif = 0;
        foreach ($karyawanModel->allWithUser() as $k) {
            if ($k['status_aktif']) { $karyawanAktif++; } else { $karyawanNonAktif++; }
        }
        $cutiModel = $this->model('Cuti');
        $karyawanCuti = $cutiModel->countPending();

        $this->view('karyawan/index', [
            'title'            => 'Manajemen Karyawan',
            'daftarKaryawan'   => $daftarKaryawan,
            'daftarDepartemen' => $daftarDepartemen,
            'keyword'          => $keyword,
            'totalKaryawan'    => $totalKaryawan,
            'karyawanAktif'    => $karyawanAktif,
            'karyawanNonAktif' => $karyawanNonAktif,
            'karyawanCuti'     => $karyawanCuti,
        ]);
    }

    public function store()
    {
        AuthMiddleware::role(['admin', 'hr']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('karyawan');
            return;
        }

        $userModel = $this->model('User');
        $karyawanModel = $this->model('Karyawan');
        $sisaCutiModel = $this->model('SisaCuti');
        $logModel = $this->model('LogAktivitas');

        $name = ValidationHelper::clean($this->input('name', ''));
        $email = ValidationHelper::clean($this->input('email', ''));
        $password = $this->input('password', '123456');
        $nip = ValidationHelper::clean($this->input('nip', ''));
        $jabatan = ValidationHelper::clean($this->input('jabatan', ''));
        $departemenId = $this->input('departemen_id') ?: null;
        $gajiPokok = (float) $this->input('gaji_pokok', 0);
        $tglBergabung = $this->input('tgl_bergabung') ?: date('Y-m-d');
        $jenisKelamin = $this->input('jenis_kelamin') ?: null;

        // Role akun: karyawan biasa, atau pejabat unit (Kepala Unit / Pimpinan Unit)
        // yang nantinya ditunjuk sebagai penanggung jawab approval cuti di menu Unit Kerja.
        // Hanya Admin yang boleh membuat akun HR/Admin baru lewat form ini.
        $rolesDiizinkan = ['karyawan', 'kepala_unit', 'pimpinan_unit'];
        if (AuthHelper::isAdmin()) {
            $rolesDiizinkan[] = 'hr';
            $rolesDiizinkan[] = 'admin';
        }
        $role = $this->input('role', 'karyawan');
        if (!in_array($role, $rolesDiizinkan, true)) {
            $role = 'karyawan';
        }

        $errors = [];
        if (empty($name) || empty($email) || empty($nip) || empty($jabatan)) {
            $errors[] = 'Semua field wajib diisi.';
        }
        if (empty($errors) && $userModel->emailExists($email)) {
            $errors[] = 'Email sudah digunakan.';
        }
        if (empty($errors) && $karyawanModel->nipExists($nip)) {
            $errors[] = 'NIP sudah digunakan.';
        }

        if (!empty($errors)) {
            SessionHelper::flash('error', implode('<br>', $errors));
            $this->redirect('karyawan');
            return;
        }

        $userId = $userModel->insert([
            'name'          => $name,
            'email'         => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'role'          => $role,
        ]);

        $karyawanModel->insert([
            'user_id'        => $userId,
            'nip'            => $nip,
            'jabatan'        => $jabatan,
            'departemen_id'  => $departemenId,
            'tgl_bergabung'  => $tglBergabung,
            'status_aktif'   => 1,
            'gaji_pokok'     => $gajiPokok,
            'jenis_kelamin'  => $jenisKelamin,
        ]);

        $sisaCutiModel->insert([
            'user_id'   => $userId,
            'tahun'     => (int) date('Y'),
            'sisa_hari' => 12,
        ]);

        $logModel->catat(AuthHelper::id(), "Menambahkan karyawan baru: {$name} ({$nip}) sebagai " . role_label($role));

        SessionHelper::flash('success', 'Data karyawan berhasil ditambahkan.');
        $this->redirect('karyawan');
    }

    public function update($id)
    {
        AuthMiddleware::role(['admin', 'hr']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('karyawan');
            return;
        }

        $karyawanModel = $this->model('Karyawan');
        $userModel = $this->model('User');
        $logModel = $this->model('LogAktivitas');

        $karyawan = $karyawanModel->find((int) $id);
        if (!$karyawan) {
            SessionHelper::flash('error', 'Data karyawan tidak ditemukan.');
            $this->redirect('karyawan');
            return;
        }

        $name = ValidationHelper::clean($this->input('name', ''));
        $email = ValidationHelper::clean($this->input('email', ''));
        $jabatan = ValidationHelper::clean($this->input('jabatan', ''));
        $departemenId = $this->input('departemen_id') ?: null;
        $gajiPokok = (float) $this->input('gaji_pokok', 0);
        $statusAktif = $this->input('status_aktif', '1');
        $jenisKelamin = $this->input('jenis_kelamin') ?: null;

        $rolesDiizinkan = ['karyawan', 'kepala_unit', 'pimpinan_unit'];
        if (AuthHelper::isAdmin()) {
            $rolesDiizinkan[] = 'hr';
            $rolesDiizinkan[] = 'admin';
        }
        $role = $this->input('role', null);

        $dataUser = [
            'name'  => $name,
            'email' => $email,
        ];
        if ($role !== null && in_array($role, $rolesDiizinkan, true)) {
            $dataUser['role'] = $role;
        }

        $userModel->update((int) $karyawan['user_id'], $dataUser);

        $karyawanModel->update((int) $id, [
            'jabatan'       => $jabatan,
            'departemen_id' => $departemenId,
            'gaji_pokok'    => $gajiPokok,
            'status_aktif'  => $statusAktif,
            'jenis_kelamin' => $jenisKelamin,
        ]);

        $logModel->catat(AuthHelper::id(), "Mengubah data karyawan: {$name}");

        SessionHelper::flash('success', 'Data karyawan berhasil diperbarui.');
        $this->redirect('karyawan');
    }

    public function delete($id)
    {
        AuthMiddleware::role(['admin', 'hr']);

        $karyawanModel = $this->model('Karyawan');
        $userModel = $this->model('User');
        $logModel = $this->model('LogAktivitas');

        $karyawan = $karyawanModel->find((int) $id);
        if ($karyawan) {
            // Hapus user akan cascade menghapus data karyawan terkait
            $userModel->delete((int) $karyawan['user_id']);
            $logModel->catat(AuthHelper::id(), "Menghapus data karyawan ID #{$id}");
            SessionHelper::flash('success', 'Data karyawan berhasil dihapus.');
        } else {
            SessionHelper::flash('error', 'Data karyawan tidak ditemukan.');
        }

        $this->redirect('karyawan');
    }
}
