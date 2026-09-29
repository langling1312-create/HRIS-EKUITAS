<?php

class AbsensiController extends Controller
{
    public function index()
    {
        AuthMiddleware::handle();

        // HR/Admin melihat rekap absensi seluruh karyawan, bukan absen pribadi.
        if (AuthHelper::isHrOrAdmin()) {
            $this->rekap();
            return;
        }

        $absensiModel = $this->model('Absensi');
        $jadwalModel = $this->model('JadwalShift');
        $userId = AuthHelper::id();

        $bulan = (int) $this->input('bulan', date('n'));
        $tahun = (int) $this->input('tahun', date('Y'));

        $absenHariIni = $absensiModel->absenHariIni($userId);
        $riwayat = $absensiModel->riwayatBulanan($userId, $bulan, $tahun);

        // Shift hari ini diambil dari jadwal yang sudah diatur HR/Admin di
        // menu Shift & Roster (bukan pilihan bebas dari karyawan lagi).
        $jadwalHariIni = $jadwalModel->byUserAndTanggal($userId, date('Y-m-d'));

        $this->view('absensi/index', [
            'title'          => 'Absensi',
            'absenHariIni'   => $absenHariIni,
            'riwayatAbsensi' => $riwayat,
            'bulan'          => $bulan,
            'tahun'          => $tahun,
            'jadwalHariIni'  => $jadwalHariIni,
        ]);
    }

    /**
     * Rekap Absensi untuk HR/Admin: lihat kehadiran seluruh karyawan.
     */
    public function rekap()
    {
        AuthMiddleware::role(['admin', 'hr']);

        $absensiModel = $this->model('Absensi');

        $mode = $this->input('mode', 'harian'); // harian | bulanan
        $tanggal = $this->input('tanggal', date('Y-m-d'));
        $bulan = (int) $this->input('bulan', date('n'));
        $tahun = (int) $this->input('tahun', date('Y'));
        $keyword = trim($this->input('q', ''));

        if ($mode === 'bulanan') {
            $data = $absensiModel->rekapBulananSemuaKaryawan($bulan, $tahun, $keyword);
        } else {
            $data = $absensiModel->rekapPerTanggal($tanggal, $keyword);
        }

        $totalKaryawan = count($data);
        $totalHadir = 0;
        $totalIzinSakit = 0;
        $totalAlfa = 0;
        if ($mode === 'harian') {
            foreach ($data as $d) {
                if ($d['status'] === 'hadir') $totalHadir++;
                elseif (in_array($d['status'], ['izin', 'sakit'], true)) $totalIzinSakit++;
                elseif ($d['status'] === 'alfa') $totalAlfa++; // Hanya yang benar-benar 'alfa' yang dihitung
            }
        }

        $this->view('absensi/rekap', [
            'title'          => 'Rekap Absensi',
            'mode'           => $mode,
            'data'           => $data,
            'tanggal'        => $tanggal,
            'bulan'          => $bulan,
            'tahun'          => $tahun,
            'keyword'        => $keyword,
            'totalKaryawan'  => $totalKaryawan,
            'totalHadir'     => $totalHadir,
            'totalIzinSakit' => $totalIzinSakit,
            'totalAlfa'      => $totalAlfa,
        ]);
    }

    /**
     * Rekap Absensi untuk KARYAWAN: setiap karyawan hanya bisa melihat
     * rekap absensi miliknya sendiri (tidak tercampur dengan karyawan lain).
     * HR/Admin diarahkan ke halaman rekap semua karyawan (method rekap()).
     */
    public function rekapSaya()
    {
        AuthMiddleware::handle();

        if (AuthHelper::isHrOrAdmin()) {
            $this->redirect('absensi');
            return;
        }

        $absensiModel = $this->model('Absensi');
        $userId = AuthHelper::id();

        $bulan = (int) $this->input('bulan', date('n'));
        $tahun = (int) $this->input('tahun', date('Y'));

        // riwayatBulanan() sudah difilter WHERE user_id = ? di dalam model,
        // jadi karyawan lain tidak akan pernah ikut muncul di sini.
        $riwayat = $absensiModel->riwayatBulanan($userId, $bulan, $tahun);

        $totalHadir = 0;
        $totalIzin = 0;
        $totalSakit = 0;
        $totalAlfa = 0;
        foreach ($riwayat as $r) {
            $status = strtolower(trim($r['status'] ?? ''));
            if (in_array($status, ['hadir', 'terlambat'], true)) {
                $totalHadir++;
            } elseif ($status === 'izin') {
                $totalIzin++;
            } elseif ($status === 'sakit') {
                $totalSakit++;
            } elseif ($status === 'alfa') {
                $totalAlfa++;
            }
        }

        $this->view('absensi/rekap-saya', [
            'title'      => 'Rekap Absensi',
            'riwayat'    => $riwayat,
            'bulan'      => $bulan,
            'tahun'      => $tahun,
            'totalHadir' => $totalHadir,
            'totalIzin'  => $totalIzin,
            'totalSakit' => $totalSakit,
            'totalAlfa'  => $totalAlfa,
        ]);
    }

