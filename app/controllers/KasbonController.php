<?php

class KasbonController extends Controller
{
    public function index()
    {
        AuthMiddleware::handle();

        $kasbonModel = $this->model('Kasbon');

        if (AuthHelper::isHrOrAdmin()) {
            $daftar = $kasbonModel->allWithUser();
            $this->view('kasbon/index', [
                'title'  => 'Kasbon Karyawan',
                'daftar' => $daftar,
            ]);
            return;
        }

        $daftar = $kasbonModel->byUser(AuthHelper::id());
        $sisaAktif = $kasbonModel->totalSisaAktif(AuthHelper::id());
        $this->view('kasbon/index', [
            'title'     => 'Kasbon Saya',
            'daftar'    => $daftar,
            'sisaAktif' => $sisaAktif,
        ]);
    }

    public function ajukan()
    {
        AuthMiddleware::handle();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('kasbon');
            return;
        }

        $kasbonModel = $this->model('Kasbon');
        $notifikasiModel = $this->model('Notifikasi');

        $jumlah = (float) $this->input('jumlah', 0);
        $tenor = (int) $this->input('tenor_bulan', 1);
        $alasan = ValidationHelper::clean($this->input('alasan', ''));

        if ($jumlah <= 0 || $tenor <= 0) {
            SessionHelper::flash('error', 'Jumlah kasbon dan tenor wajib diisi dengan benar.');
            $this->redirect('kasbon');
            return;
        }

        $cicilan = round($jumlah / $tenor, 2);

        $kasbonModel->insert([
            'user_id'           => AuthHelper::id(),
            'jumlah'            => $jumlah,
            'alasan'            => $alasan,
            'tenor_bulan'       => $tenor,
            'cicilan_per_bulan' => $cicilan,
            'sisa_cicilan'      => $tenor,
            'status'            => 'pending',
        ]);

        $notifikasiModel->kirimKeRole(['hr', 'admin'], AuthHelper::name() . ' mengajukan kasbon sebesar ' . format_rupiah($jumlah) . '.');

        SessionHelper::flash('success', 'Pengajuan kasbon berhasil dikirim, menunggu persetujuan HR/Finance.');
        $this->redirect('kasbon');
    }

    public function setujui($id)
    {
        AuthMiddleware::role(['admin', 'hr']);
        $this->prosesApproval((int) $id, 'disetujui');
    }

    public function tolak($id)
    {
        AuthMiddleware::role(['admin', 'hr']);
        $this->prosesApproval((int) $id, 'ditolak');
    }

    private function prosesApproval(int $id, string $status)
    {
        $kasbonModel = $this->model('Kasbon');
        $notifikasiModel = $this->model('Notifikasi');
        $logModel = $this->model('LogAktivitas');

        $kasbon = $kasbonModel->find($id);
        if (!$kasbon) {
            SessionHelper::flash('error', 'Data kasbon tidak ditemukan.');
            $this->redirect('kasbon');
            return;
        }

        $kasbonModel->update($id, ['status' => $status]);

        $pesan = $status === 'disetujui'
            ? 'Pengajuan kasbon Anda sebesar ' . format_rupiah($kasbon['jumlah']) . ' disetujui, cicilan akan otomatis dipotong dari gaji setiap bulan.'
            : 'Pengajuan kasbon Anda ditolak.';
        $notifikasiModel->insert(['user_id' => $kasbon['user_id'], 'pesan' => $pesan]);
        $logModel->catat(AuthHelper::id(), 'Meng-' . $status . ' kasbon #' . $id);

        SessionHelper::flash('success', 'Status kasbon berhasil diperbarui.');
        $this->redirect('kasbon');
    }
}
