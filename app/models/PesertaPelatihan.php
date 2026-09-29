<?php
require_once __DIR__ . '/../core/Model.php';

class PesertaPelatihan extends Model
{
    protected string $table = 'peserta_pelatihan';

    public function byPelatihan(int $pelatihanId): array
    {
        $sql = "SELECT pp.*, u.name, u.foto FROM peserta_pelatihan pp
                JOIN users u ON pp.user_id = u.id
                WHERE pp.pelatihan_id = ? ORDER BY u.name ASC";
        return $this->query($sql, [$pelatihanId])->fetchAll();
    }

    public function byUser(int $userId): array
    {
        $sql = "SELECT pp.*, p.judul, p.deskripsi, p.tanggal_mulai, p.tanggal_selesai, p.materi_file
                FROM peserta_pelatihan pp
                JOIN pelatihan p ON pp.pelatihan_id = p.id
                WHERE pp.user_id = ? ORDER BY p.tanggal_mulai DESC";
        return $this->query($sql, [$userId])->fetchAll();
    }

    public function sudahDaftar(int $pelatihanId, int $userId): bool
    {
        $stmt = $this->query(
            "SELECT id FROM peserta_pelatihan WHERE pelatihan_id = ? AND user_id = ?",
            [$pelatihanId, $userId]
        );
        return (bool) $stmt->fetch();
    }
}
