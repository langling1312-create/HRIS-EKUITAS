<div class="row g-3 mb-3">
  <div class="col-md-6">
    <div class="card stat-card">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="icon-box bg-icon-blue"><i class="fa-solid fa-file-lines"></i></div>
        <div>
          <div class="stat-label">Total Log Hari Ini</div>
          <div class="stat-value"><?= (int) $totalLogHariIni ?></div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card stat-card">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="icon-box bg-icon-green"><i class="fa-solid fa-user-clock"></i></div>
        <div>
          <div class="stat-label">Pengguna Aktif Hari Ini</div>
          <div class="stat-value"><?= (int) $userAktifHariIni ?></div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="card mb-3">
  <div class="card-body">
    <form class="row g-2" onsubmit="return false;">
      <div class="col-md-4">
        <div class="sb-search w-100">
          <i class="fa-solid fa-search"></i>
          <input type="text" id="cariUser" placeholder="Cari nama user..." onkeyup="filterLog()">
        </div>
      </div>
      <div class="col-md-3">
        <input type="date" id="dariTanggal" class="form-control form-control-sm" onchange="filterLog()">
      </div>
      <div class="col-md-3">
        <input type="date" id="sampaiTanggal" class="form-control form-control-sm" onchange="filterLog()">
      </div>
      <div class="col-md-2">
        <button type="button" class="btn btn-sm btn-outline-secondary w-100" onclick="resetFilter()">
          <i class="fa-solid fa-rotate-left me-1"></i> Reset
        </button>
      </div>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-header">Log Aktivitas Sistem</div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0" id="tabelLog">
        <thead>
          <tr><th>Waktu</th><th>User</th><th>Aktivitas</th><th>IP Address</th></tr>
        </thead>
        <tbody>
          <?php if (empty($daftarLog)): ?>
            <tr><td colspan="4" class="text-center text-muted py-4">Belum ada log aktivitas.</td></tr>
          <?php else: ?>
            <?php foreach ($daftarLog as $l): ?>
              <tr data-user="<?= e(strtolower($l['name'] ?? 'sistem')) ?>" data-date="<?= date('Y-m-d', strtotime($l['created_at'])) ?>">
                <td><?= format_tanggal($l['created_at'], 'd M Y H:i:s') ?></td>
                <td class="d-flex align-items-center gap-2">
                  <img src="https://ui-avatars.com/api/?background=2f6fed&color=fff&size=32&name=<?= urlencode($l['name'] ?? 'S') ?>" class="rounded-circle" width="30" height="30">
                  <?= e($l['name'] ?? 'Sistem') ?>
                </td>
                <td><?= e($l['aktivitas']) ?></td>
                <td><?= e($l['ip_address'] ?? '-') ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
function filterLog() {
  var kw = document.getElementById('cariUser').value.toLowerCase();
  var dari = document.getElementById('dariTanggal').value;
  var sampai = document.getElementById('sampaiTanggal').value;
  document.querySelectorAll('#tabelLog tbody tr').forEach(function (row) {
    if (!row.dataset.user) return;
    var matchUser = !kw || row.dataset.user.indexOf(kw) !== -1;
    var matchDari = !dari || row.dataset.date >= dari;
    var matchSampai = !sampai || row.dataset.date <= sampai;
    row.style.display = (matchUser && matchDari && matchSampai) ? '' : 'none';
  });
}
function resetFilter() {
  document.getElementById('cariUser').value = '';
  document.getElementById('dariTanggal').value = '';
  document.getElementById('sampaiTanggal').value = '';
  filterLog();
}
</script>
