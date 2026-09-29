<?php

class CutiController extends Controller
{
    public function index()
    {
        AuthMiddleware::handle();

        $cutiModel = $this->model('Cuti');
        $sisaCutiModel = $this->model('SisaCuti');

        if (AuthHelper::isHrOrAdmin()) {
            $daftarCuti = $cutiModel->allWithUser();
            $this->view('cuti/index', [
                'title'       => 'Pengajuan Cuti',
                'daftarCuti'  => $daftarCuti,
            ]);
            return;
        }

        $userId = AuthHelper::id();
        $tahun = (int) date('Y');
        $sisaCuti = $sisaCutiModel->getOrCreate($userId, $tahun);
        $daftarCuti = $cutiModel->byUser($userId);

        // Kepala Unit & Pimpinan Unit juga punya antrean cuti bawahannya
        // yang perlu mereka setujui/tolak, di atas riwayat cuti mereka sendiri.
        $antreanApproval = [];
        if (AuthHelper::isKepalaUnit() || AuthHelper::isPimpinanUnit()) {
            $antreanApproval = $cutiModel->queueForApproval($userId);
        }

        $this->view('cuti/index', [
            'title'           => 'Pengajuan Cuti',
            'sisaCuti'        => $sisaCuti,
            'daftarCuti'      => $daftarCuti,
            'antreanApproval' => $antreanApproval,
        ]);
    }

    public function ajukan()
    {
        AuthMiddleware::handle();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('cuti');
            return;
        }

        $userId = AuthHelper::id();
        $jenisCuti = $this->input('jenis_cuti', 'tahunan');
        $mulai = $this->input('tanggal_mulai', '');
        $selesai = $this->input('tanggal_selesai', '');
        $alasan = ValidationHelper::clean($this->input('alasan', ''));

        $cutiModel = $this->model('Cuti');
        $sisaCutiModel = $this->model('SisaCuti');
        $notifikasiModel = $this->model('Notifikasi');
        $karyawanModel = $this->model('Karyawan'); // Model untuk ambil data karyawan/gender

        $errors = [];

        // Validasi Dasar Tanggal
        if (empty($mulai) || empty($selesai)) {
            $errors[] = 'Tanggal mulai dan selesai wajib diisi.';
        } elseif (strtotime($mulai) < strtotime(date('Y-m-d'))) {
            $errors[] = 'Tanggal mulai tidak boleh sebelum hari ini.';
        } elseif (strtotime($selesai) < strtotime($mulai)) {
            $errors[] = 'Tanggal selesai tidak boleh sebelum tanggal mulai.';
        }

        $jumlahHari = 0;
        if (empty($errors)) {
            $jumlahHari = (strtotime($selesai) - strtotime($mulai)) / 86400 + 1;
            
            // 1. Validasi khusus Cuti Tahunan (cek sisa cuti)
            if ($jenisCuti === 'tahunan') {
                $tahun = (int) date('Y', strtotime($mulai));
                $sisaCuti = $sisaCutiModel->getOrCreate($userId, $tahun);

                if ($jumlahHari > $sisaCuti['sisa_hari']) {
                    $errors[] = 'Sisa cuti tahunan tidak mencukupi. Sisa cuti Anda: ' . $sisaCuti['sisa_hari'] . ' hari.';
                }
            }

            // 2. Validasi Cuti Spesial (Berdasarkan Gender)
            if ($jenisCuti === 'spesial') {
                $karyawan = $karyawanModel->findByUserId($userId);
                $gender = $karyawan['jenis_kelamin'] ?? 'L'; // 'L' atau 'P'

                if ($gender === 'P' && $jumlahHari > 90) {
                    $errors[] = 'Cuti spesial melahirkan untuk perempuan maksimal 90 hari.';
                } elseif ($gender === 'L' && $jumlahHari > 2) {
                    $errors[] = 'Cuti spesial untuk laki-laki maksimal 2 hari.';
                }
            }

            // 3. Validasi Cuti Khusus (Wajib 1 hari)
            if ($jenisCuti === 'khusus' && $jumlahHari != 1) {
                $errors[] = 'Cuti khusus hanya dapat diajukan selama 1 hari.';
            }

            // Cek Bentrok Jadwal Cuti
            if (empty($errors) && $cutiModel->cekBentrok($userId, $mulai, $selesai)) {
                $errors[] = 'Tanggal cuti bentrok dengan pengajuan cuti lain.';
            }
        }

