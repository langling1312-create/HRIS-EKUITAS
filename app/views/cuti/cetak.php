<?php
$labelJenis = [
    'tahunan' => 'Cuti Tahunan',
    'sakit'   => 'Cuti Sakit',
    'penting' => 'Cuti Kepentingan Penting',
    'spesial' => 'Cuti Spesial',
    'khusus'  => 'Cuti Khusus',
];
$jenisCutiLabel = $labelJenis[$cuti['jenis_cuti']] ?? ucfirst($cuti['jenis_cuti']);

// Nomor surat sederhana: <id 4 digit>/CUTI-HRIS/<bulan romawi>/<tahun>
$romawiBulan = ['', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
$bulanSurat = (int) date('n', strtotime($cuti['created_at']));
$nomorSurat = str_pad((string) $cuti['id'], 4, '0', STR_PAD_LEFT) . '/CUTI-HRIS/' . $romawiBulan[$bulanSurat] . '/' . date('Y', strtotime($cuti['created_at']));

/**
 * Helper kecil untuk merender kolom tanda tangan satu pejabat approval.
 * $statusTahap: dilewati | pending | disetujui | ditolak
 */
if (!function_exists('kolomTandaTanganCuti')) {
function kolomTandaTanganCuti(string $jabatanLabel, ?string $namaPejabat, string $statusTahap, ?string $tanggal, ?string $catatan = null, string $labelDilewati = ''): void
{
    // Catatan: $jabatanLabel hanya diisi string statis dari file ini sendiri
    // (bukan input pengguna), jadi sengaja TIDAK di-escape supaya tag <br>
    // untuk baris kedua labelnya bisa tampil dengan benar.
    echo '<div class="ttd-col text-center">';
    echo '<div class="ttd-label">' . $jabatanLabel . '</div>';

    if ($statusTahap === 'dilewati') {
        echo '<div class="ttd-box d-flex align-items-center justify-content-center text-muted small text-center px-1">' . e($labelDilewati) . '</div>';
        echo '<div class="mt-1 small text-muted">-</div>';
    } elseif ($statusTahap === 'disetujui') {
        echo '<div class="ttd-box ttd-approved d-flex flex-column align-items-center justify-content-center">';
        echo '<i class="fa-solid fa-signature text-success mb-1"></i>';
        echo '<span class="text-success fw-bold small">DISETUJUI</span>';
        echo '</div>';
        echo '<div class="mt-1 fw-semibold small">' . e($namaPejabat ?: $jabatanLabel) . '</div>';
        if ($tanggal) {
            echo '<div class="small text-muted">' . e(format_tanggal($tanggal, 'd/m/Y H:i')) . '</div>';
        }
    } elseif ($statusTahap === 'ditolak') {
        echo '<div class="ttd-box ttd-rejected d-flex flex-column align-items-center justify-content-center">';
        echo '<i class="fa-solid fa-circle-xmark text-danger mb-1"></i>';
        echo '<span class="text-danger fw-bold small">DITOLAK</span>';
        echo '</div>';
        echo '<div class="mt-1 fw-semibold small">' . e($namaPejabat ?: $jabatanLabel) . '</div>';
        if ($tanggal) {
            echo '<div class="small text-muted">' . e(format_tanggal($tanggal, 'd/m/Y H:i')) . '</div>';
        }
        if ($catatan) {
            echo '<div class="small text-danger fst-italic">"' . e($catatan) . '"</div>';
        }
    } else { // pending
        echo '<div class="ttd-box d-flex align-items-center justify-content-center text-muted small">Menunggu tanda tangan</div>';
        echo '<div class="mt-1 small text-muted">' . e($namaPejabat ?: '-') . '</div>';
    }

    echo '</div>';
}
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Surat Permohonan Cuti - <?= e($cuti['name']) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
<style>
  body { padding: 2rem; background:#eef1f6; }
  .surat-box { position: relative; max-width: 780px; margin: 0 auto; background:#fff; border: 1px solid #ddd; padding: 2.5rem; border-radius: .5rem; overflow: hidden; }
  .surat-box .table { font-size: .92rem; }
  .stempel {
    position: absolute;
    top: 90px;
    right: 40px;
    transform: rotate(-18deg);
    border: 4px solid;
    border-radius: 10px;
    padding: 6px 18px;
    font-weight: 800;
    font-size: 1.4rem;
    letter-spacing: 2px;
    opacity: .85;
  }
  .stempel-disetujui { color:#198754; border-color:#198754; }
  .stempel-ditolak { color:#dc3545; border-color:#dc3545; }
  .stempel-pending { color:#6c757d; border-color:#6c757d; font-size: 1rem; }

  .ttd-row { display:flex; gap: 1rem; margin-top: 2.5rem; flex-wrap: wrap; align-items: stretch; }
  .ttd-col { flex: 1 1 0; min-width: 150px; display: flex; flex-direction: column; }
  .ttd-label {
    min-height: 2.6rem;
    display: flex;
    align-items: flex-end;
    justify-content: center;
    margin-bottom: .25rem;
    line-height: 1.3;
  }
  .ttd-box {
    height: 78px;
    border: 1px dashed #adb5bd;
    border-radius: .375rem;
    background: #fafbfc;
  }
  .ttd-approved { border: 1px solid #198754; background:#f0fbf5; }
  .ttd-rejected { border: 1px solid #dc3545; background:#fff5f5; }

  @media print { .no-print { display: none; } body{ background:#fff; padding:0; } .surat-box{ border:none; } }
</style>
</head>
<body>
<div class="text-center mb-3 no-print">
  <button class="btn btn-primary" style="background:#2f6fed;border:none;" onclick="window.print()"><i class="fa-solid fa-print"></i> Cetak / Simpan sebagai PDF</button>
</div>
<div class="surat-box">

  <?php if ($cuti['status'] === 'disetujui'): ?>
    <div class="stempel stempel-disetujui">DISETUJUI</div>
  <?php elseif ($cuti['status'] === 'ditolak'): ?>
    <div class="stempel stempel-ditolak">DITOLAK</div>
  <?php else: ?>
    <div class="stempel stempel-pending">MENUNGGU<br>PERSETUJUAN</div>
  <?php endif; ?>

  <div class="text-center mb-3">
    <h5 class="fw-bold mb-0">EKUITAS HRIS INDONESIA</h5>
    <p class="text-muted mb-0 small">Jl. Contoh Alamat Perusahaan No. 1, Indonesia</p>
    <hr class="my-2">
    <h6 class="fw-bold text-uppercase mb-0">Surat Permohonan Cuti Karyawan</h6>
    <p class="mb-0 small">Nomor: <?= e($nomorSurat) ?></p>
  </div>

  <p class="mb-2">Yang bertanda tangan di bawah ini:</p>
  <table class="table table-sm table-borderless mb-3" style="width:auto;">
    <tr><td style="width:160px;">Nama</td><td style="width:20px;">:</td><td class="fw-semibold"><?= e($cuti['name']) ?></td></tr>
    <tr><td>NIP</td><td>:</td><td><?= e($cuti['nip'] ?? '-') ?></td></tr>
    <tr><td>Jabatan</td><td>:</td><td><?= e($cuti['jabatan'] ?? '-') ?></td></tr>
    <tr><td>Departemen</td><td>:</td><td><?= e($cuti['departemen_nama'] ?? '-') ?></td></tr>
  </table>

  <p class="mb-2">Dengan ini mengajukan permohonan cuti kepada Bagian HRD EKUITAS HRIS INDONESIA, dengan rincian sebagai berikut:</p>
  <table class="table table-bordered mb-3">
    <tr><td style="width:220px;">Jenis Cuti</td><td><?= e($jenisCutiLabel) ?></td></tr>
    <tr><td>Tanggal Mulai</td><td><?= format_tanggal($cuti['tanggal_mulai']) ?></td></tr>
    <tr><td>Tanggal Selesai</td><td><?= format_tanggal($cuti['tanggal_selesai']) ?></td></tr>
    <tr><td>Lama Cuti</td><td><?= (int) $jumlahHari ?> hari</td></tr>
    <tr><td>Alasan</td><td><?= nl2br(e($cuti['alasan'] ?? '-')) ?></td></tr>
    <?php if (!empty($cuti['surat_sakit'])): ?>
      <tr><td>Lampiran</td><td>Surat keterangan dokter terlampir</td></tr>
    <?php endif; ?>
  </table>

  <p class="mb-1">Demikian surat permohonan ini saya buat dengan sebenar-benarnya. Atas persetujuan Bapak/Ibu, saya ucapkan terima kasih.</p>

  <p class="text-end mb-0 mt-3">Diajukan pada, <?= format_tanggal($cuti['created_at'], 'd F Y') ?></p>

  <div class="ttd-row">
    <?php
      // Kalau proses sudah berakhir (tahap_sekarang = selesai) tapi suatu
      // tahap belum sempat diproses (masih 'pending') -- itu artinya proses
      // dihentikan lebih awal karena ditolak di tahap sebelumnya, BUKAN
      // sedang menunggu tanda tangan. Supaya surat tidak menyesatkan,
      // tampilkan sebagai "Tidak diperlukan (proses dihentikan)".
      $prosesSelesai = ($cuti['tahap_sekarang'] === 'selesai');

      $statusPimpinanTampil = $cuti['status_pimpinan_unit'];
      $labelDilewatiPimpinan = 'Tidak diperlukan';
      if ($prosesSelesai && $statusPimpinanTampil === 'pending') {
          $statusPimpinanTampil = 'dilewati';
          $labelDilewatiPimpinan = 'Tidak diperlukan (proses dihentikan)';
      }

      $statusHrdTampil = $cuti['status_hrd'];
      $labelDilewatiHrd = 'Tidak diperlukan';
      if ($prosesSelesai && $statusHrdTampil === 'pending') {
          $statusHrdTampil = 'dilewati';
          $labelDilewatiHrd = 'Tidak diperlukan (proses dihentikan)';
      }
    ?>
    <?php kolomTandaTanganCuti('Pemohon,', $cuti['name'], 'disetujui', $cuti['created_at']); ?>

    <?php kolomTandaTanganCuti(
        'Mengetahui,<br>Kepala Unit',
        $cuti['kepala_unit_name'] ?? null,
        $cuti['status_kepala_unit'],
        $cuti['tanggal_kepala_unit'] ?? null,
        $cuti['catatan_kepala_unit'] ?? null
    ); ?>

    <?php if (!empty($cuti['pimpinan_unit_id'])): ?>
      <?php kolomTandaTanganCuti(
          'Mengetahui,<br>Pimpinan Unit',
          $cuti['pimpinan_unit_name'] ?? null,
          $statusPimpinanTampil,
          $cuti['tanggal_pimpinan_unit'] ?? null,
          $cuti['catatan_pimpinan_unit'] ?? null,
          $labelDilewatiPimpinan
      ); ?>
    <?php endif; ?>

    <?php kolomTandaTanganCuti(
        'Menyetujui,<br>HRD',
        'HRD EKUITAS HRIS',
        $statusHrdTampil,
        $cuti['tanggal_hrd'] ?? null,
        $cuti['catatan_hrd'] ?? null,
        $labelDilewatiHrd
    ); ?>
  </div>

</div>
</body>
</html>