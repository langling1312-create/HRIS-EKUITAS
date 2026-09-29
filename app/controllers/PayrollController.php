<?php

class PayrollController extends Controller
{
    /**
     * Untuk HR/Admin: daftar & proses payroll.
     * Untuk Karyawan: lihat slip gaji sendiri.
     */
    public function index()
    {
        AuthMiddleware::handle();

        $payrollModel = $this->model('Payroll');
        $bulan = (int) $this->input('bulan', date('n'));
        $tahun = (int) $this->input('tahun', date('Y'));

        if (AuthHelper::isHrOrAdmin()) {
            $karyawanModel = $this->model('Karyawan');
            $daftarPayroll = $payrollModel->byBulanTahun($bulan, $tahun);
            $daftarKaryawanAktif = $karyawanModel->allWithUser();

            $totalKaryawanAktif = 0;
            $sudahDiproses = 0;
            foreach ($daftarKaryawanAktif as $k) {
                if (!$k['status_aktif']) continue;
                $totalKaryawanAktif++;
                if ($payrollModel->sudahDiproses((int) $k['user_id'], $bulan, $tahun)) {
                    $sudahDiproses++;
                }
            }
            $belumDiproses = $totalKaryawanAktif - $sudahDiproses;
            $totalGajiBulanIni = $payrollModel->totalBulanIni($bulan, $tahun);

            $this->view('payroll/index', [
                'title'               => 'Penggajian',
                'daftarPayroll'       => $daftarPayroll,
                'daftarKaryawanAktif' => $daftarKaryawanAktif,
                'bulan'               => $bulan,
                'tahun'               => $tahun,
                'totalKaryawanAktif'  => $totalKaryawanAktif,
                'sudahDiproses'       => $sudahDiproses,
                'belumDiproses'       => $belumDiproses,
                'totalGajiBulanIni'   => $totalGajiBulanIni,
            ]);
            return;
        }

        $daftarSlip = $payrollModel->byUser(AuthHelper::id(), $bulan ?: null, $tahun ?: null);
        $totalYtd = 0;
        foreach ($payrollModel->byUser(AuthHelper::id(), null, $tahun) as $s) {
            $totalYtd += (float) $s['total'];
        }
        $this->view('payroll/slip', [
            'title'      => 'Slip Gaji',
            'daftarSlip' => $daftarSlip,
            'bulan'      => $bulan,
            'tahun'      => $tahun,
            'totalYtd'   => $totalYtd,
        ]);
    }