        // 4. Validasi & Handle Upload Surat Sakit jika jenis cuti 'sakit'
        $suratSakitName = null;
        if ($jenisCuti === 'sakit') {
            if (!isset($_FILES['surat_sakit']) || $_FILES['surat_sakit']['error'] !== 0) {
                $errors[] = 'Surat sakit wajib diunggah untuk pengajuan cuti sakit.';
            } else {
                $file = $_FILES['surat_sakit'];
                $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $allowedExt = ['pdf', 'jpg', 'jpeg', 'png'];

                if ($file['size'] <= 0) {
                    $errors[] = 'File surat sakit yang diunggah kosong/rusak (0 byte). Silakan coba unggah ulang.';
                } elseif (!in_array($fileExt, $allowedExt)) {
                    $errors[] = 'Format file surat sakit harus PDF, JPG, JPEG, atau PNG.';
                } else {
                    $targetDir = UPLOAD_PATH . 'surat_sakit/';
                    if (!is_dir($targetDir)) {
                        mkdir($targetDir, 0777, true);
                    }

                    $suratSakitName = 'surat_sakit_' . time() . '_' . mt_rand(100, 999) . '.' . $fileExt;
                    if (!move_uploaded_file($file['tmp_name'], $targetDir . $suratSakitName)) {
                        $errors[] = 'Gagal mengunggah file surat sakit ke server.';
                    }
                }
            }
        }

        if (!empty($errors)) {
            SessionHelper::flash('error', implode('<br>', $errors));
            $this->redirect('cuti');
            return;
        }

        // Simpan ke Database. Tentukan dulu alur berjenjang (Kepala Unit ->
        // Pimpinan Unit -> HRD) berdasarkan siapa yang ditunjuk di departemen
        // karyawan ini. Tahap yang pejabatnya kosong otomatis dilewati.
        $karyawan = $karyawan ?? $karyawanModel->findByUserId($userId);
        $departemenModel = $this->model('Departemen');
        $dept = !empty($karyawan['departemen_id']) ? $departemenModel->find((int) $karyawan['departemen_id']) : null;

        $kepalaUnitId = $dept['kepala_unit_id'] ?? null;
        $pimpinanUnitId = $dept['pimpinan_unit_id'] ?? null;

        if ($kepalaUnitId) {
            $tahapSekarang = 'kepala_unit';
            $statusKepalaUnit = 'pending';
            $statusPimpinanUnit = $pimpinanUnitId ? 'pending' : 'dilewati';
        } elseif ($pimpinanUnitId) {
            $tahapSekarang = 'pimpinan_unit';
            $statusKepalaUnit = 'dilewati';
            $statusPimpinanUnit = 'pending';
        } else {
            $tahapSekarang = 'hrd';
            $statusKepalaUnit = 'dilewati';
            $statusPimpinanUnit = 'dilewati';
        }

        $cutiModel->insert([
            'user_id'              => $userId,
            'jenis_cuti'           => $jenisCuti,
            'tanggal_mulai'        => $mulai,
            'tanggal_selesai'      => $selesai,
            'alasan'               => $alasan,
            'surat_sakit'          => $suratSakitName,
            'status'               => 'pending',
            'kepala_unit_id'       => $kepalaUnitId,
            'pimpinan_unit_id'     => $pimpinanUnitId,
            'status_kepala_unit'   => $statusKepalaUnit,
            'status_pimpinan_unit' => $statusPimpinanUnit,
            'status_hrd'           => 'pending',
            'tahap_sekarang'       => $tahapSekarang,
        ]);

        if ($tahapSekarang === 'kepala_unit') {
            $notifikasiModel->insert(['user_id' => $kepalaUnitId, 'pesan' => AuthHelper::name() . ' mengajukan cuti ' . $jenisCuti . ', menunggu persetujuan Anda.']);
        } elseif ($tahapSekarang === 'pimpinan_unit') {
            $notifikasiModel->insert(['user_id' => $pimpinanUnitId, 'pesan' => AuthHelper::name() . ' mengajukan cuti ' . $jenisCuti . ', menunggu persetujuan Anda.']);
        } else {
            $notifikasiModel->kirimKeRole(['hr', 'admin'], AuthHelper::name() . ' mengajukan cuti ' . $jenisCuti . '.');
        }

