<?php
require_once __DIR__ . '/../core/Model.php';

class Pelatihan extends Model
{
    protected string $table = 'pelatihan';

    public function allWithPeserta(): array
    {
        $sql = "SELECT p.*, (SELECT COUNT(*) FROM peserta_pelatihan pp WHERE pp.pelatihan_id = p.id) AS total_peserta
                FROM pelatihan p ORDER BY p.tanggal_mulai DESC";
        return $this->query($sql)->fetchAll();
    }
}
