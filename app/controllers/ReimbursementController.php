<?php

class ReimbursementController extends Controller
{
    public function index()
    {
        AuthMiddleware::handle();

        $reimbursementModel = $this->model('Reimbursement');

        if (AuthHelper::isHrOrAdmin()) {
            $daftar = $reimbursementModel->allWithUser();
            $this->view('reimbursement/index', [
                'title'  => 'Reimbursement & Klaim',
                'daftar' => $daftar,
            ]);
            return;
        }

        $daftar = $reimbursementModel->byUser(AuthHelper::id());
        $this->view('reimbursement/index', [
            'title'  => 'Reimbursement & Klaim',
            'daftar' => $daftar,
        ]);
    }

    public function ajukan()
    {
        AuthMiddleware::handle();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('reimbursement');
            return;
        }

        $reimbursementModel = $this->model('Reimbursement');
        $notifikasiModel = $this->model('Notifikasi');

        $jenis = ValidationHelper::clean($this->input('jenis', ''));
        $jumlah = (float) $this->input('jumlah', 0);
        $deskripsi = ValidationHelper::clean($this->input('deskripsi', ''));

        if (empty($jenis) || $jumlah <= 0) {
            SessionHelper::flash('error', 'Jenis klaim dan jumlah wajib diisi dengan benar.');
            $this->redirect('reimbursement');
            return;
        }

        $buktiFile = null;
        if (!empty($_FILES['bukti']['name'])) {
            $ext = strtolower(pathinfo($_FILES['bukti']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'pdf'], true)) {
                $buktiFile = 'bukti_' . AuthHelper::id() . '_' . time() . '.' . $ext;
                move_uploaded_file($_FILES['bukti']['tmp_name'], UPLOAD_PATH . 'kontrak/' . $buktiFile);
            }
        }

        $reimbursementModel->insert([
            'user_id'    => AuthHelper::id(),
            'jenis'      => $jenis,
            'jumlah'     => $jumlah,
            'deskripsi'  => $deskripsi,
            'bukti_file' => $buktiFile,
            'status'     => 'pending',
        ]);

        $notifikasiModel->kirimKeRole(['hr', 'admin'], AuthHelper::name() . ' mengajukan klaim reimbursement ' . $jenis . '.');

        SessionHelper::flash('success', 'Pengajuan klaim berhasil dikirim, menunggu persetujuan.');
        $this->redirect('reimbursement');
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
        $reimbursementModel = $this->model('Reimbursement');
        $notifikasiModel = $this->model('Notifikasi');
        $logModel = $this->model('LogAktivitas');

        $klaim = $reimbursementModel->find($id);
        if (!$klaim) {
            SessionHelper::flash('error', 'Data klaim tidak ditemukan.');
            $this->redirect('reimbursement');
            return;
        }

        $catatan = ValidationHelper::clean($this->input('catatan', ''));
        $reimbursementModel->update($id, ['status' => $status, 'catatan_approval' => $catatan]);

        $pesan = $status === 'disetujui'
            ? 'Klaim reimbursement Anda sebesar ' . format_rupiah($klaim['jumlah']) . ' disetujui.'
            : 'Klaim reimbursement Anda ditolak.';
        $notifikasiModel->insert(['user_id' => $klaim['user_id'], 'pesan' => $pesan]);
        $logModel->catat(AuthHelper::id(), 'Meng-' . $status . ' klaim reimbursement #' . $id);

        SessionHelper::flash('success', 'Status klaim berhasil diperbarui.');
        $this->redirect('reimbursement');
    }
}
