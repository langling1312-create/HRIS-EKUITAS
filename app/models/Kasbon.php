<?php
require_once __DIR__ . '/../core/Model.php';

class Kasbon extends Model
{
    protected string $table = 'kasbon';

    public function byUser(int $userId): array
    {
        return $this->query("SELECT * FROM kasbon WHERE user_id = ? ORDER BY created_at DESC", [$userId])->fetchAll();
    }

    public function allWithUser(): array
    {
        $sql = "SELECT k.*, u.name, u.foto FROM kasbon k
                JOIN users u ON k.user_id = u.id
                ORDER BY FIELD(k.status,'pending','disetujui','lunas','ditolak'), k.created_at DESC";
        return $this->query($sql)->fetchAll();
    }

    public function countPending(): int
    {
        $stmt = $this->query("SELECT COUNT(*) as total FROM kasbon WHERE status = 'pending'");
        return (int) $stmt->fetch()['total'];
    }

    public function aktifByUser(int $userId)
    {
        $stmt = $this->query(
            "SELECT * FROM kasbon WHERE user_id = ? AND status = 'disetujui' AND sisa_cicilan > 0 ORDER BY created_at ASC LIMIT 1",
            [$userId]
        );
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function totalSisaAktif(int $userId): float
    {
        $stmt = $this->query(
            "SELECT SUM(cicilan_per_bulan * sisa_cicilan) as total FROM kasbon WHERE user_id = ? AND status = 'disetujui' AND sisa_cicilan > 0",
            [$userId]
        );
        $row = $stmt->fetch();
        return (float) ($row['total'] ?? 0);
    }
}