    public function proses()
    {
        AuthMiddleware::role(['admin', 'hr']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('payroll');
            return;
        }

        $bulan = (int) $this->input('bulan', date('n'));
        $tahun = (int) $this->input('tahun', date('Y'));

        $karyawanModel = $this->model('Karyawan');
        $payrollModel = $this->model('Payroll');
        $notifikasiModel = $this->model('Notifikasi');
        $logModel = $this->model('LogAktivitas');
        $pengaturanModel = $this->model('PengaturanSistem');
        $kasbonModel = $this->model('Kasbon');

        $persenBpjsKesehatan = (float) $pengaturanModel->get('persen_bpjs_kesehatan', 1);
        $persenBpjsJht = (float) $pengaturanModel->get('persen_bpjs_jht', 2);
        $persenPph21 = (float) $pengaturanModel->get('persen_pph21', 5);

        // Rincian tunjangan per komponen (opsional, boleh dikosongkan/0)
        $tjJabatanInput   = $_POST['tunjangan_jabatan'] ?? [];
        $tjTransportInput = $_POST['tunjangan_transport'] ?? [];
        $tjBpjsInput      = $_POST['tunjangan_bpjs'] ?? [];
        $tjSakitInput     = $_POST['tunjangan_sakit'] ?? [];
        $tjLainnyaInput   = $_POST['tunjangan_lainnya'] ?? [];
        $potonganInput    = $_POST['potongan'] ?? [];

        $karyawanAktif = $karyawanModel->allAktif();
        $diproses = 0;

        foreach ($karyawanAktif as $k) {
            $userId = (int) $k['user_id'];

            if ($payrollModel->sudahDiproses($userId, $bulan, $tahun)) {
                continue; // sudah diproses sebelumnya, lewati
            }

            $gajiPokok = (float) $k['gaji_pokok'];

            $tjJabatan   = isset($tjJabatanInput[$userId]) ? (float) $tjJabatanInput[$userId] : 0;
            $tjTransport = isset($tjTransportInput[$userId]) ? (float) $tjTransportInput[$userId] : 0;
            $tjBpjs      = isset($tjBpjsInput[$userId]) ? (float) $tjBpjsInput[$userId] : 0;
            $tjSakit     = isset($tjSakitInput[$userId]) ? (float) $tjSakitInput[$userId] : 0;
            $tjLainnya   = isset($tjLainnyaInput[$userId]) ? (float) $tjLainnyaInput[$userId] : 0;
            $tunjangan   = $tjJabatan + $tjTransport + $tjBpjs + $tjSakit + $tjLainnya;

            $potongan = isset($potonganInput[$userId]) ? (float) $potonganInput[$userId] : 0;

            // Perhitungan otomatis BPJS Kesehatan & Ketenagakerjaan (dari pengaturan sistem)
            $bpjsKesehatan = round($gajiPokok * $persenBpjsKesehatan / 100, 2);
            $bpjsKetenagakerjaan = round($gajiPokok * $persenBpjsJht / 100, 2);

            // Perhitungan sederhana PPh 21 (persentase dari gaji pokok + tunjangan)
            $pph21 = round(($gajiPokok + $tunjangan) * $persenPph21 / 100, 2);

            // Potongan cicilan kasbon aktif (jika ada)
            $potonganKasbon = 0;
            $kasbonAktif = $kasbonModel->aktifByUser($userId);
            if ($kasbonAktif) {
                $potonganKasbon = (float) $kasbonAktif['cicilan_per_bulan'];
                $sisaBaru = (int) $kasbonAktif['sisa_cicilan'] - 1;
                $kasbonModel->update((int) $kasbonAktif['id'], [
                    'sisa_cicilan' => max(0, $sisaBaru),
                    'status'       => $sisaBaru <= 0 ? 'lunas' : 'disetujui',
                ]);
            }

            $total = $gajiPokok + $tunjangan - $potongan - $bpjsKesehatan - $bpjsKetenagakerjaan - $pph21 - $potonganKasbon;

            $payrollId = $payrollModel->insert([
                'user_id'              => $userId,
                'bulan'                => $bulan,
                'tahun'                => $tahun,
                'gaji_pokok'           => $gajiPokok,
                'tunjangan'            => $tunjangan,
                'tunjangan_jabatan'    => $tjJabatan,
                'tunjangan_transport'  => $tjTransport,
                'tunjangan_bpjs'       => $tjBpjs,
                'tunjangan_sakit'      => $tjSakit,
                'tunjangan_lainnya'    => $tjLainnya,
                'potongan'             => $potongan,
                'bpjs_kesehatan'       => $bpjsKesehatan,
                'bpjs_ketenagakerjaan' => $bpjsKetenagakerjaan,
                'pph21'                => $pph21,
                'potongan_kasbon'      => $potonganKasbon,
                'total'                => max(0, $total),
            ]);

            // Slip gaji disediakan sebagai halaman cetak (print-to-PDF) agar tidak butuh
            // library composer tambahan saat dijalankan native di XAMPP.
            $payrollModel->update($payrollId, ['slip_pdf' => 'payroll/cetak/' . $payrollId]);

            $diproses++;
        }

        if ($diproses > 0) {
            $notifikasiModel->kirimKeSemuaKaryawan('Slip gaji bulan ' . nama_bulan($bulan) . ' ' . $tahun . ' sudah tersedia.');
        }

        $logModel->catat(AuthHelper::id(), "Memproses payroll bulan {$bulan}/{$tahun} untuk {$diproses} karyawan (termasuk BPJS, PPh21, kasbon)");

        SessionHelper::flash('success', "Berhasil memproses gaji untuk {$diproses} karyawan (BPJS, PPh21, dan cicilan kasbon otomatis terhitung).");
        $this->redirect('payroll?bulan=' . $bulan . '&tahun=' . $tahun);
    }