    /**
     * Hapus satu data absensi dari halaman Rekap Absensi. Khusus role admin
     * (bukan HR) sesuai kebijakan: HR hanya boleh melihat & export rekap.
     */
    public function hapus()
    {
        AuthMiddleware::role(['admin']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('absensi');
            return;
        }

        $id = (int) $this->input('id', 0);
        $absensiModel = $this->model('Absensi');

        if ($id > 0) {
            $record = $absensiModel->find($id);
            if ($record) {
                // Hapus juga file foto terkait supaya tidak menumpuk sampah di server.
                foreach (['foto_in', 'foto_out'] as $kolomFoto) {
                    if (!empty($record[$kolomFoto])) {
                        $path = UPLOAD_PATH . 'absensi/' . $record[$kolomFoto];
                        if (is_file($path)) {
                            @unlink($path);
                        }
                    }
                }
                $absensiModel->delete($id);
                SessionHelper::flash('success', 'Data absensi berhasil dihapus.');
            } else {
                SessionHelper::flash('error', 'Data absensi tidak ditemukan.');
            }
        } else {
            SessionHelper::flash('error', 'ID absensi tidak valid.');
        }

        // Kembali ke halaman rekap dengan filter yang sama.
        $mode = $this->input('mode', 'harian');
        $tanggal = $this->input('tanggal', date('Y-m-d'));
        $bulan = (int) $this->input('bulan', date('n'));
        $tahun = (int) $this->input('tahun', date('Y'));
        $keyword = $this->input('q', '');

        $query = 'mode=' . urlencode($mode);
        if ($mode === 'bulanan') {
            $query .= '&bulan=' . $bulan . '&tahun=' . $tahun;
        } else {
            $query .= '&tanggal=' . urlencode($tanggal);
        }
        if ($keyword !== '') {
            $query .= '&q=' . urlencode($keyword);
        }

        $this->redirect('absensi?' . $query);
    }

    public function rekapExportExcel()
    {
        AuthMiddleware::role(['admin', 'hr']);

        $absensiModel = $this->model('Absensi');
        $mode = $this->input('mode', 'harian');
        $tanggal = $this->input('tanggal', date('Y-m-d'));
        $bulan = (int) $this->input('bulan', date('n'));
        $tahun = (int) $this->input('tahun', date('Y'));
        $keyword = trim($this->input('q', ''));

        header('Content-Type: application/vnd.ms-excel; charset=utf-8');

        if ($mode === 'bulanan') {
            $data = $absensiModel->rekapBulananSemuaKaryawan($bulan, $tahun, $keyword);
            header('Content-Disposition: attachment; filename="rekap_absensi_' . $bulan . '_' . $tahun . '.xls"');
            echo "<table border='1'>";
            echo "<tr><th colspan='6'>Rekap Absensi Bulanan - " . nama_bulan($bulan) . " {$tahun}</th></tr>";
            echo "<tr><th>NIP</th><th>Nama</th><th>Hadir</th><th>Izin</th><th>Sakit</th><th>Alfa</th></tr>";
            foreach ($data as $d) {
                echo "<tr><td>" . e($d['nip']) . "</td><td>" . e($d['name']) . "</td><td>" . (int) $d['total_hadir'] . "</td><td>" . (int) $d['total_izin'] . "</td><td>" . (int) $d['total_sakit'] . "</td><td>" . (int) $d['total_alfa'] . "</td></tr>";
            }
            echo "</table>";
        } else {
            $data = $absensiModel->rekapPerTanggal($tanggal, $keyword);
            header('Content-Disposition: attachment; filename="rekap_absensi_' . $tanggal . '.xls"');
            echo "<table border='1'>";
            echo "<tr><th colspan='6'>Rekap Absensi Harian - " . e(format_tanggal($tanggal)) . "</th></tr>";
            echo "<tr><th>NIP</th><th>Nama</th><th>Departemen</th><th>Jam Masuk</th><th>Jam Keluar</th><th>Status</th></tr>";
            foreach ($data as $d) {
                $statusText = 'Belum Hadir';
                if (!empty($d['status'])) {
                    $statusText = ucfirst($d['status']);
                }
                echo "<tr><td>" . e($d['nip']) . "</td><td>" . e($d['name']) . "</td><td>" . e($d['departemen_nama'] ?? '-') . "</td><td>" . e($d['check_in'] ?? '-') . "</td><td>" . e($d['check_out'] ?? '-') . "</td><td>" . e($statusText) . "</td></tr>";
            }
            echo "</table>";
        }
        exit;
    }

