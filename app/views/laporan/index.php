<div class="card mb-3">
  <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
    <span>Laporan &amp; Analitik</span>
    <div class="d-flex gap-2">
      <a href="<?= url('laporan/export-excel?bulan=' . $bulan . '&tahun=' . $tahun) ?>" class="btn btn-sm" style="background:var(--green-bg);color:var(--green);font-weight:600;">
        <i class="fa-solid fa-file-excel me-1"></i> Export Excel
      </a>
      <a href="<?= url('laporan/export-pdf?bulan=' . $bulan . '&tahun=' . $tahun) ?>" target="_blank" class="btn btn-sm" style="background:var(--red-bg);color:var(--red);font-weight:600;">
        <i class="fa-solid fa-file-pdf me-1"></i> Export / Cetak PDF
      </a>
    </div>
  </div>
  <div class="card-body">
    <form method="GET" action="<?= url('laporan') ?>" class="d-flex gap-2 flex-wrap">
      <select name="bulan" class="form-select form-select-sm" style="width:150px;">
        <?php for ($b = 1; $b <= 12; $b++): ?>
          <option value="<?= $b ?>" <?= $b == $bulan ? 'selected' : '' ?>><?= nama_bulan($b) ?></option>
        <?php endfor; ?>
      </select>
      <select name="tahun" class="form-select form-select-sm" style="width:120px;">
        <?php for ($t = (int) date('Y') - 2; $t <= (int) date('Y') + 1; $t++): ?>
          <option value="<?= $t ?>" <?= $t == $tahun ? 'selected' : '' ?>><?= $t ?></option>
        <?php endfor; ?>
      </select>
      <button class="btn btn-sm btn-outline-secondary" type="submit">Tampilkan Laporan</button>
    </form>
    <p class="text-muted small mb-0 mt-2"><i class="fa-solid fa-circle-info me-1"></i>Menampilkan data untuk periode <?= nama_bulan($bulan) ?> <?= $tahun ?></p>
  </div>
</div>

<div class="row g-3">
  <div class="col-lg-6">
    <div class="card h-100">
      <div class="card-header">Jumlah Karyawan per Departemen</div>
      <div class="card-body"><canvas id="chartKaryawan" height="150"></canvas></div>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="card h-100">
      <div class="card-header">Rekapitulasi Absensi - <?= nama_bulan($bulan) ?> <?= $tahun ?></div>
      <div class="card-body"><canvas id="chartAbsensi" height="150"></canvas></div>
    </div>
  </div>
  <div class="col-12">
    <div class="card">
      <div class="card-header">Total Biaya Gaji per Departemen - <?= nama_bulan($bulan) ?> <?= $tahun ?></div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead><tr><th>Departemen</th><th class="text-end">Total Gaji</th></tr></thead>
            <tbody>
              <?php if (empty($totalGajiPerDept)): ?>
                <tr><td colspan="2" class="text-center text-muted py-4">Belum ada data payroll untuk periode ini.</td></tr>
              <?php else: ?>
                <?php foreach ($totalGajiPerDept as $g): ?>
                  <tr><td><?= e($g['nama']) ?></td><td class="text-end"><?= format_rupiah($g['total_gaji']) ?></td></tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<?php
$labelDept = array_map(fn($d) => $d['nama'], $jumlahPerDept);
$totalDept = array_map(fn($d) => (int) $d['total'], $jumlahPerDept);
$labelAbsensi = array_map(fn($a) => ucfirst($a['status']), $rekapAbsensi);
$totalAbsensi = array_map(fn($a) => (int) $a['total'], $rekapAbsensi);

$extraScript = "<script>
document.addEventListener('DOMContentLoaded', function () {
  new Chart(document.getElementById('chartKaryawan'), {
    type: 'doughnut',
    data: {
      labels: " . json_encode($labelDept) . ",
      datasets: [{ data: " . json_encode($totalDept) . ", backgroundColor: ['#2f6fed','#16a34a','#f59e0b','#dc2626','#06b6d4','#7c3aed'] }]
    },
    options: { responsive: true }
  });
  new Chart(document.getElementById('chartAbsensi'), {
    type: 'bar',
    data: {
      labels: " . json_encode($labelAbsensi) . ",
      datasets: [{ label: 'Jumlah', data: " . json_encode($totalAbsensi) . ", backgroundColor: '#16a34a', borderRadius: 6 }]
    },
    options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
  });
});
</script>";
?>
