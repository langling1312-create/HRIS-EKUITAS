<div class="card mb-3">
  <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
    <span>Rekap Absensi Karyawan</span>
    <div class="btn-group btn-group-sm" role="group">
      <a href="<?= url('absensi?mode=harian') ?>" class="btn <?= $mode === 'harian' ? 'btn-brand' : 'btn-outline-secondary' ?>">Harian</a>
      <a href="<?= url('absensi?mode=bulanan') ?>" class="btn <?= $mode === 'bulanan' ? 'btn-brand' : 'btn-outline-secondary' ?>">Rekap Bulanan</a>
    </div>
  </div>
  <div class="card-body">
    <form method="GET" action="<?= url('absensi') ?>" class="d-flex gap-2 flex-wrap align-items-end">
      <input type="hidden" name="mode" value="<?= e($mode) ?>">
      <?php if ($mode === 'harian'): ?>
        <div>
          <label class="form-label small mb-1">Tanggal</label>
          <input type="date" name="tanggal" class="form-control form-control-sm" value="<?= e($tanggal) ?>">
        </div>
      <?php else: ?>
        <div>
          <label class="form-label small mb-1">Bulan</label>
          <select name="bulan" class="form-select form-select-sm" style="width:150px;">
            <?php for ($b = 1; $b <= 12; $b++): ?>
              <option value="<?= $b ?>" <?= $b == $bulan ? 'selected' : '' ?>><?= nama_bulan($b) ?></option>
            <?php endfor; ?>
          </select>
        </div>
        <div>
          <label class="form-label small mb-1">Tahun</label>
          <select name="tahun" class="form-select form-select-sm" style="width:110px;">
            <?php for ($t = (int) date('Y') - 2; $t <= (int) date('Y') + 1; $t++): ?>
              <option value="<?= $t ?>" <?= $t == $tahun ? 'selected' : '' ?>><?= $t ?></option>
            <?php endfor; ?>
          </select>
        </div>
      <?php endif; ?>
      <div>
        <label class="form-label small mb-1">Cari Karyawan</label>
        <input type="text" name="q" class="form-control form-control-sm" placeholder="Nama / NIP..." value="<?= e($keyword) ?>">
      </div>
      <div>
        <button class="btn btn-sm btn-outline-secondary" type="submit">Tampilkan</button>
      </div>
      <div class="ms-auto">
        <?php
          $exportParams = $mode === 'harian'
            ? 'mode=harian&tanggal=' . urlencode($tanggal) . '&q=' . urlencode($keyword)
            : 'mode=bulanan&bulan=' . $bulan . '&tahun=' . $tahun . '&q=' . urlencode($keyword);
        ?>
        <a href="<?= url('absensi/rekap-export-excel?' . $exportParams) ?>" class="btn btn-sm" style="background:var(--green-bg);color:var(--green);font-weight:600;">
          <i class="fa-solid fa-file-excel me-1"></i> Export Excel
        </a>
      </div>
    </form>
  </div>
</div>

