<div class="card mb-3">
  <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
    <span>Rekap Absensi Saya</span>
    <form method="GET" action="<?= url('absensi/rekap-saya') ?>" class="d-flex gap-2">
      <select name="bulan" class="form-select form-select-sm" style="width:130px;">
        <?php for ($b = 1; $b <= 12; $b++): ?>
          <option value="<?= $b ?>" <?= $b == $bulan ? 'selected' : '' ?>><?= nama_bulan($b) ?></option>
        <?php endfor; ?>
      </select>
      <select name="tahun" class="form-select form-select-sm" style="width:110px;">
        <?php for ($t = (int) date('Y') - 2; $t <= (int) date('Y') + 1; $t++): ?>
          <option value="<?= $t ?>" <?= $t == $tahun ? 'selected' : '' ?>><?= $t ?></option>
        <?php endfor; ?>
      </select>
      <button class="btn btn-sm btn-outline-secondary" type="submit">Filter</button>
      <a href="<?= url('absensi/export-excel?bulan=' . $bulan . '&tahun=' . $tahun) ?>" class="btn btn-sm btn-outline-brand">
        <i class="fa-solid fa-file-excel me-1"></i> Export Excel
      </a>
    </form>
  </div>
  <div class="card-body">
    <p class="text-muted small mb-0">
      <i class="fa-solid fa-circle-info me-1"></i>
      Rekap ini hanya menampilkan absensi milik Anda sendiri (<?= e(AuthHelper::name()) ?>) untuk periode <?= nama_bulan($bulan) ?> <?= $tahun ?>.
    </p>
  </div>
</div>

<div class="row g-3 mb-3">
  <div class="col-6 col-lg-3">
    <div class="card stat-card h-100">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="icon-box bg-icon-green"><i class="fa-solid fa-user-check"></i></div>
        <div>
          <div class="stat-label">Hadir</div>
          <div class="stat-value"><?= (int) $totalHadir ?></div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="card stat-card h-100">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="icon-box bg-icon-yellow"><i class="fa-solid fa-calendar-check"></i></div>
        <div>
          <div class="stat-label">Izin</div>
          <div class="stat-value"><?= (int) $totalIzin ?></div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="card stat-card h-100">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="icon-box bg-icon-info"><i class="fa-solid fa-notes-medical"></i></div>
        <div>
          <div class="stat-label">Sakit</div>
          <div class="stat-value"><?= (int) $totalSakit ?></div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="card stat-card h-100">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="icon-box bg-icon-red"><i class="fa-solid fa-user-xmark"></i></div>
        <div>
          <div class="stat-label">Alfa</div>
          <div class="stat-value"><?= (int) $totalAlfa ?></div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-header">
    Detail Absensi - <?= nama_bulan($bulan) ?> <?= $tahun ?>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead>
          <tr>
            <th>Tanggal</th>
            <th>Hari</th>
            <th>Shift</th>
            <th>Jam Masuk</th>
            <th>Jam Keluar</th>
            <th>Status</th>
            <th>Foto</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($riwayat)): ?>
            <tr><td colspan="7" class="text-center text-muted py-4">Belum ada riwayat absensi pada periode ini.</td></tr>
          <?php else: ?>
            <?php foreach ($riwayat as $r): ?>
              <tr>
                <td><?= format_tanggal($r['tanggal']) ?></td>
                <td><?= e(date('l', strtotime($r['tanggal']))) ?></td>
                <td><?= e($r['shift'] ?? '-') ?></td>
                <td><?= e($r['check_in'] ?? '-') ?></td>
                <td><?= e($r['check_out'] ?? '-') ?></td>
                <td>
                  <?php
                    $status = strtolower(trim($r['status'] ?? ''));
                    if (empty($status)) {
                        $shift_parts = explode('-', $r['shift'] ?? '');
                        $jam_mulai = $shift_parts[0] ?? '08:00';
                        $check_in = $r['check_in'] ?? '00:00:00';
                        $status = ($check_in > $jam_mulai) ? 'terlambat' : 'hadir';
                    }

                    if ($status === 'terlambat') {
                        echo '<span class="badge bg-warning text-dark">Terlambat</span>';
                    } elseif ($status === 'hadir') {
                        echo '<span class="badge bg-success">Hadir</span>';
                    } elseif ($status === 'izin') {
                        echo '<span class="badge bg-info text-dark">Izin</span>';
                    } elseif ($status === 'sakit') {
                        echo '<span class="badge bg-primary">Sakit</span>';
                    } elseif ($status === 'alfa') {
                        echo '<span class="badge bg-danger">Alfa</span>';
                    } else {
                        echo '<span class="badge bg-secondary">' . e(ucfirst($status)) . '</span>';
                    }
                  ?>

                  <?php if (in_array($status, ['terlambat', 'alfa'], true) && !empty($r['potongan_gaji'])): ?>
                    <div class="text-danger small fw-semibold mt-1">
                      Pot. Gaji: Rp <?= number_format($r['potongan_gaji'], 0, ',', '.') ?>
                    </div>
                  <?php endif; ?>
                  <?php if (!empty($r['potongan_uang_makan'])): ?>
                    <div class="text-danger small fw-semibold mt-1">
                      <i class="fa-solid fa-utensils me-1"></i>Uang Makan Hangus: Rp <?= number_format($r['potongan_uang_makan'], 0, ',', '.') ?>
                    </div>
                  <?php endif; ?>
                </td>
                <td>
                  <?php
                    $fileRow = $r['foto_in'] ?? $r['bukti_sakit'] ?? null;
                    $isIzinSakit = in_array($status, ['izin', 'sakit'], true);
                  ?>

                  <?php if (!empty($fileRow)): ?>
                    <?php if ($isIzinSakit): ?>
                      <?php $extRow = strtolower(pathinfo($fileRow, PATHINFO_EXTENSION)); ?>
                      <?php if ($extRow === 'pdf'): ?>
                        <a href="<?= UPLOAD_URL . 'absensi/' . e($fileRow) ?>" target="_blank" class="small">
                          <i class="fa-solid fa-file-pdf text-danger"></i> Lihat PDF
                        </a>
                      <?php else: ?>
                        <a href="<?= UPLOAD_URL . 'absensi/' . e($fileRow) ?>" target="_blank">
                          <img src="<?= UPLOAD_URL . 'absensi/' . e($fileRow) ?>" class="avatar-sm" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;" onerror="this.style.display='none';">
                        </a>
                      <?php endif; ?>
                    <?php else: ?>
                      <img src="<?= UPLOAD_URL . 'absensi/' . e($fileRow) ?>" class="avatar-sm" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;" onerror="this.style.display='none';">
                    <?php endif; ?>
                  <?php else: ?>
                    <span class="text-muted">-</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
