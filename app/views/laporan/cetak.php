<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Laporan HRIS - <?= nama_bulan($bulan) ?> <?= $tahun ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
  body { padding: 2rem; }
  .report-box { max-width: 900px; margin: 0 auto; }
  @media print { .no-print { display: none; } }
</style>
</head>
<body>
<div class="text-center mb-3 no-print">
  <button class="btn btn-primary" style="background:#2f6fed;border:none;" onclick="window.print()"><i class="fa-solid fa-print"></i> Cetak / Simpan sebagai PDF</button>
</div>
<div class="report-box">
  <div class="text-center mb-4">
    <h4 class="fw-bold mb-0">PT HRIS INDONESIA</h4>
    <p class="text-muted mb-0">LAPORAN HRIS - <?= strtoupper(nama_bulan($bulan)) ?> <?= $tahun ?></p>
    <hr>
  </div>

  <h6 class="fw-bold">Jumlah Karyawan per Departemen</h6>
  <table class="table table-bordered table-sm mb-4">
    <thead class="table-light"><tr><th>Departemen</th><th>Jumlah</th></tr></thead>
    <tbody>
      <?php foreach ($jumlahPerDept as $row): ?>
        <tr><td><?= e($row['nama']) ?></td><td><?= (int) $row['total'] ?></td></tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <h6 class="fw-bold">Rekapitulasi Absensi</h6>
  <table class="table table-bordered table-sm mb-4">
    <thead class="table-light"><tr><th>Status</th><th>Jumlah</th></tr></thead>
    <tbody>
      <?php foreach ($rekapAbsensi as $row): ?>
        <tr><td><?= e(ucfirst($row['status'])) ?></td><td><?= (int) $row['total'] ?></td></tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <h6 class="fw-bold">Total Biaya Gaji per Departemen</h6>
  <table class="table table-bordered table-sm mb-4">
    <thead class="table-light"><tr><th>Departemen</th><th>Total Gaji</th></tr></thead>
    <tbody>
      <?php foreach ($totalGajiPerDept as $row): ?>
        <tr><td><?= e($row['nama']) ?></td><td><?= format_rupiah($row['total_gaji']) ?></td></tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <p class="text-muted small mt-4 mb-0">Dicetak pada <?= format_tanggal(date('Y-m-d')) ?> melalui sistem HRIS.</p>
</div>
</body>
</html>
