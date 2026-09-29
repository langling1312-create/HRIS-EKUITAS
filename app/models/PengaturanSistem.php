<?php
require_once __DIR__ . '/../core/Model.php';

class PengaturanSistem extends Model
{
    protected string $table = 'pengaturan_sistem';

    public function get(string $key, $default = null)
    {
        $stmt = $this->query("SELECT value_setting FROM pengaturan_sistem WHERE key_setting = ?", [$key]);
        $row = $stmt->fetch();
        return $row ? $row['value_setting'] : $default;
    }

    public function set(string $key, string $value): void
    {
        $stmt = $this->query("SELECT id FROM pengaturan_sistem WHERE key_setting = ?", [$key]);
        $row = $stmt->fetch();
        if ($row) {
            $this->update((int) $row['id'], ['value_setting' => $value]);
        } else {
            $this->insert(['key_setting' => $key, 'value_setting' => $value]);
        }
    }

    public function allAsMap(): array
    {
        $rows = $this->all('key_setting');
        $map = [];
        foreach ($rows as $r) {
            $map[$r['key_setting']] = $r['value_setting'];
        }
        return $map;
    }
}
