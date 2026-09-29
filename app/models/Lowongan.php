<?php
require_once __DIR__ . '/../core/Model.php';

class Lowongan extends Model
{
    protected string $table = 'lowongan';

    public function allWithDept(): array
    {
        $sql = "SELECT l.*, d.nama AS departemen_nama,
                       (SELECT COUNT(*) FROM pelamar p WHERE p.lowongan_id = l.id) AS total_pelamar
                FROM lowongan l
                LEFT JOIN departemen d ON l.departemen_id = d.id
                ORDER BY l.created_at DESC";
        return $this->query($sql)->fetchAll();
    }

    public function terbuka(): array
    {
        $sql = "SELECT l.*, d.nama AS departemen_nama FROM lowongan l
                LEFT JOIN departemen d ON l.departemen_id = d.id
                WHERE l.status = 'buka' ORDER BY l.created_at DESC";
        return $this->query($sql)->fetchAll();
    }
}
