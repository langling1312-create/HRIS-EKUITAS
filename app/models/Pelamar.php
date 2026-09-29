<?php
require_once __DIR__ . '/../core/Model.php';

class Pelamar extends Model
{
    protected string $table = 'pelamar';

    public function byLowongan(int $lowonganId): array
    {
        return $this->query(
            "SELECT * FROM pelamar WHERE lowongan_id = ? ORDER BY FIELD(status,'baru','interview','diterima','ditolak'), created_at DESC",
            [$lowonganId]
        )->fetchAll();
    }

    public function allWithLowongan(): array
    {
        $sql = "SELECT p.*, l.judul AS lowongan_judul FROM pelamar p
                JOIN lowongan l ON p.lowongan_id = l.id
                ORDER BY FIELD(p.status,'baru','interview','diterima','ditolak'), p.created_at DESC";
        return $this->query($sql)->fetchAll();
    }

    public function countBaru(): int
    {
        $stmt = $this->query("SELECT COUNT(*) as total FROM pelamar WHERE status = 'baru'");
        return (int) $stmt->fetch()['total'];
    }
}