    /**
     * Form edit gaji karyawan yang sudah diproses (HR/Admin).
     * Memungkinkan HR mengubah rincian tunjangan (Jabatan, Transport,
     * BPJS, Sakit, Lainnya), potongan, BPJS, PPh21, dan cicilan kasbon.
     */
    public function edit($id)
    {
        AuthMiddleware::role(['admin', 'hr']);

        $payrollModel = $this->model('Payroll');
        $slip = $payrollModel->findDetail((int) $id);

        if (!$slip) {
            SessionHelper::flash('error', 'Data payroll tidak ditemukan.');
            $this->redirect('payroll');
            return;
        }

        $this->view('payroll/edit', [
            'title' => 'Edit Gaji Karyawan',
            'slip'  => $slip,
        ]);
    }

    /**
     * Simpan perubahan gaji dari form edit di atas dan hitung ulang total.
     */
    public function update($id)
    {
        AuthMiddleware::role(['admin', 'hr']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('payroll');
            return;
        }

        $payrollModel = $this->model('Payroll');
        $logModel = $this->model('LogAktivitas');
        $id = (int) $id;

        $existing = $payrollModel->find($id);
        if (!$existing) {
            SessionHelper::flash('error', 'Data payroll tidak ditemukan.');
            $this->redirect('payroll');
            return;
        }

        $gajiPokok   = (float) $this->input('gaji_pokok', 0);
        $tjJabatan   = (float) $this->input('tunjangan_jabatan', 0);
        $tjTransport = (float) $this->input('tunjangan_transport', 0);
        $tjBpjs      = (float) $this->input('tunjangan_bpjs', 0);
        $tjSakit     = (float) $this->input('tunjangan_sakit', 0);
        $tjLainnya   = (float) $this->input('tunjangan_lainnya', 0);
        $tunjangan   = $tjJabatan + $tjTransport + $tjBpjs + $tjSakit + $tjLainnya;

        $potongan            = (float) $this->input('potongan', 0);
        $bpjsKesehatan       = (float) $this->input('bpjs_kesehatan', 0);
        $bpjsKetenagakerjaan = (float) $this->input('bpjs_ketenagakerjaan', 0);
        $pph21               = (float) $this->input('pph21', 0);
        $potonganKasbon      = (float) $this->input('potongan_kasbon', 0);

        $total = $gajiPokok + $tunjangan - $potongan - $bpjsKesehatan - $bpjsKetenagakerjaan - $pph21 - $potonganKasbon;

        $payrollModel->update($id, [
            'gaji_pokok'           => $gajiPokok,
            'tunjangan'            => $tunjangan,
            'tunjangan_jabatan'    => $tjJabatan,
            'tunjangan_transport'  => $tjTransport,
            'tunjangan_bpjs'       => $tjBpjs,
            'tunjangan_sakit'      => $tjSakit,
            'tunjangan_lainnya'    => $tjLainnya,
            'potongan'             => $potongan,
            'bpjs_kesehatan'       => $bpjsKesehatan,
            'bpjs_ketenagakerjaan' => $bpjsKetenagakerjaan,
            'pph21'                => $pph21,
            'potongan_kasbon'      => $potonganKasbon,
            'total'                => max(0, $total),
        ]);

        $logModel->catat(AuthHelper::id(), "Mengedit gaji payroll #{$id} (bulan {$existing['bulan']}/{$existing['tahun']})");

        SessionHelper::flash('success', 'Data gaji berhasil diperbarui.');
        $this->redirect('payroll?bulan=' . $existing['bulan'] . '&tahun=' . $existing['tahun']);
    }

    /**
     * Tampilan slip gaji siap cetak (bisa "Save as PDF" langsung dari browser).
     */
    public function cetak($id)
    {
        AuthMiddleware::handle();

        $payrollModel = $this->model('Payroll');
        $absensiModel = $this->model('Absensi');
        $slip = $payrollModel->findDetail((int) $id);

        if (!$slip) {
            die('Slip gaji tidak ditemukan.');
        }

        // Karyawan/Kepala Unit/Pimpinan Unit hanya boleh melihat slip miliknya sendiri.
        if (!AuthHelper::isHrOrAdmin() && (int) $slip['user_id'] !== AuthHelper::id()) {
            http_response_code(403);
            die('Anda tidak berhak mengakses slip gaji ini.');
        }

        $hadir = $absensiModel->hitungHadirBulanIni((int) $slip['user_id'], (int) $slip['bulan'], (int) $slip['tahun']);

        $this->view('payroll/cetak', [
            'title' => 'Slip Gaji',
            'slip'  => $slip,
            'hadir' => $hadir,
        ], false);
    }
}