        SessionHelper::flash('success', 'Pengajuan cuti berhasil dikirim, menunggu persetujuan.');
        $this->redirect('cuti');
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
        $cutiModel = $this->model('Cuti');
        $sisaCutiModel = $this->model('SisaCuti');
        $notifikasiModel = $this->model('Notifikasi');
        $logModel = $this->model('LogAktivitas');

        $cuti = $cutiModel->find($id);
        if (!$cuti) {
            SessionHelper::flash('error', 'Data cuti tidak ditemukan.');
            $this->redirect('cuti');
            return;
        }

        // HR hanya berwenang memutuskan di tahap HRD (tahap terakhir). Kalau
        // pengajuan masih menunggu Kepala Unit/Pimpinan Unit, HR belum boleh
        // memutuskan lebih dulu supaya alur berjenjangnya tidak dilompati.
        if ($cuti['tahap_sekarang'] !== 'hrd') {
            SessionHelper::flash('error', 'Pengajuan ini masih menunggu persetujuan ' . label_tahap_cuti($cuti['tahap_sekarang']) . ' sebelum bisa diputuskan HR.');
            $this->redirect('cuti');
            return;
        }

        $cutiModel->update($id, [
            'status'         => $status,
            'status_hrd'     => $status,
            'tanggal_hrd'    => date('Y-m-d H:i:s'),
            'tahap_sekarang' => 'selesai',
        ]);

        if ($status === 'disetujui') {
            // Hanya cuti bertipe 'tahunan' yang memotong jatah sisa_cuti
            if ($cuti['jenis_cuti'] === 'tahunan') {
                $jumlahHari = $cutiModel->jumlahHari($id);
                $tahun = (int) date('Y', strtotime($cuti['tanggal_mulai']));
                $sisaCutiModel->getOrCreate((int) $cuti['user_id'], $tahun);
                $sisaCutiModel->kurangi((int) $cuti['user_id'], $tahun, $jumlahHari);
            }
            // Surat permohonan cuti otomatis berubah menjadi bukti resmi ber-
            // "tanda tangan" HRD begitu status disetujui (lihat CutiController::cetak()),
            // jadi karyawan bisa langsung mengunduhnya sebagai bukti sudah di-ACC.
            $pesan = 'Pengajuan cuti Anda telah DISETUJUI oleh HRD. Surat persetujuan resmi sudah bisa diunduh/dicetak di menu Cuti & Izin (tombol "Surat").';
        } else {
            $pesan = 'Mohon maaf, pengajuan cuti Anda DITOLAK oleh HRD. Silakan cek menu Cuti & Izin untuk detail dan catatan penolakannya.';
        }

        $notifikasiModel->insert(['user_id' => $cuti['user_id'], 'pesan' => $pesan]);
        $logModel->catat(AuthHelper::id(), 'Meng-' . $status . ' cuti #' . $id);

