<?php
require_once __DIR__ . '/../core/Model.php';

class Kpi extends Model
{
    protected string $table = 'kpi';

    public function byUser(int $userId): array
    {
        return $this->query("SELECT * FROM kpi WHERE user_id = ? ORDER BY created_at DESC", [$userId])->fetchAll();
    }

    public function allWithUser(): array
    {
        $sql = "SELECT k.*, u.name, u.foto, ky.jabatan FROM kpi k
                JOIN users u ON k.user_id = u.id
                LEFT JOIN karyawan ky ON ky.user_id = u.id
                ORDER BY k.created_at DESC";
        return $this->query($sql)->fetchAll();
    }

    public function rataRataByUser(int $userId): float
    {
        $stmt = $this->query(
            "SELECT AVG(nilai) as rata FROM kpi WHERE user_id = ? AND status = 'dinilai'",
            [$userId]
        );
        $row = $stmt->fetch();
        return (float) ($row['rata'] ?? 0);
    }
}