<?php if ($mode === 'harian'): ?>
<div class="row g-3 mb-3">
  <div class="col-md-4">
    <div class="card stat-card">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="icon-box bg-icon-green"><i class="fa-solid fa-user-check"></i></div>
        <div>
          <div class="stat-label">Hadir</div>
          <div class="stat-value"><?= (int) $totalHadir ?></div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card stat-card">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="icon-box bg-icon-yellow"><i class="fa-solid fa-user-clock"></i></div>
        <div>
          <div class="stat-label">Izin / Sakit</div>
          <div class="stat-value"><?= (int) $totalIzinSakit ?></div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card stat-card">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="icon-box bg-icon-red"><i class="fa-solid fa-user-xmark"></i></div>
        <div>
          <div class="stat-label">Alfa / Belum Absen</div>
          <div class="stat-value"><?= (int) $totalAlfa ?></div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<div class="card">
  <div class="card-header">
    <?= $mode === 'harian' ? 'Rekap Absensi - ' . format_tanggal($tanggal) : 'Rekap Bulanan - ' . nama_bulan($bulan) . ' ' . $tahun ?>
    <span class="text-muted small">(<?= (int) $totalKaryawan ?> karyawan)</span>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <?php if ($mode === 'harian'): ?>
          <thead>
            <tr>
              <th>NIP</th>
              <th>Nama</th>
              <th>Departemen</th>
              <th>Jam Masuk</th>
              <th>Jam Keluar</th>
              <th>Status</th>
              <th>Lampiran</th>
              <th>Potongan</th>
              <?php if (AuthHelper::isAdmin()): ?>
                <th>Aksi</th>
              <?php endif; ?>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($data)): ?>
              <tr><td colspan="<?= AuthHelper::isAdmin() ? 9 : 8 ?>" class="text-center text-muted py-4">Tidak ada data karyawan.</td></tr>
            <?php else: ?>
              <?php foreach ($data as $d): ?>
                <tr>
                  <td><?= e($d['nip']) ?></td>
                  <td class="d-flex align-items-center gap-2">
                    <img src="<?= $d['foto'] ? UPLOAD_URL . 'foto_profil/' . e($d['foto']) : 'https://ui-avatars.com/api/?background=2f6fed&color=fff&name=' . urlencode($d['name']) ?>" class="avatar-sm">
                    <?= e($d['name']) ?>
                  </td>
                  <td><?= e($d['departemen_nama'] ?? '-') ?></td>
                  <td><?= e($d['check_in'] ?? '-') ?></td>
                  <td><?= e($d['check_out'] ?? '-') ?></td>
                  <td>
                    <?php 
                      $st = strtolower(trim($d['status'] ?? ''));
                      $checkIn = $d['check_in'] ?? '';

                      if (empty($st) && !empty($checkIn)) {
                          $st = ($checkIn > '08:00:00') ? 'terlambat' : 'hadir';
                      } elseif ($st === 'hadir' && !empty($checkIn) && $checkIn > '08:00:00') {
                          $st = 'terlambat';
                      }

                      if ($st === 'terlambat') {
                          echo '<span class="badge bg-warning text-dark">Terlambat</span>';
                      } elseif ($st === 'hadir') {
                          echo '<span class="badge bg-success">Hadir</span>';
                      } elseif ($st === 'izin') {
                          echo '<span class="badge bg-info text-dark">Izin</span>';
                      } elseif ($st === 'sakit') {
                          echo '<span class="badge bg-primary">Sakit</span>';
                      } elseif ($st === 'alfa') {
                          echo '<span class="badge bg-danger">Alfa</span>';
                      } else {
                          // Jika belum absen sama sekali, ubah jadi "Belum Hadir" (Badge Secondary/Abu-abu)
                          echo '<span class="badge bg-secondary">Belum Hadir</span>';
                      }
                    ?>
                  </td>
                  <td>
                    <?php
                      // Fallback ke foto_in untuk data lama yang sempat tersimpan salah
                      // kolom sebelum perbaikan (lihat AbsensiController::izin()).
                      $fileLampiran = $d['bukti_sakit'] ?? null;
                      if (empty($fileLampiran) && in_array($st, ['izin', 'sakit'], true)) {
                          $fileLampiran = $d['foto_in'] ?? null;
                      }
                    ?>
                    <?php if (!empty($fileLampiran)): ?>
                      <?php $extLampiran = strtolower(pathinfo($fileLampiran, PATHINFO_EXTENSION)); ?>
                      <button type="button" class="btn btn-sm btn-outline-secondary"
                              data-bs-toggle="modal" data-bs-target="#modalLampiranAbsensi"
                              data-file="<?= UPLOAD_URL . 'absensi/' . e($fileLampiran) ?>"
                              data-ext="<?= e($extLampiran) ?>"
                              data-nama="<?= e($d['name']) ?>">
                        <i class="fa-solid fa-paperclip me-1"></i>Lihat
                      </button>
                    <?php else: ?>
                      <span class="text-muted small">-</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <?php if (!empty($d['potongan_gaji']) && $d['potongan_gaji'] > 0): ?>
                      <div class="text-danger fw-semibold small">
                        Gaji: Rp <?= number_format($d['potongan_gaji'], 0, ',', '.') ?>
                      </div>
                    <?php endif; ?>
                    <?php if (!empty($d['potongan_uang_makan']) && $d['potongan_uang_makan'] > 0): ?>
                      <div class="text-danger fw-semibold small">
                        <i class="fa-solid fa-utensils me-1"></i>Uang Makan: Rp <?= number_format($d['potongan_uang_makan'], 0, ',', '.') ?>
                      </div>
                    <?php endif; ?>
                    <?php if (empty($d['potongan_gaji']) && empty($d['potongan_uang_makan'])): ?>
                      <span class="text-muted small">-</span>
                    <?php endif; ?>
                  </td>
                  <?php if (AuthHelper::isAdmin()): ?>
                    <td>
                      <?php if (!empty($d['absensi_id'])): ?>
                        <form method="POST" action="<?= url('absensi/hapus') ?>" class="form-hapus-absen d-inline">
                          <input type="hidden" name="id" value="<?= (int) $d['absensi_id'] ?>">
                          <input type="hidden" name="mode" value="harian">
                          <input type="hidden" name="tanggal" value="<?= e($tanggal) ?>">
                          <input type="hidden" name="q" value="<?= e($keyword) ?>">
                          <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus data absensi ini">
                            <i class="fa-solid fa-trash-can"></i>
                          </button>
                        </form>
                      <?php else: ?>
                        <span class="text-muted small">-</span>
                      <?php endif; ?>
                    </td>
                  <?php endif; ?>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        <?php else: ?>
          <thead>
            <tr><th>NIP</th><th>Nama</th><th class="text-center">Hadir</th><th class="text-center">Izin</th><th class="text-center">Sakit</th><th class="text-center">Alfa</th></tr>
          </thead>
          <tbody>
            <?php if (empty($data)): ?>
              <tr><td colspan="6" class="text-center text-muted py-4">Tidak ada data karyawan.</td></tr>
            <?php else: ?>
              <?php foreach ($data as $d): ?>
                <tr>
                  <td><?= e($d['nip']) ?></td>
                  <td class="d-flex align-items-center gap-2">
                    <img src="<?= $d['foto'] ? UPLOAD_URL . 'foto_profil/' . e($d['foto']) : 'https://ui-avatars.com/api/?background=2f6fed&color=fff&name=' . urlencode($d['name']) ?>" class="avatar-sm">
                    <?= e($d['name']) ?>
                  </td>
                  <td class="text-center"><span class="badge badge-status-hadir"><?= (int) $d['total_hadir'] ?></span></td>
                  <td class="text-center"><span class="badge badge-status-izin"><?= (int) $d['total_izin'] ?></span></td>
                  <td class="text-center"><span class="badge badge-status-sakit"><?= (int) $d['total_sakit'] ?></span></td>
                  <td class="text-center"><span class="badge badge-status-alfa"><?= (int) $d['total_alfa'] ?></span></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        <?php endif; ?>
      </table>
    </div>
  </div>
