<?php
require_once __DIR__ . '/../core/Model.php';

class Pengumuman extends Model
{
    protected string $table = 'pengumuman';

    public function terbaru(int $limit = 20): array
    {
        $sql = "SELECT pg.*, u.name AS pembuat FROM pengumuman pg
                LEFT JOIN users u ON pg.dibuat_oleh = u.id
                ORDER BY pg.created_at DESC LIMIT {$limit}";
        return $this->query($sql)->fetchAll();
    }
}
