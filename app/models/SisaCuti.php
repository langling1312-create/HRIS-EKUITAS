<?php
require_once __DIR__ . '/../core/Model.php';

class SisaCuti extends Model
{
    protected string $table = 'sisa_cuti';

    public function get(int $userId, int $tahun)
    {
        $stmt = $this->query(
            "SELECT * FROM sisa_cuti WHERE user_id = ? AND tahun = ? LIMIT 1",
            [$userId, $tahun]
        );
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getOrCreate(int $userId, int $tahun, int $default = 12)
    {
        $row = $this->get($userId, $tahun);
        if ($row) {
            return $row;
        }
        $this->insert([
            'user_id'   => $userId,
            'tahun'     => $tahun,
            'sisa_hari' => $default,
        ]);
        return $this->get($userId, $tahun);
    }

    public function kurangi(int $userId, int $tahun, int $jumlahHari): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE sisa_cuti SET sisa_hari = sisa_hari - ? WHERE user_id = ? AND tahun = ?"
        );
        return $stmt->execute([$jumlahHari, $userId, $tahun]);
    }
}
