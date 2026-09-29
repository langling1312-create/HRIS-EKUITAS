<?php
require_once __DIR__ . '/../core/Model.php';

class Departemen extends Model
{
    protected string $table = 'departemen';

    public function jumlahKaryawanPerDept(): array
    {
        $sql = "SELECT d.id, d.nama, COUNT(k.id) AS total
                FROM departemen d
                LEFT JOIN karyawan k ON d.id = k.departemen_id AND k.status_aktif = TRUE
                GROUP BY d.id, d.nama
                ORDER BY d.nama ASC";
        return $this->query($sql)->fetchAll();
    }

    /**
     * Daftar departemen lengkap dengan nama Kepala Unit & Pimpinan Unit
     * (jika sudah ditentukan) untuk kebutuhan alur persetujuan cuti.
     */
    public function allWithPejabat(): array
    {
        $sql = "SELECT d.*,
                    ku.name AS kepala_unit_nama, ku.foto AS kepala_unit_foto,
                    pu.name AS pimpinan_unit_nama, pu.foto AS pimpinan_unit_foto
                FROM departemen d
                LEFT JOIN users ku ON d.kepala_unit_id = ku.id
                LEFT JOIN users pu ON d.pimpinan_unit_id = pu.id
                ORDER BY d.nama ASC";
        return $this->query($sql)->fetchAll();
    }

    public function findWithPejabat(int $id)
    {
        $sql = "SELECT d.*,
                    ku.name AS kepala_unit_nama,
                    pu.name AS pimpinan_unit_nama
                FROM departemen d
                LEFT JOIN users ku ON d.kepala_unit_id = ku.id
                LEFT JOIN users pu ON d.pimpinan_unit_id = pu.id
                WHERE d.id = ? LIMIT 1";
        $row = $this->query($sql, [$id])->fetch();
        return $row ?: null;
    }

    /**
     * Tentukan Kepala Unit & Pimpinan Unit sebuah departemen.
     * Kedua nilai bebas diisi salah satu, keduanya, atau dikosongkan (NULL)
     * -- kosong berarti tahap tersebut otomatis dilewati saat approval cuti.
     */
    public function setPejabat(int $id, ?int $kepalaUnitId, ?int $pimpinanUnitId): bool
    {
        return $this->update($id, [
            'kepala_unit_id'   => $kepalaUnitId,
            'pimpinan_unit_id' => $pimpinanUnitId,
        ]);
    }

    /**
     * Cari departemen yang "diampu" oleh seorang user sebagai Kepala Unit
     * dan/atau Pimpinan Unit. Dipakai untuk menampilkan menu approval cuti.
     */
    public function unitDiampu(int $userId): array
    {
        $sql = "SELECT id, nama,
                    (kepala_unit_id = ?) AS sebagai_kepala_unit,
                    (pimpinan_unit_id = ?) AS sebagai_pimpinan_unit
                FROM departemen
                WHERE kepala_unit_id = ? OR pimpinan_unit_id = ?";
        return $this->query($sql, [$userId, $userId, $userId, $userId])->fetchAll();
    }
}
