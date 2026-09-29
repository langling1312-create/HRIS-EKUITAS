<?php
require_once __DIR__ . '/../core/Model.php';

class Aset extends Model
{
    protected string $table = 'aset';

    public function allWithUser(): array
    {
        $sql = "SELECT a.*, u.name AS peminjam_nama FROM aset a
                LEFT JOIN users u ON a.user_id = u.id
                ORDER BY a.id DESC";
        return $this->query($sql)->fetchAll();
    }

    public function byUser(int $userId): array
    {
        return $this->query("SELECT * FROM aset WHERE user_id = ? ORDER BY tanggal_pinjam DESC", [$userId])->fetchAll();
    }

    public function kodeExists(string $kode, ?int $exceptId = null): bool
    {
        $sql = "SELECT id FROM aset WHERE kode_aset = ?";
        $params = [$kode];
        if ($exceptId) {
            $sql .= " AND id != ?";
            $params[] = $exceptId;
        }
        return (bool) $this->query($sql, $params)->fetch();
    }

    public function countByStatus(string $status): int
    {
        $stmt = $this->query("SELECT COUNT(*) as total FROM aset WHERE status = ?", [$status]);
        return (int) $stmt->fetch()['total'];
    }
}
