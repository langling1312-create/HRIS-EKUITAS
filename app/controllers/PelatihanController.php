<?php

class PelatihanController extends Controller
{
    public function index()
    {
        AuthMiddleware::handle();

        $pelatihanModel = $this->model('Pelatihan');
        $pesertaModel = $this->model('PesertaPelatihan');

        if (AuthHelper::isHrOrAdmin()) {
            $daftarPelatihan = $pelatihanModel->allWithPeserta();
            // Lampirkan daftar peserta ke masing-masing pelatihan
            foreach ($daftarPelatihan as &$p) {
                $p['peserta'] = $pesertaModel->byPelatihan((int) $p['id']);
            }
            $karyawanModel = $this->model('Karyawan');
            $this->view('pelatihan/index', [
                'title'            => 'Pelatihan & Pengembangan',
                'daftarPelatihan'  => $daftarPelatihan,
                'daftarKaryawan'   => $karyawanModel->allWithUser(),
            ]);
            return;
        }

        $daftarPelatihan = $pelatihanModel->allWithPeserta();
        $pelatihanSaya = $pesertaModel->byUser(AuthHelper::id());
        $idTerdaftar = array_column($pelatihanSaya, 'pelatihan_id');

        $this->view('pelatihan/index', [
            'title'           => 'Pelatihan & Pengembangan',
            'daftarPelatihan' => $daftarPelatihan,
            'pelatihanSaya'   => $pelatihanSaya,
            'idTerdaftar'     => $idTerdaftar,
        ]);
    }

    public function store()
    {
        AuthMiddleware::role(['admin', 'hr']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('pelatihan');
            return;
        }

        $pelatihanModel = $this->model('Pelatihan');
        $judul = ValidationHelper::clean($this->input('judul', ''));
        $deskripsi = ValidationHelper::clean($this->input('deskripsi', ''));
        $mulai = $this->input('tanggal_mulai');
        $selesai = $this->input('tanggal_selesai');

        if (empty($judul)) {
            SessionHelper::flash('error', 'Judul pelatihan wajib diisi.');
            $this->redirect('pelatihan');
            return;
        }

        $materiFile = null;
        if (!empty($_FILES['materi']['name'])) {
            $ext = strtolower(pathinfo($_FILES['materi']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['pdf', 'doc', 'docx', 'ppt', 'pptx'], true)) {
                $materiFile = 'materi_' . time() . '.' . $ext;
                move_uploaded_file($_FILES['materi']['tmp_name'], UPLOAD_PATH . 'kontrak/' . $materiFile);
            }
        }

        $pelatihanModel->insert([
            'judul'           => $judul,
            'deskripsi'       => $deskripsi,
            'tanggal_mulai'   => $mulai ?: null,
            'tanggal_selesai' => $selesai ?: null,
            'materi_file'     => $materiFile,
            'dibuat_oleh'     => AuthHelper::id(),
        ]);

        SessionHelper::flash('success', 'Pelatihan baru berhasil dibuat.');
        $this->redirect('pelatihan');
    }

    public function daftar($id)
    {
        AuthMiddleware::handle();

        $pesertaModel = $this->model('PesertaPelatihan');
        $pelatihanId = (int) $id;

        if ($pesertaModel->sudahDaftar($pelatihanId, AuthHelper::id())) {
            SessionHelper::flash('error', 'Anda sudah terdaftar pada pelatihan ini.');
            $this->redirect('pelatihan');
            return;
        }

        $pesertaModel->insert([
            'pelatihan_id' => $pelatihanId,
            'user_id'      => AuthHelper::id(),
            'status'       => 'terdaftar',
        ]);

        SessionHelper::flash('success', 'Berhasil mendaftar pelatihan.');
        $this->redirect('pelatihan');
    }

    public function tandaiSelesai($id)
    {
        AuthMiddleware::role(['admin', 'hr']);

        $pesertaModel = $this->model('PesertaPelatihan');
        $pesertaModel->update((int) $id, ['status' => 'selesai']);

        SessionHelper::flash('success', 'Peserta ditandai telah menyelesaikan pelatihan.');
        $this->redirect('pelatihan');
    }
}
