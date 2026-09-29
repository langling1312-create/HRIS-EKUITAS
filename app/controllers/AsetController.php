<?php

class AsetController extends Controller
{
    public function index()
    {
        AuthMiddleware::handle();

        $asetModel = $this->model('Aset');

        if (AuthHelper::isHrOrAdmin()) {
            $daftar = $asetModel->allWithUser();
            $karyawanModel = $this->model('Karyawan');
            $daftarKaryawan = $karyawanModel->allWithUser();

            $this->view('aset/index', [
                'title'             => 'Manajemen Aset',
                'daftar'            => $daftar,
                'daftarKaryawan'    => $daftarKaryawan,
                'totalTersedia'     => $asetModel->countByStatus('tersedia'),
                'totalDipinjam'     => $asetModel->countByStatus('dipinjam'),
                'totalRusak'        => $asetModel->countByStatus('rusak'),
            ]);
            return;
        }

        $daftar = $asetModel->byUser(AuthHelper::id());
        $this->view('aset/index', [
            'title'  => 'Aset Saya',
            'daftar' => $daftar,
        ]);
    }

    public function store()
    {
        AuthMiddleware::role(['admin', 'hr']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('aset');
            return;
        }

        $asetModel = $this->model('Aset');
        $kode = ValidationHelper::clean($this->input('kode_aset', ''));
        $nama = ValidationHelper::clean($this->input('nama_aset', ''));
        $kategori = ValidationHelper::clean($this->input('kategori', ''));

        if (empty($kode) || empty($nama)) {
            SessionHelper::flash('error', 'Kode dan nama aset wajib diisi.');
            $this->redirect('aset');
            return;
        }
        if ($asetModel->kodeExists($kode)) {
            SessionHelper::flash('error', 'Kode aset sudah digunakan.');
            $this->redirect('aset');
            return;
        }

        $asetModel->insert([
            'kode_aset' => $kode,
            'nama_aset' => $nama,
            'kategori'  => $kategori,
            'status'    => 'tersedia',
        ]);

        SessionHelper::flash('success', 'Aset baru berhasil ditambahkan.');
        $this->redirect('aset');
    }

    public function pinjamkan($id)
    {
        AuthMiddleware::role(['admin', 'hr']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('aset');
            return;
        }

        $asetModel = $this->model('Aset');
        $notifikasiModel = $this->model('Notifikasi');
        $userId = (int) $this->input('user_id');

        $asetModel->update((int) $id, [
            'status'         => 'dipinjam',
            'user_id'        => $userId,
            'tanggal_pinjam' => date('Y-m-d'),
            'tanggal_kembali'=> null,
        ]);

        $aset = $asetModel->find((int) $id);
        $notifikasiModel->insert(['user_id' => $userId, 'pesan' => 'Anda dipinjamkan aset: ' . $aset['nama_aset'] . ' (' . $aset['kode_aset'] . ')']);

        SessionHelper::flash('success', 'Aset berhasil dipinjamkan.');
        $this->redirect('aset');
    }

    public function kembalikan($id)
    {
        AuthMiddleware::role(['admin', 'hr']);

        $asetModel = $this->model('Aset');
        $asetModel->update((int) $id, [
            'status'          => 'tersedia',
            'user_id'         => null,
            'tanggal_kembali' => date('Y-m-d'),
        ]);

        SessionHelper::flash('success', 'Aset berhasil dikembalikan dan tersedia lagi.');
        $this->redirect('aset');
    }

    public function tandaiRusak($id)
    {
        AuthMiddleware::role(['admin', 'hr']);

        $asetModel = $this->model('Aset');
        $asetModel->update((int) $id, ['status' => 'rusak']);

        SessionHelper::flash('success', 'Status aset ditandai rusak/perbaikan.');
        $this->redirect('aset');
    }
}
