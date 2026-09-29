<?php

class LaporanController extends Controller
{
    private function ambilData(): array
    {
        $bulan = (int) $this->input('bulan', date('n'));
        $tahun = (int) $this->input('tahun', date('Y'));
        $departemenId = $this->input('departemen_id', '');

        $departemenModel = $this->model('Departemen');
        $absensiModel = $this->model('Absensi');
        $payrollModel = $this->model('Payroll');

        $jumlahPerDept = $departemenModel->jumlahKaryawanPerDept();
        $rekapAbsensi = $absensiModel->rekapPerStatus($bulan, $tahun);
        $totalGajiPerDept = $payrollModel->totalGajiPerDept($bulan, $tahun);
        $daftarDepartemen = $departemenModel->all('nama ASC');

        return compact('bulan', 'tahun', 'departemenId', 'jumlahPerDept', 'rekapAbsensi', 'totalGajiPerDept', 'daftarDepartemen');
    }

    public function index()
    {
        AuthMiddleware::role(['admin', 'hr']);

        $data = $this->ambilData();
        $data['title'] = 'Laporan & Analitik';

        $this->view('laporan/index', $data);
    }

    /**
     * Export laporan ke Excel (.xls) menggunakan format tabel HTML native,
     * tanpa memerlukan library composer tambahan (PhpSpreadsheet) yang butuh internet.
     */
    public function exportExcel()
    {
        AuthMiddleware::role(['admin', 'hr']);

        $data = $this->ambilData();

        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="laporan_hris_' . $data['bulan'] . '_' . $data['tahun'] . '.xls"');

        echo "<table border='1'>";
        echo "<tr><th colspan='2'>Laporan HRIS - " . nama_bulan($data['bulan']) . " " . $data['tahun'] . "</th></tr>";
        echo "<tr><th colspan='2'>Jumlah Karyawan per Departemen</th></tr>";
        echo "<tr><th>Departemen</th><th>Jumlah</th></tr>";
        foreach ($data['jumlahPerDept'] as $row) {
            echo "<tr><td>" . e($row['nama']) . "</td><td>" . (int) $row['total'] . "</td></tr>";
        }
        echo "<tr><td colspan='2'></td></tr>";
        echo "<tr><th colspan='2'>Rekapitulasi Absensi</th></tr>";
        echo "<tr><th>Status</th><th>Jumlah</th></tr>";
        foreach ($data['rekapAbsensi'] as $row) {
            echo "<tr><td>" . e(ucfirst($row['status'])) . "</td><td>" . (int) $row['total'] . "</td></tr>";
        }
        echo "<tr><td colspan='2'></td></tr>";
        echo "<tr><th colspan='2'>Total Biaya Gaji per Departemen</th></tr>";
        echo "<tr><th>Departemen</th><th>Total Gaji</th></tr>";
        foreach ($data['totalGajiPerDept'] as $row) {
            echo "<tr><td>" . e($row['nama']) . "</td><td>" . number_format((float) $row['total_gaji'], 0, ',', '.') . "</td></tr>";
        }
        echo "</table>";
        exit;
    }

    /**
     * Export laporan ke tampilan cetak (bisa disimpan sebagai PDF via dialog print browser).
     */
    public function exportPdf()
    {
        AuthMiddleware::role(['admin', 'hr']);

        $data = $this->ambilData();
        $data['title'] = 'Cetak Laporan';

        $this->view('laporan/cetak', $data, false);
    }
}
