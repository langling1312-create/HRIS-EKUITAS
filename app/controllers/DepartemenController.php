<?php

class DepartemenController extends Controller
{
    public function index()
    {
        AuthMiddleware::role(['admin', 'hr']);

        $departemenModel = $this->model('Departemen');
        $userModel = $this->model('User');

        $daftarDepartemen = $departemenModel->allWithPejabat();
        // Kandidat pejabat approval HARUS akun dengan role yang sesuai
        // (Kepala Unit / Pimpinan Unit dibuat lewat menu Karyawan).
        $kandidatKepalaUnit = $userModel->findByRoles(['kepala_unit']);
        $kandidatPimpinanUnit = $userModel->findByRoles(['pimpinan_unit']);

        $this->view('departemen/index', [
            'title'                => 'Unit Kerja & Pejabat Approval',
            'daftarDepartemen'     => $daftarDepartemen,
            'kandidatKepalaUnit'   => $kandidatKepalaUnit,
            'kandidatPimpinanUnit' => $kandidatPimpinanUnit,
        ]);
    }

    public function store()
    {
        AuthMiddleware::role(['admin', 'hr']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('departemen');
            return;
        }

        $nama = ValidationHelper::clean($this->input('nama', ''));
        if (empty($nama)) {
            SessionHelper::flash('error', 'Nama unit/departemen wajib diisi.');
            $this->redirect('departemen');
            return;
        }

        $departemenModel = $this->model('Departemen');
        $logModel = $this->model('LogAktivitas');

        $departemenModel->insert(['nama' => $nama]);
        $logModel->catat(AuthHelper::id(), "Menambahkan unit/departemen baru: {$nama}");

        SessionHelper::flash('success', 'Unit kerja berhasil ditambahkan.');
        $this->redirect('departemen');
    }

    public function pejabat($id)
    {
        AuthMiddleware::role(['admin', 'hr']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('departemen');
            return;
        }

        $departemenModel = $this->model('Departemen');
        $userModel = $this->model('User');
        $logModel = $this->model('LogAktivitas');

        $departemen = $departemenModel->find((int) $id);
        if (!$departemen) {
            SessionHelper::flash('error', 'Unit/departemen tidak ditemukan.');
            $this->redirect('departemen');
            return;
        }

        $kepalaUnitId = $this->input('kepala_unit_id', '');
        $pimpinanUnitId = $this->input('pimpinan_unit_id', '');

        $kepalaUnitId = $kepalaUnitId !== '' ? (int) $kepalaUnitId : null;
        $pimpinanUnitId = $pimpinanUnitId !== '' ? (int) $pimpinanUnitId : null;

        if ($kepalaUnitId && $pimpinanUnitId && $kepalaUnitId === $pimpinanUnitId) {
            SessionHelper::flash('error', 'Kepala Unit dan Pimpinan Unit tidak boleh orang yang sama.');
            $this->redirect('departemen');
            return;
        }

        // Validasi: pastikan akun yang dipilih benar-benar ber-role sesuai.
        if ($kepalaUnitId) {
            $user = $userModel->find($kepalaUnitId);
            if (!$user || $user['role'] !== 'kepala_unit') {
                SessionHelper::flash('error', 'Akun yang dipilih sebagai Kepala Unit belum memiliki role Kepala Unit. Ubah role akun tersebut dulu di menu Karyawan.');
                $this->redirect('departemen');
                return;
            }
        }
        if ($pimpinanUnitId) {
            $user = $userModel->find($pimpinanUnitId);
            if (!$user || $user['role'] !== 'pimpinan_unit') {
                SessionHelper::flash('error', 'Akun yang dipilih sebagai Pimpinan Unit belum memiliki role Pimpinan Unit. Ubah role akun tersebut dulu di menu Karyawan.');
                $this->redirect('departemen');
                return;
            }
        }

        $departemenModel->setPejabat((int) $id, $kepalaUnitId, $pimpinanUnitId);
        $logModel->catat(AuthHelper::id(), "Mengatur pejabat approval unit: {$departemen['nama']}");

        SessionHelper::flash('success', 'Pejabat approval unit berhasil diperbarui.');
        $this->redirect('departemen');
    }
}