</div>

<?php if ($mode === 'harian'): ?>
<!-- Modal Preview Lampiran (foto/PDF bukti izin/sakit) -->
<div class="modal fade" id="modalLampiranAbsensi" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Lampiran <span id="lampiranAbsensiNama"></span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-center" id="lampiranAbsensiBody" style="min-height:200px;">
        <!-- Diisi otomatis lewat JS: <img> untuk jpg/jpeg/png, <iframe> untuk pdf -->
      </div>
      <div class="modal-footer">
        <a href="#" target="_blank" rel="noopener" id="lampiranAbsensiBukaTabBaru" class="btn btn-outline-secondary btn-sm">
          <i class="fa-solid fa-up-right-from-square me-1"></i>Buka di tab baru
        </a>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  var modalLampiranAbsensi = document.getElementById('modalLampiranAbsensi');
  if (modalLampiranAbsensi) {
    modalLampiranAbsensi.addEventListener('show.bs.modal', function (event) {
      var btn = event.relatedTarget;
      var fileUrl = btn.getAttribute('data-file');
      var ext = btn.getAttribute('data-ext');
      var nama = btn.getAttribute('data-nama') || '';
      var body = document.getElementById('lampiranAbsensiBody');
      var imgExt = ['jpg', 'jpeg', 'png'];

      document.getElementById('lampiranAbsensiNama').textContent = nama ? '- ' + nama : '';
      document.getElementById('lampiranAbsensiBukaTabBaru').setAttribute('href', fileUrl);

      if (imgExt.indexOf(ext) !== -1) {
        body.innerHTML = '<img src="' + fileUrl + '" alt="Lampiran bukti izin/sakit" style="max-width:100%; max-height:70vh; border-radius:8px;" onerror="this.replaceWith(Object.assign(document.createElement(\'div\'), {className:\'text-danger small py-4\', textContent:\'File lampiran tidak bisa ditampilkan (kemungkinan rusak/kosong atau sudah terhapus). Coba minta karyawan mengunggah ulang.\'}))">';
      } else {
        body.innerHTML = '<iframe src="' + fileUrl + '" style="width:100%; height:70vh; border:0;"></iframe>';
      }
    });
  }
});
</script>
<?php endif; ?>

<?php if (AuthHelper::isAdmin() && $mode === 'harian'): ?>
<?php
$extraScript = <<<'SCRIPT'
<script>
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.form-hapus-absen').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      if (!confirm('Hapus data absensi karyawan ini? Tindakan ini tidak dapat dibatalkan.')) {
        e.preventDefault();
      }
    });
  });
});
</script>
SCRIPT;
?>
<?php endif; ?>