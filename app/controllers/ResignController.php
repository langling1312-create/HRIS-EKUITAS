<?php

class ResignController extends Controller
{
    public function index()
    {
        AuthMiddleware::handle();

        $resignModel = $this->model('Resign');

        if (AuthHelper::isHrOrAdmin()) {
            $daftar = $resignModel->allWithUser();
            $this->view('resign/index', [
                'title'  => 'Resign & Offboarding',
                'daftar' => $daftar,
            ]);
            return;
        }

        $daftar = $resignModel->byUser(AuthHelper::id());
        $pengajuanAktif = $resignModel->pengajuanAktif(AuthHelper::id());
        $this->view('resign/index', [
            'title'          => 'Pengajuan Resign',
            'daftar'         => $daftar,
            'pengajuanAktif' => $pengajuanAktif,
        ]);
    }

    public function ajukan()
    {
        AuthMiddleware::handle();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('resign');
            return;
        }

        $resignModel = $this->model('Resign');
        $notifikasiModel = $this->model('Notifikasi');

        if ($resignModel->pengajuanAktif(AuthHelper::id())) {
            SessionHelper::flash('error', 'Anda masih memiliki pengajuan resign yang sedang berjalan.');
            $this->redirect('resign');
            return;
        }

        $tanggalEfektif = $this->input('tanggal_efektif');
        $alasan = ValidationHelper::clean($this->input('alasan', ''));
        $exitInterview = ValidationHelper::clean($this->input('exit_interview', ''));

        if (empty($tanggalEfektif) || empty($alasan)) {
            SessionHelper::flash('error', 'Tanggal efektif dan alasan resign wajib diisi.');
            $this->redirect('resign');
            return;
        }

        $resignModel->insert([
            'user_id'           => AuthHelper::id(),
            'tanggal_pengajuan' => date('Y-m-d'),
            'tanggal_efektif'   => $tanggalEfektif,
            'alasan'            => $alasan,
            'exit_interview'    => $exitInterview,
            'status'            => 'pending',
        ]);

        $notifikasiModel->kirimKeRole(['hr', 'admin'], AuthHelper::name() . ' mengajukan pengunduran diri (resign).');

        SessionHelper::flash('success', 'Pengajuan resign berhasil dikirim ke HRD.');
        $this->redirect('resign');
    }

    public function proses($id)
    {
        AuthMiddleware::role(['admin', 'hr']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('resign');
            return;
        }

        $resignModel = $this->model('Resign');
        $notifikasiModel = $this->model('Notifikasi');
        $logModel = $this->model('LogAktivitas');

        $status = $this->input('status', 'diproses');
        $catatan = ValidationHelper::clean($this->input('catatan_hrd', ''));

        $resign = $resignModel->find((int) $id);
        if (!$resign) {
            SessionHelper::flash('error', 'Data resign tidak ditemukan.');
            $this->redirect('resign');
            return;
        }

        $resignModel->update((int) $id, ['status' => $status, 'catatan_hrd' => $catatan]);

        if ($status === 'selesai') {
            // Nonaktifkan karyawan saat offboarding selesai
            $karyawanModel = $this->model('Karyawan');
            $karyawan = $karyawanModel->findByUserId((int) $resign['user_id']);
            if ($karyawan) {
                $karyawanModel->update((int) $karyawan['id'], ['status_aktif' => 0]);
            }
        }

        $notifikasiModel->insert(['user_id' => $resign['user_id'], 'pesan' => 'Status pengajuan resign Anda: ' . ucfirst($status) . '.']);
        $logModel->catat(AuthHelper::id(), 'Memproses resign #' . $id . ' -> ' . $status);

        SessionHelper::flash('success', 'Status proses resign berhasil diperbarui.');
        $this->redirect('resign');
    }
}
