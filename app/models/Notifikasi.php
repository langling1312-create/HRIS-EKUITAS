<?php
require_once __DIR__ . '/../core/Model.php';

class Notifikasi extends Model
{
    protected string $table = 'notifikasi';

    public function terbaru(int $userId, int $limit = 5): array
    {
        $stmt = $this->query(
            "SELECT * FROM notifikasi WHERE user_id = ? ORDER BY created_at DESC LIMIT {$limit}",
            [$userId]
        );
        return $stmt->fetchAll();
    }

    public function countBelumDibaca(int $userId): int
    {
        $stmt = $this->query(
            "SELECT COUNT(*) as total FROM notifikasi WHERE user_id = ? AND dibaca = FALSE",
            [$userId]
        );
        return (int) $stmt->fetch()['total'];
    }

    public function kirimKeRole(array $roles, string $pesan): void
    {
        $placeholders = implode(',', array_fill(0, count($roles), '?'));
        $sql = "INSERT INTO notifikasi (user_id, pesan)
                SELECT id, ? FROM users WHERE role IN ({$placeholders})";
        $this->query($sql, array_merge([$pesan], $roles));
    }

    public function kirimKeSemuaKaryawan(string $pesan): void
    {
        $sql = "INSERT INTO notifikasi (user_id, pesan)
                SELECT id, ? FROM users WHERE role = 'karyawan'";
        $this->query($sql, [$pesan]);
    }

    public function tandaiDibaca(int $userId): void
    {
        $this->query(
            "UPDATE notifikasi SET dibaca = TRUE WHERE user_id = ?",
            [$userId]
        );
    }
}