<?php
/**
 * Kumpulan fungsi bantu global yang dipakai di seluruh view.
 */

function url(string $path = ''): string
{
    return BASE_URL . ltrim($path, '/');
}

function asset(string $path = ''): string
{
    return BASE_URL . 'assets/' . ltrim($path, '/');
}

function format_rupiah($angka): string
{
    return 'Rp ' . number_format((float) $angka, 0, ',', '.');
}

function format_tanggal($tanggal, string $format = 'd F Y'): string
{
    if (empty($tanggal)) {
        return '-';
    }
    $bulanIndo = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];
    $ts = is_numeric($tanggal) ? $tanggal : strtotime($tanggal);
    if ($format === 'd F Y') {
        return date('d', $ts) . ' ' . $bulanIndo[(int) date('n', $ts)] . ' ' . date('Y', $ts);
    }
    return date($format, $ts);
}

function nama_bulan(int $bulan): string
{
    $bulanIndo = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];
    return $bulanIndo[$bulan] ?? '-';
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function old(string $key, $default = '')
{
    $old = SessionHelper::flash('old_input');
    if ($old && isset($old[$key])) {
        return $old[$key];
    }
    return $default;
}

function badge_status_cuti(string $status): string
{
    return '<span class="badge badge-status-' . e($status) . '">' . ucfirst($status) . '</span>';
}

/**
 * Label & badge untuk tahap alur persetujuan cuti berjenjang
 * (Kepala Unit -> Pimpinan Unit -> HRD).
 */
function label_tahap_cuti(string $tahap): string
{
    $map = [
        'kepala_unit'   => 'Menunggu Kepala Unit',
        'pimpinan_unit' => 'Menunggu Pimpinan Unit',
        'hrd'           => 'Menunggu HRD',
        'selesai'       => 'Selesai',
    ];
    return $map[$tahap] ?? ucfirst($tahap);
}

function badge_tahap_cuti(array $cuti): string
{
    if ($cuti['status'] === 'ditolak') {
        return '<span class="badge badge-status-ditolak">Ditolak</span>';
    }
    if ($cuti['status'] === 'disetujui') {
        return '<span class="badge badge-status-disetujui">Disetujui</span>';
    }
    return '<span class="badge badge-status-pending">' . e(label_tahap_cuti($cuti['tahap_sekarang'])) . '</span>';
}

/**
 * Rangkaian mini-progress 3 tahap persetujuan cuti untuk ditampilkan
 * di daftar riwayat pengajuan karyawan.
 */
function progress_tahap_cuti(array $cuti): string
{
    $langkah = [
        'kepala_unit'   => ['label' => 'Kepala Unit', 'status' => $cuti['status_kepala_unit']],
        'pimpinan_unit' => ['label' => 'Pimpinan Unit', 'status' => $cuti['status_pimpinan_unit']],
        'hrd'           => ['label' => 'HRD', 'status' => $cuti['status_hrd']],
    ];

    $ikon = [
        'dilewati'  => '<i class="fa-solid fa-minus text-muted" title="Dilewati"></i>',
        'pending'   => '<i class="fa-regular fa-clock text-warning" title="Menunggu"></i>',
        'disetujui' => '<i class="fa-solid fa-circle-check text-success" title="Disetujui"></i>',
        'ditolak'   => '<i class="fa-solid fa-circle-xmark text-danger" title="Ditolak"></i>',
    ];

    $out = [];
    foreach ($langkah as $l) {
        $status = $l['status'];
        $icon = $ikon[$status] ?? $ikon['pending'];
        $out[] = '<span class="small text-nowrap">' . $icon . ' ' . e($l['label']) . '</span>';
    }
    return implode(' <span class="text-muted">&rarr;</span> ', $out);
}

function badge_status_absensi(string $status): string
{
    return '<span class="badge badge-status-' . e($status) . '">' . ucfirst($status) . '</span>';
}

function role_label(string $role): string
{
    $map = [
        'admin'          => 'Admin',
        'hr'             => 'HR',
        'karyawan'       => 'Karyawan',
        'kepala_unit'    => 'Kepala Unit',
        'pimpinan_unit'  => 'Pimpinan Unit',
    ];
    return $map[$role] ?? $role;
}
