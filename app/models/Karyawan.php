<?php
require_once __DIR__ . '/../core/Model.php';

class Karyawan extends Model
{
    protected string $table = 'karyawan';

    public function allWithUser(): array
    {
        $sql = "SELECT k.*, u.name, u.email, u.foto, u.no_hp, u.role, d.nama AS departemen_nama
                FROM karyawan k
                JOIN users u ON k.user_id = u.id
                LEFT JOIN departemen d ON k.departemen_id = d.id
                ORDER BY k.id DESC";
        return $this->query($sql)->fetchAll();
    }

    public function search(string $keyword): array
    {
        $sql = "SELECT k.*, u.name, u.email, u.foto, u.role, d.nama AS departemen_nama
                FROM karyawan k
                JOIN users u ON k.user_id = u.id
                LEFT JOIN departemen d ON k.departemen_id = d.id
                WHERE u.name LIKE ? OR k.nip LIKE ?
                ORDER BY k.id DESC";
        $kw = "%{$keyword}%";
        return $this->query($sql, [$kw, $kw])->fetchAll();
    }

    public function findWithUser(int $id)
    {
        $sql = "SELECT k.*, u.name, u.email, u.foto, u.no_hp, u.alamat
                FROM karyawan k
                JOIN users u ON k.user_id = u.id
                WHERE k.id = ? LIMIT 1";
        $row = $this->query($sql, [$id])->fetch();
        return $row ?: null;
    }

    public function findByUserId(int $userId)
    {
        $stmt = $this->query("SELECT * FROM karyawan WHERE user_id = ? LIMIT 1", [$userId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function countAktif(): int
    {
        $stmt = $this->query("SELECT COUNT(*) as total FROM karyawan WHERE status_aktif = TRUE");
        return (int) $stmt->fetch()['total'];
    }

    public function allAktif(): array
    {
        return $this->query("SELECT * FROM karyawan WHERE status_aktif = TRUE")->fetchAll();
    }

    public function nipExists(string $nip, ?int $exceptId = null): bool
    {
        $sql = "SELECT id FROM karyawan WHERE nip = ?";
        $params = [$nip];
        if ($exceptId) {
            $sql .= " AND id != ?";
            $params[] = $exceptId;
        }
        return (bool) $this->query($sql, $params)->fetch();
    }
}
