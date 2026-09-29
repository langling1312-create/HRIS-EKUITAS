<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Slip Gaji - <?= e($slip['name']) ?> - <?= nama_bulan((int) $slip['bulan']) ?> <?= $slip['tahun'] ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
  body { padding: 2rem; }
  .slip-box { max-width: 720px; margin: 0 auto; border: 1px solid #ddd; padding: 2rem; border-radius: .5rem; }
  .slip-box .table thead { background:#f4f6fb; }
  @media print { .no-print { display: none; } }
</style>
</head>
<body>
<div class="text-center mb-3 no-print">
  <button class="btn btn-primary" style="background:#2f6fed;border:none;" onclick="window.print()"><i class="fa-solid fa-print"></i> Cetak / Simpan sebagai PDF</button>
</div>
<div class="slip-box">
  <div class="text-center mb-4">
    <h4 class="fw-bold mb-0">EKUITAS HRIS </h4>
    <p class="text-muted mb-0">SLIP GAJI KARYAWAN</p>
    <hr>
  </div>
  <div class="row mb-4">
    <div class="col-6">
      <p class="mb-1"><strong>Nama</strong>: <?= e($slip['name']) ?></p>
      <p class="mb-1"><strong>NIP</strong>: <?= e($slip['nip'] ?? '-') ?></p>
      <p class="mb-1"><strong>Jabatan</strong>: <?= e($slip['jabatan'] ?? '-') ?></p>
    </div>
    <div class="col-6 text-end">
      <p class="mb-1"><strong>Departemen</strong>: <?= e($slip['departemen_nama'] ?? '-') ?></p>
      <p class="mb-1"><strong>Periode</strong>: <?= nama_bulan((int) $slip['bulan']) ?> <?= $slip['tahun'] ?></p>
      <p class="mb-1"><strong>Jumlah Hadir</strong>: <?= (int) $hadir ?> hari</p>
    </div>
  </div>

  <table class="table table-bordered">
    <thead class="table-light">
      <tr><th>Komponen</th><th class="text-end">Jumlah</th></tr>
    </thead>
    <tbody>
      <tr><td>Gaji Pokok</td><td class="text-end"><?= format_rupiah($slip['gaji_pokok']) ?></td></tr>
      <?php
        $rincianTunjangan = [
          'Tunjangan Jabatan'          => $slip['tunjangan_jabatan'] ?? 0,
          'Tunjangan Transport/Makan'  => $slip['tunjangan_transport'] ?? 0,
          'Tunjangan BPJS'             => $slip['tunjangan_bpjs'] ?? 0,
          'Tunjangan Sakit'            => $slip['tunjangan_sakit'] ?? 0,
          'Tunjangan Lainnya'          => $slip['tunjangan_lainnya'] ?? 0,
        ];
        $adaRincian = false;
        foreach ($rincianTunjangan as $nilai) {
          if ((float) $nilai > 0) { $adaRincian = true; break; }
        }
      ?>
      <?php if ($adaRincian): ?>
        <?php foreach ($rincianTunjangan as $label => $nilai): if ((float) $nilai <= 0) continue; ?>
          <tr><td class="ps-4 text-muted"><?= e($label) ?></td><td class="text-end"><?= format_rupiah($nilai) ?></td></tr>
        <?php endforeach; ?>
        <tr class="fw-semibold"><td>Total Tunjangan</td><td class="text-end"><?= format_rupiah($slip['tunjangan']) ?></td></tr>
      <?php else: ?>
        <tr><td>Tunjangan</td><td class="text-end"><?= format_rupiah($slip['tunjangan']) ?></td></tr>
      <?php endif; ?>
      <tr><td>Potongan Lain-lain</td><td class="text-end">- <?= format_rupiah($slip['potongan']) ?></td></tr>
      <tr><td>BPJS Kesehatan</td><td class="text-end">- <?= format_rupiah($slip['bpjs_kesehatan'] ?? 0) ?></td></tr>
      <tr><td>BPJS Ketenagakerjaan (JHT)</td><td class="text-end">- <?= format_rupiah($slip['bpjs_ketenagakerjaan'] ?? 0) ?></td></tr>
      <tr><td>PPh 21</td><td class="text-end">- <?= format_rupiah($slip['pph21'] ?? 0) ?></td></tr>
      <?php if (!empty($slip['potongan_kasbon'])): ?>
        <tr><td>Cicilan Kasbon</td><td class="text-end">- <?= format_rupiah($slip['potongan_kasbon']) ?></td></tr>
      <?php endif; ?>

      <?php
        // Hitung ulang langsung di view agar sinkron dengan data yang ditampilkan
        $gaji_pokok           = (float) ($slip['gaji_pokok'] ?? 0);
        $total_tunjangan      = (float) ($slip['tunjangan'] ?? 0);
        $potongan_lain        = (float) ($slip['potongan'] ?? 0);
        $bpjs_kesehatan       = (float) ($slip['bpjs_kesehatan'] ?? 0);
        $bpjs_ketenagakerjaan = (float) ($slip['bpjs_ketenagakerjaan'] ?? 0);
        $pph21                = (float) ($slip['pph21'] ?? 0);
        $potongan_kasbon      = (float) ($slip['potongan_kasbon'] ?? 0);

        $total_diterima       = ($gaji_pokok + $total_tunjangan) - ($potongan_lain + $bpjs_kesehatan + $bpjs_ketenagakerjaan + $pph21 + $potongan_kasbon);
      ?>

      <tr class="table-light fw-bold"><td>Total Diterima</td><td class="text-end"><?= format_rupiah(max(0, $total_diterima)) ?></td></tr>
    </tbody>
  </table>

  <p class="text-muted small mt-4 mb-0">Dicetak pada <?= format_tanggal(date('Y-m-d')) ?> melalui sistem HRIS.</p>
</div>
</body>
</html>   