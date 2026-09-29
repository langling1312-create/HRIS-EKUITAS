<?php

class PengumumanController extends Controller
{
    public function index()
    {
        AuthMiddleware::handle();

        $pengumumanModel = $this->model('Pengumuman');
        $pollingModel = $this->model('Polling');

        $daftarPengumuman = $pengumumanModel->terbaru(20);
        $daftarPolling = $pollingModel->allWithOpsi();

        // Tandai polling mana yang sudah divote oleh user saat ini
        foreach ($daftarPolling as &$p) {
            $p['sudah_vote'] = $pollingModel->sudahVote((int) $p['id'], AuthHelper::id());
        }

        $this->view('pengumuman/index', [
            'title'            => 'Pengumuman & Polling',
            'daftarPengumuman' => $daftarPengumuman,
            'daftarPolling'    => $daftarPolling,
        ]);
    }

    public function storePengumuman()
    {
        AuthMiddleware::role(['admin', 'hr']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('pengumuman');
            return;
        }

        $pengumumanModel = $this->model('Pengumuman');
        $notifikasiModel = $this->model('Notifikasi');

        $judul = ValidationHelper::clean($this->input('judul', ''));
        $isi = ValidationHelper::clean($this->input('isi', ''));

        if (empty($judul) || empty($isi)) {
            SessionHelper::flash('error', 'Judul dan isi pengumuman wajib diisi.');
            $this->redirect('pengumuman');
            return;
        }

        $pengumumanModel->insert([
            'judul'       => $judul,
            'isi'         => $isi,
            'dibuat_oleh' => AuthHelper::id(),
        ]);

        $notifikasiModel->kirimKeSemuaKaryawan('Pengumuman baru: ' . $judul);

        SessionHelper::flash('success', 'Pengumuman berhasil dipublikasikan.');
        $this->redirect('pengumuman');
    }

    public function storePolling()
    {
        AuthMiddleware::role(['admin', 'hr']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('pengumuman');
            return;
        }

        $pollingModel = $this->model('Polling');
        $opsiModel = $this->model('PollingOpsi');

        $pertanyaan = ValidationHelper::clean($this->input('pertanyaan', ''));
        $opsiList = array_filter(array_map('trim', $_POST['opsi'] ?? []));

        if (empty($pertanyaan) || count($opsiList) < 2) {
            SessionHelper::flash('error', 'Pertanyaan dan minimal 2 opsi jawaban wajib diisi.');
            $this->redirect('pengumuman');
            return;
        }

        $pollingId = $pollingModel->insert([
            'pertanyaan'  => $pertanyaan,
            'status'      => 'aktif',
            'dibuat_oleh' => AuthHelper::id(),
        ]);

        foreach ($opsiList as $opsi) {
            $opsiModel->insert(['polling_id' => $pollingId, 'opsi_text' => ValidationHelper::clean($opsi)]);
        }

        SessionHelper::flash('success', 'Polling baru berhasil dibuat.');
        $this->redirect('pengumuman');
    }

    public function vote($pollingId)
    {
        AuthMiddleware::handle();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('pengumuman');
            return;
        }

        $pollingModel = $this->model('Polling');
        $voteModel = $this->model('PollingVote');

        $opsiId = (int) $this->input('opsi_id');
        $pollingId = (int) $pollingId;

        if ($pollingModel->sudahVote($pollingId, AuthHelper::id())) {
            SessionHelper::flash('error', 'Anda sudah memberikan suara pada polling ini.');
            $this->redirect('pengumuman');
            return;
        }

        $voteModel->insert([
            'polling_id' => $pollingId,
            'opsi_id'    => $opsiId,
            'user_id'    => AuthHelper::id(),
        ]);

        SessionHelper::flash('success', 'Terima kasih, suara Anda berhasil disimpan.');
        $this->redirect('pengumuman');
    }

    public function tutupPolling($id)
    {
        AuthMiddleware::role(['admin', 'hr']);
        $pollingModel = $this->model('Polling');
        $pollingModel->update((int) $id, ['status' => 'ditutup']);
        SessionHelper::flash('success', 'Polling berhasil ditutup.');
        $this->redirect('pengumuman');
    }
}
