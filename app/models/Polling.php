<?php
require_once __DIR__ . '/../core/Model.php';

class Polling extends Model
{
    protected string $table = 'polling';

    public function allWithOpsi(): array
    {
        $pollingList = $this->query("SELECT * FROM polling ORDER BY created_at DESC")->fetchAll();
        foreach ($pollingList as &$p) {
            $p['opsi'] = $this->query(
                "SELECT po.*, (SELECT COUNT(*) FROM polling_vote pv WHERE pv.opsi_id = po.id) AS jumlah_vote
                 FROM polling_opsi po WHERE po.polling_id = ?",
                [$p['id']]
            )->fetchAll();
            $p['total_vote'] = $this->query(
                "SELECT COUNT(*) as total FROM polling_vote WHERE polling_id = ?",
                [$p['id']]
            )->fetch()['total'];
        }
        return $pollingList;
    }

    public function sudahVote(int $pollingId, int $userId): bool
    {
        $stmt = $this->query(
            "SELECT id FROM polling_vote WHERE polling_id = ? AND user_id = ?",
            [$pollingId, $userId]
        );
        return (bool) $stmt->fetch();
    }
}
