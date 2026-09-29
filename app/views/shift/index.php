<?php
$hariIndo = ['Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu'];
$tanggalMinggu = [];
$cursor = strtotime($mulai);
for ($i = 0; $i < 7; $i++) {
    $tanggalMinggu[] = date('Y-m-d', $cursor);
    $cursor = strtotime('+1 day', $cursor);
}
?>

<?php if (AuthHelper::isHrOrAdmin()): ?>

  <div class="card mb-3">
    <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-2">
      <form method="GET" action="<?= url('shift') ?>" class="d-flex gap-2 align-items-end">
        <div>
          <label class="form-label small mb-1">Minggu Mulai</label>
          <input type="date" name="mulai" class="form-control form-control-sm" value="<?= e($mulai) ?>">
        </div>
        <div>
          <label class="form-label small mb-1">Cari Karyawan</label>
          <input type="text" name="q" class="form-control form-control-sm" value="<?= e($keyword) ?>" placeholder="Nama karyawan...">
        </div>
        <button class="btn btn-sm btn-outline-secondary" type="submit">Tampilkan</button>
      </form>
      <button class="btn btn-sm btn-outline-brand" data-bs-toggle="modal" data-bs-target="#modalTambahShift">
        <i class="fa-solid fa-plus me-1"></i> Jenis Shift Baru
      </button>
    </div>
  </div>

  <div class="card mb-3">
    <div class="card-body py-2">
      <span class="text-muted small me-2"><i class="fa-solid fa-clock me-1"></i>Jenis Shift:</span>
      <?php foreach ($daftarShift as $s): ?>
        <span class="badge bg-light text-dark border me-1">
          <?= e($s['nama_shift']) ?>: <?= e(substr($s['jam_mulai'], 0, 5)) ?>-<?= e(substr($s['jam_selesai'], 0, 5)) ?>
        </span>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="card">
    <div class="card-header">Jadwal Shift Mingguan (<?= format_tanggal($mulai) ?> - <?= format_tanggal($selesai) ?>)</div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-bordered align-middle mb-0" style="min-width:900px;">
          <thead>
            <tr>
              <th style="min-width:160px;">Karyawan</th>
              <?php foreach ($tanggalMinggu as $tgl): ?>
                <th class="text-center"><?= $hariIndo[date('l', strtotime($tgl))] ?><br><span class="text-muted fw-normal"><?= date('d/m', strtotime($tgl)) ?></span></th>
              <?php endforeach; ?>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($daftarKaryawan)): ?>
              <tr><td colspan="8" class="text-center text-muted py-4">Belum ada data karyawan.</td></tr>
            <?php else: ?>
              <?php foreach ($daftarKaryawan as $k): if (!$k['status_aktif']) continue; ?>
                <tr>
                  <td class="fw-semibold"><?= e($k['name']) ?></td>
                  <?php foreach ($tanggalMinggu as $tgl): ?>
                    <?php $jadwal = $grid[$k['user_id']][$tgl] ?? null; ?>
                    <td class="text-center p-1">
                      <select class="form-select form-select-sm select-shift" data-user="<?= $k['user_id'] ?>" data-tanggal="<?= $tgl ?>">
                        <option value="">-</option>
                        <?php foreach ($daftarShift as $s): ?>
                          <option value="<?= $s['id'] ?>"
                                  data-jam="<?= e(substr($s['jam_mulai'], 0, 5)) ?> - <?= e(substr($s['jam_selesai'], 0, 5)) ?>"
                                  <?= $jadwal && $jadwal['shift_id'] == $s['id'] ? 'selected' : '' ?>>
                            <?= e($s['nama_shift']) ?> (<?= e(substr($s['jam_mulai'], 0, 5)) ?>-<?= e(substr($s['jam_selesai'], 0, 5)) ?>)
                          </option>
                        <?php endforeach; ?>
                      </select>
                      <div class="text-muted small jam-shift-label mt-1">
                        <?php if ($jadwal): ?>
                          <?= e(substr($jadwal['jam_mulai'], 0, 5)) ?> - <?= e(substr($jadwal['jam_selesai'], 0, 5)) ?>
                        <?php endif; ?>
                      </div>
                    </td>
                  <?php endforeach; ?>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modalTambahShift" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <form method="POST" action="<?= url('shift/tambah-shift') ?>">
          <div class="modal-header">
            <h5 class="modal-title">Tambah Jenis Shift</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Nama Shift <span class="text-danger">*</span></label>
              <input type="text" name="nama_shift" class="form-control" placeholder="Contoh: Shift Pagi" required>
            </div>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">Jam Mulai</label>
                <input type="time" name="jam_mulai" class="form-control" required>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Jam Selesai</label>
                <input type="time" name="jam_selesai" class="form-control" required>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-brand">Simpan</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.select-shift').forEach(function (el) {
      el.addEventListener('change', function () {
        var jamLabel = this.closest('td').querySelector('.jam-shift-label');
        var opt = this.options[this.selectedIndex];
        if (jamLabel) {
          jamLabel.textContent = (this.value && opt) ? opt.getAttribute('data-jam') : '';
        }
        if (!this.value) return;
        var body = new URLSearchParams();
        body.append('user_id', this.dataset.user);
        body.append('tanggal', this.dataset.tanggal);
        body.append('shift_id', this.value);
        fetch('<?= url("shift/simpan-jadwal") ?>', { method: 'POST', body: body });
      });
    });
  });
  </script>

<?php else: ?>

  <div class="card mb-3">
    <div class="card-body">
      <form method="GET" action="<?= url('shift') ?>" class="d-flex gap-2 align-items-end">
        <div>
          <label class="form-label small mb-1">Minggu Mulai</label>
          <input type="date" name="mulai" class="form-control form-control-sm" value="<?= e($mulai) ?>">
        </div>
        <button class="btn btn-sm btn-outline-secondary" type="submit">Tampilkan</button>
      </form>
    </div>
  </div>

  <div class="card">
    <div class="card-header">Jadwal Shift Saya (<?= format_tanggal($mulai) ?> - <?= format_tanggal($selesai) ?>)</div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead><tr><th>Hari</th><th>Tanggal</th><th>Shift</th><th>Jam</th></tr></thead>
          <tbody>
            <?php
              $jadwalMap = [];
              foreach ($jadwalSaya as $j) { $jadwalMap[$j['tanggal']] = $j; }
            ?>
            <?php foreach ($tanggalMinggu as $tgl): $j = $jadwalMap[$tgl] ?? null; ?>
              <tr>
                <td><?= $hariIndo[date('l', strtotime($tgl))] ?></td>
                <td><?= format_tanggal($tgl) ?></td>
                <td><?= $j ? e($j['nama_shift']) : '<span class="text-muted">Belum dijadwalkan</span>' ?></td>
                <td><?= $j ? e($j['jam_mulai']) . ' - ' . e($j['jam_selesai']) : '-' ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

<?php endif; ?>
