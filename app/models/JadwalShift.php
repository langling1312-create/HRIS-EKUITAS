<?php
require_once __DIR__ . '/../core/Model.php';

class JadwalShift extends Model
{
    protected string $table = 'jadwal_shift';

    public function mingguan(string $tanggalMulai, string $tanggalSelesai, string $keyword = ''): array
    {
        $sql = "SELECT js.*, u.name, u.foto, s.nama_shift, s.jam_mulai, s.jam_selesai
                FROM jadwal_shift js
                JOIN users u ON js.user_id = u.id
                JOIN shift s ON js.shift_id = s.id
                WHERE js.tanggal BETWEEN ? AND ?";
        $params = [$tanggalMulai, $tanggalSelesai];
        if ($keyword !== '') {
            $sql .= " AND u.name LIKE ?";
            $params[] = "%{$keyword}%";
        }
        $sql .= " ORDER BY js.tanggal ASC, u.name ASC";
        return $this->query($sql, $params)->fetchAll();
    }

    public function byUserMingguan(int $userId, string $tanggalMulai, string $tanggalSelesai): array
    {
        $sql = "SELECT js.*, s.nama_shift, s.jam_mulai, s.jam_selesai
                FROM jadwal_shift js
                JOIN shift s ON js.shift_id = s.id
                WHERE js.user_id = ? AND js.tanggal BETWEEN ? AND ?
                ORDER BY js.tanggal ASC";
        return $this->query($sql, [$userId, $tanggalMulai, $tanggalSelesai])->fetchAll();
    }

    /**
     * Ambil jadwal shift satu karyawan pada satu tanggal tertentu (dipakai saat
     * Check-in Absensi, supaya shift yang berlaku otomatis mengikuti jadwal
     * yang sudah diatur HR/Admin di menu Shift & Roster, bukan pilihan bebas
     * dari karyawan).
     */
    public function byUserAndTanggal(int $userId, string $tanggal)
    {
        $sql = "SELECT js.*, s.nama_shift, s.jam_mulai, s.jam_selesai
                FROM jadwal_shift js
                JOIN shift s ON js.shift_id = s.id
                WHERE js.user_id = ? AND js.tanggal = ?
                LIMIT 1";
        $stmt = $this->query($sql, [$userId, $tanggal]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function setJadwal(int $userId, string $tanggal, int $shiftId): void
    {
        $stmt = $this->query("SELECT id FROM jadwal_shift WHERE user_id = ? AND tanggal = ?", [$userId, $tanggal]);
        $row = $stmt->fetch();
        if ($row) {
            $this->update((int) $row['id'], ['shift_id' => $shiftId]);
        } else {
            $this->insert(['user_id' => $userId, 'tanggal' => $tanggal, 'shift_id' => $shiftId]);
        }
    }
}
