<?php
require_once __DIR__ . '/../core/Model.php';

class Reimbursement extends Model
{
    protected string $table = 'reimbursement';

    public function byUser(int $userId): array
    {
        return $this->query("SELECT * FROM reimbursement WHERE user_id = ? ORDER BY created_at DESC", [$userId])->fetchAll();
    }

    public function allWithUser(): array
    {
        $sql = "SELECT r.*, u.name, u.foto FROM reimbursement r
                JOIN users u ON r.user_id = u.id
                ORDER BY FIELD(r.status,'pending','disetujui','ditolak'), r.created_at DESC";
        return $this->query($sql)->fetchAll();
    }

    public function countPending(): int
    {
        $stmt = $this->query("SELECT COUNT(*) as total FROM reimbursement WHERE status = 'pending'");
        return (int) $stmt->fetch()['total'];
    }
}
