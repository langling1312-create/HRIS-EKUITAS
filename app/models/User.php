<?php
require_once __DIR__ . '/../core/Model.php';

class User extends Model
{
    protected string $table = 'users';

    public function findByEmail(string $email)
    {
        $stmt = $this->query("SELECT * FROM users WHERE email = ? LIMIT 1", [$email]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function emailExists(string $email, ?int $exceptId = null): bool
    {
        $sql = "SELECT id FROM users WHERE email = ?";
        $params = [$email];
        if ($exceptId) {
            $sql .= " AND id != ?";
            $params[] = $exceptId;
        }
        $stmt = $this->query($sql, $params);
        return (bool) $stmt->fetch();
    }

    public function countByRole(string $role): int
    {
        $stmt = $this->query("SELECT COUNT(*) as total FROM users WHERE role = ?", [$role]);
        return (int) $stmt->fetch()['total'];
    }

    public function findByRoles(array $roles): array
    {
        $placeholders = implode(',', array_fill(0, count($roles), '?'));
        $stmt = $this->query("SELECT * FROM users WHERE role IN ({$placeholders})", $roles);
        return $stmt->fetchAll();
    }
}
