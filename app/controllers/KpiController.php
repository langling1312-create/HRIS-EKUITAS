<?php

class KpiController extends Controller
{
    public function index()
    {
        AuthMiddleware::handle();

        $kpiModel = $this->model('Kpi');

        if (AuthHelper::isHrOrAdmin()) {
            $daftar = $kpiModel->allWithUser();
            $karyawanModel = $this->model('Karyawan');
            $daftarKaryawan = $karyawanModel->allWithUser();

            $this->view('kpi/index', [
                'title'          => 'Kinerja & KPI',
                'daftar'         => $daftar,
                'daftarKaryawan' => $daftarKaryawan,
            ]);
            return;
        }

        $daftar = $kpiModel->byUser(AuthHelper::id());
        $rataRata = $kpiModel->rataRataByUser(AuthHelper::id());
        $this->view('kpi/index', [
            'title'    => 'Kinerja Saya',
            'daftar'   => $daftar,
            'rataRata' => $rataRata,
        ]);
    }

    public function tambahTarget()
    {
        AuthMiddleware::handle();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('kpi');
            return;
        }

        $kpiModel = $this->model('Kpi');

        // HR/Admin bisa menetapkan target untuk karyawan tertentu; karyawan menetapkan untuk diri sendiri
        $userId = AuthHelper::isHrOrAdmin() && $this->input('user_id')
            ? (int) $this->input('user_id')
            : AuthHelper::id();

        $periode = ValidationHelper::clean($this->input('periode', date('Y')));
        $deskripsi = ValidationHelper::clean($this->input('deskripsi_target', ''));
        $targetValue = ValidationHelper::clean($this->input('target_value', ''));

        if (empty($deskripsi)) {
            SessionHelper::flash('error', 'Deskripsi target wajib diisi.');
            $this->redirect('kpi');
            return;
        }

        $kpiModel->insert([
            'user_id'          => $userId,
            'periode'          => $periode,
            'deskripsi_target' => $deskripsi,
            'target_value'     => $targetValue,
            'status'           => 'berjalan',
        ]);

        SessionHelper::flash('success', 'Target KPI berhasil ditambahkan.');
        $this->redirect('kpi');
    }

    public function beriNilai($id)
    {
        AuthMiddleware::role(['admin', 'hr']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('kpi');
            return;
        }

        $kpiModel = $this->model('Kpi');
        $notifikasiModel = $this->model('Notifikasi');

        $nilai = (int) $this->input('nilai', 0);
        $catatan = ValidationHelper::clean($this->input('catatan_atasan', ''));

        $kpi = $kpiModel->find((int) $id);
        if (!$kpi) {
            SessionHelper::flash('error', 'Data KPI tidak ditemukan.');
            $this->redirect('kpi');
            return;
        }

        $kpiModel->update((int) $id, [
            'nilai'          => max(0, min(100, $nilai)),
            'catatan_atasan' => $catatan,
            'status'         => 'dinilai',
        ]);

        $notifikasiModel->insert(['user_id' => $kpi['user_id'], 'pesan' => 'Target KPI Anda telah dinilai oleh atasan.']);

        SessionHelper::flash('success', 'Penilaian KPI berhasil disimpan.');
        $this->redirect('kpi');
    }
}
