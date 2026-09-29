<?php

class RekrutmenController extends Controller
{
    public function index()
    {
        AuthMiddleware::role(['admin', 'hr']);

        $lowonganModel = $this->model('Lowongan');
        $pelamarModel = $this->model('Pelamar');
        $departemenModel = $this->model('Departemen');

        $daftarLowongan = $lowonganModel->allWithDept();
        $daftarPelamar = $pelamarModel->allWithLowongan();
        $daftarDepartemen = $departemenModel->all('nama ASC');

        $this->view('rekrutmen/index', [
            'title'            => 'Rekrutmen',
            'daftarLowongan'   => $daftarLowongan,
            'daftarPelamar'    => $daftarPelamar,
            'daftarDepartemen' => $daftarDepartemen,
            'pelamarBaru'      => $pelamarModel->countBaru(),
        ]);
    }

    public function storeLowongan()
    {
        AuthMiddleware::role(['admin', 'hr']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('rekrutmen');
            return;
        }

        $lowonganModel = $this->model('Lowongan');
        $judul = ValidationHelper::clean($this->input('judul', ''));
        $departemenId = $this->input('departemen_id') ?: null;
        $deskripsi = ValidationHelper::clean($this->input('deskripsi', ''));

        if (empty($judul)) {
            SessionHelper::flash('error', 'Judul lowongan wajib diisi.');
            $this->redirect('rekrutmen');
            return;
        }

        $lowonganModel->insert([
            'judul'         => $judul,
            'departemen_id' => $departemenId,
            'deskripsi'     => $deskripsi,
            'status'        => 'buka',
        ]);

        SessionHelper::flash('success', 'Lowongan baru berhasil dibuka.');
        $this->redirect('rekrutmen');
    }

    public function tutupLowongan($id)
    {
        AuthMiddleware::role(['admin', 'hr']);
        $lowonganModel = $this->model('Lowongan');
        $lowonganModel->update((int) $id, ['status' => 'tutup']);
        SessionHelper::flash('success', 'Lowongan berhasil ditutup.');
        $this->redirect('rekrutmen');
    }

    public function tambahPelamar()
    {
        AuthMiddleware::role(['admin', 'hr']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('rekrutmen');
            return;
        }

        $pelamarModel = $this->model('Pelamar');
        $lowonganId = (int) $this->input('lowongan_id');
        $nama = ValidationHelper::clean($this->input('nama', ''));
        $email = ValidationHelper::clean($this->input('email', ''));
        $noHp = ValidationHelper::clean($this->input('no_hp', ''));

        if (empty($nama) || empty($email) || !$lowonganId) {
            SessionHelper::flash('error', 'Data pelamar tidak lengkap.');
            $this->redirect('rekrutmen');
            return;
        }

        $cvFile = null;
        if (!empty($_FILES['cv']['name'])) {
            $ext = strtolower(pathinfo($_FILES['cv']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['pdf', 'doc', 'docx'], true)) {
                $cvFile = 'cv_' . time() . '.' . $ext;
                move_uploaded_file($_FILES['cv']['tmp_name'], UPLOAD_PATH . 'kontrak/' . $cvFile);
            }
        }

        $pelamarModel->insert([
            'lowongan_id' => $lowonganId,
            'nama'        => $nama,
            'email'       => $email,
            'no_hp'       => $noHp,
            'cv_file'     => $cvFile,
            'status'      => 'baru',
        ]);

        SessionHelper::flash('success', 'Data pelamar berhasil ditambahkan.');
        $this->redirect('rekrutmen');
    }

    public function updateStatusPelamar($id)
    {
        AuthMiddleware::role(['admin', 'hr']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('rekrutmen');
            return;
        }

        $pelamarModel = $this->model('Pelamar');
        $status = $this->input('status', 'interview');
        $catatan = ValidationHelper::clean($this->input('catatan', ''));

        $pelamarModel->update((int) $id, ['status' => $status, 'catatan' => $catatan]);

        // Jika diterima, langsung buat akun karyawan baru (onboarding otomatis)
        if ($status === 'diterima') {
            $pelamar = $pelamarModel->find((int) $id);
            $userModel = $this->model('User');
            $karyawanModel = $this->model('Karyawan');
            $sisaCutiModel = $this->model('SisaCuti');

            if ($pelamar && !$userModel->emailExists($pelamar['email'])) {
                $userId = $userModel->insert([
                    'name'          => $pelamar['nama'],
                    'email'         => $pelamar['email'],
                    'password_hash' => password_hash('123456', PASSWORD_DEFAULT),
                    'role'          => 'karyawan',
                ]);
                $nip = 'EMP-' . str_pad((string) $userId, 4, '0', STR_PAD_LEFT);
                $karyawanModel->insert([
                    'user_id'       => $userId,
                    'nip'           => $nip,
                    'jabatan'       => 'Staff Baru',
                    'tgl_bergabung' => date('Y-m-d'),
                    'status_aktif'  => 1,
                    'gaji_pokok'    => 0,
                ]);
                $sisaCutiModel->insert(['user_id' => $userId, 'tahun' => (int) date('Y'), 'sisa_hari' => 12]);

                SessionHelper::flash('success', 'Pelamar diterima &amp; akun karyawan baru otomatis dibuat (password default: 123456).');
                $this->redirect('rekrutmen');
                return;
            }
        }

        SessionHelper::flash('success', 'Status pelamar berhasil diperbarui.');
        $this->redirect('rekrutmen');
    }
}
