<?php
require_once __DIR__ . '/../core/Model.php';

class LogAktivitas extends Model
{
    protected string $table = 'log_aktivitas';

    public function catat(?int $userId, string $aktivitas): void
    {
        $this->insert([
            'user_id'    => $userId,
            'aktivitas'  => $aktivitas,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
        ]);
    }

    public function allWithUser(int $limit = 200): array
    {
        $sql = "SELECT l.*, u.name FROM log_aktivitas l
                LEFT JOIN users u ON l.user_id = u.id
                ORDER BY l.created_at DESC LIMIT {$limit}";
        return $this->query($sql)->fetchAll();
    }

    public function byUser(int $userId, int $limit = 8): array
    {
        $stmt = $this->query(
            "SELECT * FROM log_aktivitas WHERE user_id = ? ORDER BY created_at DESC LIMIT {$limit}",
            [$userId]
        );
        return $stmt->fetchAll();
    }
}
