<?php
require_once __DIR__ . '/../core/Model.php';

class Absensi extends Model
{
    protected string $table = 'absensi';

    public function absenHariIni(int $userId)
    {
        $stmt = $this->query(
            "SELECT * FROM absensi WHERE user_id = ? AND tanggal = CURDATE() LIMIT 1",
            [$userId]
        );
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function riwayat(int $userId, int $limit = 30): array
    {
        $stmt = $this->query(
            "SELECT * FROM absensi WHERE user_id = ? ORDER BY tanggal DESC LIMIT {$limit}",
            [$userId]
        );
        return $stmt->fetchAll();
    }

    public function riwayatBulanan(int $userId, int $bulan, int $tahun): array
    {
        $stmt = $this->query(
            "SELECT * FROM absensi WHERE user_id = ? AND MONTH(tanggal) = ? AND YEAR(tanggal) = ? ORDER BY tanggal DESC",
            [$userId, $bulan, $tahun]
        );
        return $stmt->fetchAll();
    }

    /**
     * Rekap absensi seluruh karyawan pada tanggal tertentu (untuk HR/Admin).
     */
    public function rekapPerTanggal(string $tanggal, string $keyword = ''): array
    {
        $sql = "SELECT k.nip, u.id AS user_id, u.name, u.foto, d.nama AS departemen_nama,
                       a.id AS absensi_id,
                       a.check_in, a.check_out, a.foto_in, a.lokasi, a.status,
                       a.keterangan, a.bukti_sakit,
                       a.potongan_gaji, a.potongan_uang_makan
                FROM karyawan k
                JOIN users u ON k.user_id = u.id
                LEFT JOIN departemen d ON k.departemen_id = d.id
                LEFT JOIN absensi a ON a.user_id = u.id AND a.tanggal = ?
                WHERE k.status_aktif = TRUE";
        $params = [$tanggal];
        if ($keyword !== '') {
            $sql .= " AND (u.name LIKE ? OR k.nip LIKE ?)";
            $params[] = "%{$keyword}%";
            $params[] = "%{$keyword}%";
        }
        $sql .= " ORDER BY u.name ASC";
        return $this->query($sql, $params)->fetchAll();
    }

    /**
     * Rekap rangkuman jumlah hadir/izin/sakit/alfa per karyawan dalam satu bulan (untuk HR/Admin).
     */
    public function rekapBulananSemuaKaryawan(int $bulan, int $tahun, string $keyword = ''): array
    {
        $sql = "SELECT k.nip, u.id AS user_id, u.name, u.foto, d.nama AS departemen_nama,
                       SUM(CASE WHEN a.status = 'hadir' THEN 1 ELSE 0 END) AS total_hadir,
                       SUM(CASE WHEN a.status = 'izin' THEN 1 ELSE 0 END) AS total_izin,
                       SUM(CASE WHEN a.status = 'sakit' THEN 1 ELSE 0 END) AS total_sakit,
                       SUM(CASE WHEN a.status = 'alfa' THEN 1 ELSE 0 END) AS total_alfa
                FROM karyawan k
                JOIN users u ON k.user_id = u.id
                LEFT JOIN departemen d ON k.departemen_id = d.id
                LEFT JOIN absensi a ON a.user_id = u.id AND MONTH(a.tanggal) = ? AND YEAR(a.tanggal) = ?
                WHERE k.status_aktif = TRUE";
        $params = [$bulan, $tahun];
        if ($keyword !== '') {
            $sql .= " AND (u.name LIKE ? OR k.nip LIKE ?)";
            $params[] = "%{$keyword}%";
            $params[] = "%{$keyword}%";
        }
        $sql .= " GROUP BY k.nip, u.id, u.name, u.foto, d.nama ORDER BY u.name ASC";
        return $this->query($sql, $params)->fetchAll();
    }

    public function updateCheckout(int $userId, string $waktu, ?string $foto): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE absensi SET check_out = ?, foto_out = ? WHERE user_id = ? AND tanggal = CURDATE()"
        );
        return $stmt->execute([$waktu, $foto, $userId]);
    }

    /**
     * Ajukan Izin/Sakit untuk tanggal tertentu (default hari ini). Untuk status
     * 'sakit', bukti_sakit berisi nama file foto/scan surat keterangan dokter.
     */
    public function ajukanIzinSakit(int $userId, string $tanggal, string $status, ?string $keterangan, ?string $buktiSakit): int
    {
        return $this->insert([
            'user_id'     => $userId,
            'tanggal'     => $tanggal,
            'status'      => $status,
            'keterangan'  => $keterangan,
            'bukti_sakit' => $buktiSakit,
        ]);
    }

    public function byUserAndTanggal(int $userId, string $tanggal)
    {
        $stmt = $this->query(
            "SELECT * FROM absensi WHERE user_id = ? AND tanggal = ? LIMIT 1",
            [$userId, $tanggal]
        );
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function hadirHariIni(): int
    {
        $stmt = $this->query(
            "SELECT COUNT(*) as total FROM absensi WHERE tanggal = CURDATE() AND status = 'hadir'"
        );
        return (int) $stmt->fetch()['total'];
    }

    public function hitungHadirBulanIni(int $userId, int $bulan, int $tahun): int
    {
        $stmt = $this->query(
            "SELECT COUNT(*) as total FROM absensi
             WHERE user_id = ? AND MONTH(tanggal) = ? AND YEAR(tanggal) = ? AND status = 'hadir'",
            [$userId, $bulan, $tahun]
        );
        return (int) $stmt->fetch()['total'];
    }

    public function rekapPerStatus(?int $bulan = null, ?int $tahun = null): array
    {
        $sql = "SELECT status, COUNT(*) as total FROM absensi WHERE 1=1";
        $params = [];
        if ($bulan) {
            $sql .= " AND MONTH(tanggal) = ?";
            $params[] = $bulan;
        }
        if ($tahun) {
            $sql .= " AND YEAR(tanggal) = ?";
            $params[] = $tahun;
        }
        $sql .= " GROUP BY status";
        return $this->query($sql, $params)->fetchAll();
    }
}