        SessionHelper::flash('success', 'Status pengajuan cuti berhasil diperbarui.');
        $this->redirect('cuti');
    }

    public function setujuiUnit($id)
    {
        AuthMiddleware::role(['kepala_unit', 'pimpinan_unit']);
        $this->prosesApprovalUnit((int) $id, 'disetujui');
    }

    public function tolakUnit($id)
    {
        AuthMiddleware::role(['kepala_unit', 'pimpinan_unit']);
        $this->prosesApprovalUnit((int) $id, 'ditolak');
    }

    /**
     * Proses persetujuan/penolakan oleh Kepala Unit atau Pimpinan Unit sesuai
     * gilirannya. Kalau disetujui, alur otomatis lanjut ke tahap berikutnya
     * (tahap yang pejabatnya kosong otomatis dilewati); kalau ditolak,
     * pengajuan langsung selesai tanpa perlu diteruskan.
     */
    private function prosesApprovalUnit(int $id, string $keputusan)
    {
        $cutiModel = $this->model('Cuti');
        $notifikasiModel = $this->model('Notifikasi');
        $logModel = $this->model('LogAktivitas');

        $userId = AuthHelper::id();
        $role = AuthHelper::role(); // 'kepala_unit' atau 'pimpinan_unit'

        $cuti = $cutiModel->find($id);
        if (!$cuti) {
            SessionHelper::flash('error', 'Data cuti tidak ditemukan.');
            $this->redirect('cuti');
            return;
        }

        // Pastikan ini memang gilirannya dan dia adalah pejabat yang ditunjuk
        // untuk pengajuan ini (bukan Kepala/Pimpinan Unit departemen lain).
        $kolomId = $role === 'kepala_unit' ? 'kepala_unit_id' : 'pimpinan_unit_id';
        if ($cuti['tahap_sekarang'] !== $role || (int) $cuti[$kolomId] !== $userId) {
            SessionHelper::flash('error', 'Pengajuan ini bukan wewenang Anda atau sudah diproses.');
            $this->redirect('cuti');
            return;
        }

        $kolomStatus = 'status_' . $role;
        $kolomTanggal = 'tanggal_' . $role;

        if ($keputusan === 'ditolak') {
            $cutiModel->update($id, [
                $kolomStatus     => 'ditolak',
                $kolomTanggal    => date('Y-m-d H:i:s'),
                'tahap_sekarang' => 'selesai',
                'status'         => 'ditolak',
            ]);
            $pesan = 'Pengajuan cuti Anda ditolak oleh ' . label_tahap_cuti($role) . '.';
        } else {
            // Tentukan tahap berikutnya: kalau dari Kepala Unit, cek dulu ada
            // Pimpinan Unit atau tidak; kalau dari Pimpinan Unit, langsung HRD.
            if ($role === 'kepala_unit' && !empty($cuti['pimpinan_unit_id'])) {
                $tahapBerikutnya = 'pimpinan_unit';
            } else {
                $tahapBerikutnya = 'hrd';
            }

            $cutiModel->update($id, [
                $kolomStatus     => 'disetujui',
                $kolomTanggal    => date('Y-m-d H:i:s'),
                'tahap_sekarang' => $tahapBerikutnya,
            ]);

            if ($tahapBerikutnya === 'pimpinan_unit') {
                $notifikasiModel->insert(['user_id' => (int) $cuti['pimpinan_unit_id'], 'pesan' => $cuti['name'] ?? 'Karyawan' . ' mengajukan cuti, menunggu persetujuan Anda.']);
                $pesan = 'Pengajuan cuti Anda disetujui ' . label_tahap_cuti($role) . ', menunggu Pimpinan Unit.';
            } else {
                $notifikasiModel->kirimKeRole(['hr', 'admin'], 'Pengajuan cuti karyawan sudah disetujui unit, menunggu persetujuan HRD.');
                $pesan = 'Pengajuan cuti Anda disetujui ' . label_tahap_cuti($role) . ', menunggu HRD.';
            }
        }

        $notifikasiModel->insert(['user_id' => $cuti['user_id'], 'pesan' => $pesan]);
        $logModel->catat($userId, 'Meng-' . $keputusan . ' cuti #' . $id . ' sebagai ' . $role);

        SessionHelper::flash('success', 'Keputusan Anda berhasil disimpan.');
        $this->redirect('cuti');
    }

    /**
     * Surat Permohonan Cuti siap cetak/unduh (bisa "Save as PDF" dari
     * browser). Dipakai karyawan untuk mencetak surat permohonannya, dan
     * jadi bukti resmi begitu sudah disetujui (ada tanda tangan/stempel
     * Kepala Unit, Pimpinan Unit, dan HRD di dalamnya).
     */
    public function cetak($id)
    {
        AuthMiddleware::handle();

        $cutiModel = $this->model('Cuti');
        $cuti = $cutiModel->findDetailSurat((int) $id);

        if (!$cuti) {
            die('Data pengajuan cuti tidak ditemukan.');
        }

        // Yang boleh melihat surat ini: pemilik pengajuan, Kepala Unit/
        // Pimpinan Unit yang ditunjuk untuk pengajuan ini, atau HR/Admin.
        $userId = AuthHelper::id();
        $berhak = AuthHelper::isHrOrAdmin()
            || (int) $cuti['user_id'] === $userId
            || (int) ($cuti['kepala_unit_id'] ?? 0) === $userId
            || (int) ($cuti['pimpinan_unit_id'] ?? 0) === $userId;

        if (!$berhak) {
            http_response_code(403);
            die('Anda tidak berhak mengakses surat cuti ini.');
        }

        $jumlahHari = $cutiModel->jumlahHari((int) $id);

        $this->view('cuti/cetak', [
            'title'      => 'Surat Permohonan Cuti',
            'cuti'       => $cuti,
            'jumlahHari' => $jumlahHari,
        ], false);
    }
}