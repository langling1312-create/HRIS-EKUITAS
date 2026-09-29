<?php
require_once __DIR__ . '/../core/Model.php';

class Payroll extends Model
{
    protected string $table = 'payroll';

    public function byBulanTahun(int $bulan, int $tahun): array
    {
        $sql = "SELECT p.*, u.name, k.nip,
                       (SELECT COALESCE(SUM(a.potongan_gaji + a.potongan_uang_makan), 0) 
                        FROM absensi a 
                        WHERE a.user_id = p.user_id 
                          AND MONTH(a.tanggal) = ? 
                          AND YEAR(a.tanggal) = ?) AS total_potongan_absen
                FROM payroll p
                JOIN users u ON p.user_id = u.id
                LEFT JOIN karyawan k ON k.user_id = u.id
                WHERE p.bulan = ? AND p.tahun = ?
                ORDER BY u.name ASC";
        
        $rows = $this->query($sql, [$bulan, $tahun, $bulan, $tahun])->fetchAll();

        // Menyesuaikan nilai potongan dan total bersih agar sinkron dengan absensi
        foreach ($rows as &$row) {
            $row['potongan'] = (float) $row['total_potongan_absen'];
            $row['total'] = ((float) ($row['gaji_pokok'] ?? 0)) + ((float) ($row['tunjangan'] ?? 0)) - $row['potongan'];
        }

        return $rows;
    }

    public function byUser(int $userId, ?int $bulan = null, ?int $tahun = null): array
    {
        $sql = "SELECT * FROM payroll WHERE user_id = ?";
        $params = [$userId];
        if ($bulan) {
            $sql .= " AND bulan = ?";
            $params[] = $bulan;
        }
        if ($tahun) {
            $sql .= " AND tahun = ?";
            $params[] = $tahun;
        }
        $sql .= " ORDER BY tahun DESC, bulan DESC";
        return $this->query($sql, $params)->fetchAll();
    }

    public function sudahDiproses(int $userId, int $bulan, int $tahun): bool
    {
        $stmt = $this->query(
            "SELECT id FROM payroll WHERE user_id = ? AND bulan = ? AND tahun = ?",
            [$userId, $bulan, $tahun]
        );
        return (bool) $stmt->fetch();
    }

    public function totalBulanIni(int $bulan, int $tahun): float
    {
        $rows = $this->byBulanTahun($bulan, $tahun);
        $grandTotal = 0;
        foreach ($rows as $row) {
            $grandTotal += (float) ($row['total'] ?? 0);
        }
        return $grandTotal;
    }

    public function totalGajiPerDept(int $bulan, int $tahun): array
    {
        $sql = "SELECT d.nama, SUM(p.total) AS total_gaji
                FROM payroll p
                JOIN karyawan k ON p.user_id = k.user_id
                JOIN departemen d ON k.departemen_id = d.id
                WHERE p.bulan = ? AND p.tahun = ?
                GROUP BY d.id, d.nama";
        return $this->query($sql, [$bulan, $tahun])->fetchAll();
    }

    public function findDetail(int $id)
    {
        $sql = "SELECT p.*, u.name, u.email, k.nip, k.jabatan, d.nama as departemen_nama
                FROM payroll p
                JOIN users u ON p.user_id = u.id
                LEFT JOIN karyawan k ON k.user_id = u.id
                LEFT JOIN departemen d ON k.departemen_id = d.id
                WHERE p.id = ? LIMIT 1";
        $row = $this->query($sql, [$id])->fetch();
        
        if ($row) {
            $stmtAbsen = $this->query(
                "SELECT COALESCE(SUM(potongan_gaji + potongan_uang_makan), 0) as total_potongan 
                 FROM absensi 
                 WHERE user_id = ? AND MONTH(tanggal) = ? AND YEAR(tanggal) = ?",
                [$row['user_id'], $row['bulan'], $row['tahun']]
            );
            $absenData = $stmtAbsen->fetch();
            $row['potongan'] = (float) ($absenData['total_potongan'] ?? 0);
            $row['total'] = ((float) ($row['gaji_pokok'] ?? 0)) + ((float) ($row['tunjangan'] ?? 0)) - $row['potongan'];
        }

        return $row ?: null;
    }
}