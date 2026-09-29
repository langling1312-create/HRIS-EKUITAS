<?php
require_once __DIR__ . '/../core/Model.php';

class Cuti extends Model
{
    protected string $table = 'cuti';

    public function byUser(int $userId): array
    {
        return $this->query("SELECT * FROM cuti WHERE user_id = ? ORDER BY created_at DESC", [$userId])->fetchAll();
    }

    public function allWithUser(): array
    {
        // Tambahkan c.* agar kolom surat_sakit ikut terambil ke halaman HR/Admin
        $sql = "SELECT c.*, u.name, u.foto FROM cuti c
                JOIN users u ON c.user_id = u.id
                ORDER BY FIELD(c.status, 'pending', 'disetujui', 'ditolak'), c.created_at DESC";
        return $this->query($sql)->fetchAll();
    }

    public function countPending(): int
    {
        $stmt = $this->query("SELECT COUNT(*) as total FROM cuti WHERE status = 'pending'");
        return (int) $stmt->fetch()['total'];
    }

    /**
     * Antrean cuti yang perlu diputuskan oleh seorang Kepala Unit / Pimpinan
     * Unit: dia adalah pejabat yang ditunjuk (kepala_unit_id/pimpinan_unit_id)
     * DAN pengajuan itu sedang berada tepat di tahapnya.
     */
    public function queueForApproval(int $userId): array
    {
        $sql = "SELECT c.*, u.name, u.foto FROM cuti c
                JOIN users u ON c.user_id = u.id
                WHERE c.status = 'pending' AND (
                    (c.tahap_sekarang = 'kepala_unit' AND c.kepala_unit_id = ?)
                    OR (c.tahap_sekarang = 'pimpinan_unit' AND c.pimpinan_unit_id = ?)
                )
                ORDER BY c.created_at ASC";
        return $this->query($sql, [$userId, $userId])->fetchAll();
    }

    public function countQueueForApprover(int $userId): int
    {
        $sql = "SELECT COUNT(*) as total FROM cuti c
                WHERE c.status = 'pending' AND (
                    (c.tahap_sekarang = 'kepala_unit' AND c.kepala_unit_id = ?)
                    OR (c.tahap_sekarang = 'pimpinan_unit' AND c.pimpinan_unit_id = ?)
                )";
        $stmt = $this->query($sql, [$userId, $userId]);
        return (int) $stmt->fetch()['total'];
    }

    /**
     * Riwayat semua pengajuan cuti dari karyawan di unit yang diampu seorang
     * Kepala Unit / Pimpinan Unit (untuk konteks, bukan untuk aksi approval).
     */
    public function riwayatUnit(array $departemenIds): array
    {
        if (empty($departemenIds)) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($departemenIds), '?'));
        $sql = "SELECT c.*, u.name, u.foto FROM cuti c
                JOIN users u ON c.user_id = u.id
                JOIN karyawan k ON k.user_id = u.id
                WHERE k.departemen_id IN ({$placeholders})
                ORDER BY FIELD(c.status, 'pending', 'disetujui', 'ditolak'), c.created_at DESC";
        return $this->query($sql, $departemenIds)->fetchAll();
    }
    
    public function cekBentrok(int $userId, string $mulai, string $selesai): bool
    {
        $sql = "SELECT * FROM cuti
                WHERE user_id = ? AND status != 'ditolak'
                AND ((tanggal_mulai BETWEEN ? AND ?) OR (tanggal_selesai BETWEEN ? AND ?)
                     OR (? BETWEEN tanggal_mulai AND tanggal_selesai))";
        $stmt = $this->query($sql, [$userId, $mulai, $selesai, $mulai, $selesai, $mulai]);
        return (bool) $stmt->fetch();
    }

    public function jumlahHari(int $id): int
    {
        $stmt = $this->query(
            "SELECT DATEDIFF(tanggal_selesai, tanggal_mulai) + 1 AS jumlah FROM cuti WHERE id = ?",
            [$id]
        );
        $row = $stmt->fetch();
        return $row ? (int) $row['jumlah'] : 0;
    }

    /**
     * Detail lengkap satu pengajuan cuti untuk dicetak sebagai Surat
     * Permohonan Cuti resmi: data pemohon, departemen, serta nama pejabat
     * Kepala Unit & Pimpinan Unit (untuk kolom tanda tangan di surat).
     */
    public function findDetailSurat(int $id)
    {
        $sql = "SELECT c.*, u.name, u.foto,
                       k.nip, k.jabatan, d.nama AS departemen_nama,
                       ku.name AS kepala_unit_name,
                       pu.name AS pimpinan_unit_name
                FROM cuti c
                JOIN users u ON c.user_id = u.id
                LEFT JOIN karyawan k ON k.user_id = u.id
                LEFT JOIN departemen d ON k.departemen_id = d.id
                LEFT JOIN users ku ON ku.id = c.kepala_unit_id
                LEFT JOIN users pu ON pu.id = c.pimpinan_unit_id
                WHERE c.id = ?
                LIMIT 1";
        $stmt = $this->query($sql, [$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}