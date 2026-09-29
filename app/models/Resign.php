<?php
require_once __DIR__ . '/../core/Model.php';

class Resign extends Model
{
    protected string $table = 'resign';

    public function byUser(int $userId): array
    {
        return $this->query("SELECT * FROM resign WHERE user_id = ? ORDER BY created_at DESC", [$userId])->fetchAll();
    }

    public function pengajuanAktif(int $userId)
    {
        $stmt = $this->query(
            "SELECT * FROM resign WHERE user_id = ? AND status IN ('pending','diproses') ORDER BY created_at DESC LIMIT 1",
            [$userId]
        );
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function allWithUser(): array
    {
        $sql = "SELECT r.*, u.name, u.foto, u.email FROM resign r
                JOIN users u ON r.user_id = u.id
                ORDER BY FIELD(r.status,'pending','diproses','selesai','ditolak'), r.created_at DESC";
        return $this->query($sql)->fetchAll();
    }

    public function countPending(): int
    {
        $stmt = $this->query("SELECT COUNT(*) as total FROM resign WHERE status IN ('pending','diproses')");
        return (int) $stmt->fetch()['total'];
    }
}