    public function exportExcel()
    {
        AuthMiddleware::handle();

        $absensiModel = $this->model('Absensi');
        $userId = AuthHelper::id();
        $bulan = (int) $this->input('bulan', date('n'));
        $tahun = (int) $this->input('tahun', date('Y'));
        $riwayat = $absensiModel->riwayatBulanan($userId, $bulan, $tahun);

        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="absensi_' . $bulan . '_' . $tahun . '.xls"');

        echo "<table border='1'>";
        echo "<tr><th colspan='6'>Riwayat Absensi - " . nama_bulan($bulan) . " {$tahun}</th></tr>";
        echo "<tr><th>Tanggal</th><th>Hari</th><th>Shift</th><th>Jam Masuk</th><th>Jam Keluar</th><th>Status & Potongan</th></tr>";
        foreach ($riwayat as $r) {
            echo "<tr>";
            echo "<td>" . e(format_tanggal($r['tanggal'])) . "</td>";
            echo "<td>" . e(date('l', strtotime($r['tanggal']))) . "</td>";
            echo "<td>" . e($r['shift'] ?? '-') . "</td>";
            echo "<td>" . e($r['check_in'] ?? '-') . "</td>";
            echo "<td>" . e($r['check_out'] ?? '-') . "</td>";
            echo "<td>" . e(ucfirst($r['status'] ?? 'hadir')) . (!empty($r['status']) && $r['status'] == 'terlambat' ? ' (Potongan: Rp ' . number_format($r['potongan_gaji'] ?? 0, 0, ',', '.') . ')' : '') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        exit;
    }

    private function simpanFotoBase64(string $base64, string $prefix, int $userId): ?string
    {
        if (empty($base64) || strpos($base64, 'base64,') === false) {
            return null;
        }
        [, $data] = explode('base64,', $base64);
        $data = base64_decode($data);
        if ($data === false || $data === '') {
            return null;
        }
        $filename = $prefix . '_' . $userId . '_' . time() . '.png';
        $uploadDir = UPLOAD_PATH . 'absensi/';

        // Pastikan folder tujuan benar-benar ada sebelum menyimpan. Kalau
        // folder ini sempat hilang/belum dibuat, sebelumnya sistem tetap
        // menyimpan nama file ke database walau file fisiknya gagal
        // tersimpan -> hasilnya foto tampil "patah"/hilang di halaman.
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0775, true);
        }

        $berhasil = @file_put_contents($uploadDir . $filename, $data);
        if ($berhasil === false) {
            // Simpan gagal (folder tidak bisa ditulis, disk penuh, dll).
            // Jangan kembalikan nama file supaya database TIDAK menyimpan
            // referensi ke file yang sebenarnya tidak ada.
            error_log('Gagal menyimpan foto absensi ke: ' . $uploadDir . $filename);
            return null;
        }

