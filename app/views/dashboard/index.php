<div class="row g-3 mb-3">
  <div class="col-xl-3 col-md-6">
    <div class="card stat-card">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="icon-box bg-icon-blue"><i class="fa-solid fa-users"></i></div>
        <div>
          <div class="stat-label">Total Karyawan</div>
          <div class="stat-value"><?= (int) $totalKaryawan ?></div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-xl-3 col-md-6">
    <div class="card stat-card">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="icon-box bg-icon-green"><i class="fa-solid fa-user-check"></i></div>
        <div>
          <div class="stat-label">Hadir Hari Ini</div>
          <div class="stat-value"><?= (int) $hadirHariIni ?></div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-xl-3 col-md-6">
    <div class="card stat-card">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="icon-box bg-icon-yellow"><i class="fa-solid fa-calendar-days"></i></div>
        <div>
          <div class="stat-label">Cuti Pending</div>
          <div class="stat-value"><?= (int) $cutiPending ?></div>
          <div class="stat-sub">Menunggu persetujuan</div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-xl-3 col-md-6">
    <div class="card stat-card">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="icon-box bg-icon-purple"><i class="fa-solid fa-sack-dollar"></i></div>
        <div>
          <div class="stat-label">Gaji Bulan Ini</div>
          <div class="fs-6 fw-bold"><?= format_rupiah($gajiBulanIni) ?></div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row g-3">
  <div class="col-lg-8">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span>Karyawan per Departemen</span>
      </div>
      <div class="card-body">
        <canvas id="chartDept" height="110"></canvas>
      </div>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span>Notifikasi Terbaru</span>
      </div>
      <div class="card-body p-0">
        <?php if (empty($notifikasi)): ?>
          <p class="text-muted small mb-0 p-3">Belum ada notifikasi.</p>
        <?php else: ?>
          <ul class="list-unstyled mb-0">
            <?php foreach ($notifikasi as $n): ?>
              <li class="border-bottom px-3 py-2 small">
                <div class="<?= $n['dibaca'] ? '' : 'fw-semibold' ?>"><?= e($n['pesan']) ?></div>
                <div class="text-muted" style="font-size:.72rem;"><?= format_tanggal($n['created_at'], 'd M Y H:i') ?></div>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php
$labels = array_map(fn($d) => $d['nama'], $sebaranDept);
$totals = array_map(fn($d) => (int) $d['total'], $sebaranDept);
$extraScript = "<script>
document.addEventListener('DOMContentLoaded', function () {
  var ctx = document.getElementById('chartDept');
  if (ctx) {
    new Chart(ctx, {
      type: 'bar',
      data: {
        labels: " . json_encode($labels) . ",
        datasets: [{
          label: 'Jumlah Karyawan',
          data: " . json_encode($totals) . ",
          backgroundColor: '#2f6fed',
          borderRadius: 6,
          maxBarThickness: 46,
        }]
      },
      options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } }, x: { grid: { display: false } } }
      }
    });
  }
});
</script>";
?>
