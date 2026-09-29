<?php
require_once __DIR__ . '/../core/Model.php';

class RolePermission extends Model
{
    protected string $table = 'role_permission';

    public function byRole(string $role): array
    {
        return $this->query("SELECT * FROM role_permission WHERE role = ? ORDER BY menu", [$role])->fetchAll();
    }

    public function all(string $orderBy = 'role, menu'): array
    {
        return parent::all($orderBy);
    }

    public function setAccess(string $role, string $menu, bool $canAccess): void
    {
        $stmt = $this->query(
            "SELECT id FROM role_permission WHERE role = ? AND menu = ?",
            [$role, $menu]
        );
        $row = $stmt->fetch();
        if ($row) {
            $this->update((int) $row['id'], ['can_access' => $canAccess ? 1 : 0]);
        } else {
            $this->insert(['role' => $role, 'menu' => $menu, 'can_access' => $canAccess ? 1 : 0]);
        }
    }
}