        return $filename;
    }

    public function checkin()
    {
        AuthMiddleware::handle();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('absensi');
            return;
        }

        $userId = AuthHelper::id();
        $absensiModel = $this->model('Absensi');
        $jadwalModel = $this->model('JadwalShift');

        if ($absensiModel->absenHariIni($userId)) {
            SessionHelper::flash('error', 'Anda sudah melakukan check-in hari ini.');
            $this->redirect('absensi');
            return;
        }

        // Shift TIDAK lagi dipilih manual oleh karyawan. Sistem otomatis
        // memakai jadwal yang sudah diatur HR/Admin di menu Shift & Roster
        // untuk karyawan ini pada tanggal hari ini.
        $jadwalHariIni = $jadwalModel->byUserAndTanggal($userId, date('Y-m-d'));
        if (!$jadwalHariIni) {
            SessionHelper::flash('error', 'Anda belum dijadwalkan shift kerja hari ini oleh HR/Admin. Silakan hubungi HR jika ini tidak sesuai.');
            $this->redirect('absensi');
            return;
        }

        $namaShift = $jadwalHariIni['nama_shift'];
        $jamMulaiShift = substr($jadwalHariIni['jam_mulai'], 0, 5); // "08:00"
        $jamSelesaiShift = substr($jadwalHariIni['jam_selesai'], 0, 5);
        $shiftTersimpan = $namaShift . ' (' . $jamMulaiShift . '-' . $jamSelesaiShift . ')';

        // VALIDASI WAKTU: Cek apakah sudah lewat 1 jam dari jam mulai shift
        $jamMulaiTimestamp = strtotime($jamMulaiShift);
        $batasWaktuTimestamp = strtotime($jamMulaiShift . ' + 1 hour');
        $jamAktualTimestamp = time();
        $jamAktual = date('H:i:s', $jamAktualTimestamp);

        $jamMasukStandar = $jamMulaiShift . ':00';
        $statusAbsen = 'hadir';
        $potonganGaji = 0;
        $menitTerlambat = 0;

        // Tarif potongan keterlambatan: Rp 10.000 per kelipatan 10 menit
        // (dibulatkan ke atas). Contoh: telat 1-10 menit = Rp 10.000,
        // telat 11-20 menit = Rp 20.000, dst.
        $tarifPotonganPerBlok = 10000;
        $menitPerBlok = 10;

        // Batas keterlambatan agar uang makan hangus (dalam menit).
        $batasMenitTelatUangMakan = 30;

        // Logika penentuan status berdasarkan jadwal shift dari HR:
        // - Lewat 1 jam dari jam mulai shift -> ALFA
        // - Lewat jam mulai shift (tapi belum 1 jam) -> TERLAMBAT
        // Potongan gaji dihitung dari jumlah menit keterlambatan aktual
        // (bukan lagi persentase gaji pokok).
        if ($jamAktualTimestamp > $jamMulaiTimestamp) {
            $menitTerlambat = (int) ceil(($jamAktualTimestamp - $jamMulaiTimestamp) / 60);
        }

        if ($jamAktualTimestamp > $batasWaktuTimestamp) {
            $statusAbsen = 'alfa';
        } elseif (strtotime($jamAktual) > strtotime($jamMasukStandar)) {
            $statusAbsen = 'terlambat';
        }

        if ($menitTerlambat > 0 && in_array($statusAbsen, ['terlambat', 'alfa'], true)) {
            $blokKeterlambatan = (int) ceil($menitTerlambat / $menitPerBlok);
            $potonganGaji = $blokKeterlambatan * $tarifPotonganPerBlok;
        }

        // Aturan uang makan: kalau terlambat lebih dari 30 menit, uang makan
        // hari itu hangus (tidak didapat sama sekali). Nominalnya diambil
        // dari Pengaturan Sistem > Uang Makan Harian (bisa diubah HR/Admin).
        $potonganUangMakan = 0;
        if ($menitTerlambat > $batasMenitTelatUangMakan) {
            $pengaturanModel = $this->model('PengaturanSistem');
            $nominalUangMakanHarian = (float) $pengaturanModel->get('uang_makan_harian', 40000);
            $potonganUangMakan = $nominalUangMakanHarian;
        }

        $foto = $this->simpanFotoBase64($this->input('foto', ''), 'in', $userId);
        $lokasi = ValidationHelper::clean($this->input('lokasi', ''));

        $absensiModel->insert([
            'user_id'             => $userId,
            'tanggal'             => date('Y-m-d'),
            'shift'               => $shiftTersimpan,
            'check_in'            => $jamAktual,
            'foto_in'             => $foto,
            'lokasi'              => $lokasi,
            'status'              => $statusAbsen,
            'potongan_gaji'       => $potonganGaji,
            'potongan_uang_makan' => $potonganUangMakan,
        ]);

        $pesanStatus = 'Check-in berhasil dicatat pukul ' . $jamAktual . ' (Status: ' . strtoupper($statusAbsen) . ').';
        if ($potonganGaji > 0) {
            $pesanStatus .= ' Telat ' . $menitTerlambat . ' menit, dikenakan potongan gaji Rp ' . number_format($potonganGaji, 0, ',', '.') . '.';
        }
        if ($potonganUangMakan > 0) {
            $pesanStatus .= ' Karena terlambat lebih dari ' . $batasMenitTelatUangMakan . ' menit, uang makan hari ini tidak didapatkan (Rp ' . number_format($potonganUangMakan, 0, ',', '.') . ').';
        }
        SessionHelper::flash('success', $pesanStatus);
        $this->redirect('absensi');
    }

    public function checkout()
    {
        AuthMiddleware::handle();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('absensi');
            return;
        }

        $userId = AuthHelper::id();
        $absensiModel = $this->model('Absensi');

        $absenHariIni = $absensiModel->absenHariIni($userId);
        if (!$absenHariIni) {
            SessionHelper::flash('error', 'Anda belum melakukan check-in hari ini.');
            $this->redirect('absensi');
            return;
        }
        if (!empty($absenHariIni['check_out'])) {
            SessionHelper::flash('error', 'Anda sudah melakukan check-out hari ini.');
            $this->redirect('absensi');
            return;
        }

        $foto = $this->simpanFotoBase64($this->input('foto', ''), 'out', $userId);
        $absensiModel->updateCheckout($userId, date('H:i:s'), $foto);

        SessionHelper::flash('success', 'Check-out berhasil pukul ' . date('H:i:s') . '.');
        $this->redirect('absensi');
    }

    public function izin()
    {
        AuthMiddleware::handle();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('absensi');
            return;
        }

        $userId = AuthHelper::id();
        $absensiModel = $this->model('Absensi');

        if ($absensiModel->absenHariIni($userId)) {
            SessionHelper::flash('error', 'Anda sudah melakukan absensi atau mengajukan izin hari ini.');
            $this->redirect('absensi');
            return;
        }

        $status = $this->input('status', 'izin'); // 'izin' atau 'sakit'
        if (!in_array($status, ['izin', 'sakit'])) {
            $status = 'izin';
        }

        $keterangan = ValidationHelper::clean($this->input('keterangan', ''));

        // Proses upload file bukti (jika ada) - Diperbaiki menjadi 'bukti_sakit'
        $namaFileBukti = null;
        if (isset($_FILES['bukti_sakit']) && $_FILES['bukti_sakit']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['bukti_sakit']['tmp_name'];
            $fileName = $_FILES['bukti_sakit']['name'];
            $fileSize = $_FILES['bukti_sakit']['size'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];
            // Tolak file kosong (0 byte) agar tidak tersimpan lampiran yang rusak/
            // tidak bisa ditampilkan ke HR (bisa terjadi kalau upload terputus).
            if ($fileSize <= 0) {
                SessionHelper::flash('error', 'File bukti yang diunggah kosong/rusak (0 byte). Silakan coba unggah ulang.');
                $this->redirect('absensi');
                return;
            }

            if (in_array($fileExtension, $allowedExtensions)) {
                $newFileName = 'izin_' . $status . '_' . $userId . '_' . time() . '.' . $fileExtension;
                $uploadDir = UPLOAD_PATH . 'absensi/';
                
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                if (move_uploaded_file($fileTmpPath, $uploadDir . $newFileName)) {
                    $namaFileBukti = $newFileName;
                }
            }
        }

        // Simpan data izin/sakit ke database lewat method khusus (keterangan & file
        // bukti disimpan di kolom keterangan/bukti_sakit, bukan lokasi/foto_in yang
        // sejatinya dipakai untuk data check-in biasa).
        $absensiModel->ajukanIzinSakit($userId, date('Y-m-d'), $status, $keterangan, $namaFileBukti);

        SessionHelper::flash('success', 'Pengajuan ' . ucfirst($status) . ' berhasil dikirim.');
        $this->redirect('absensi');
    }
}