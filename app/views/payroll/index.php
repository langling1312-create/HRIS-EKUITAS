<div class="card mb-3">
  <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
    <span>Penggajian - <?= nama_bulan($bulan) ?> <?= $tahun ?></span>
    <div class="d-flex gap-2 align-items-center">
      <form method="GET" action="<?= url('payroll') ?>" class="d-flex gap-2">
        <select name="bulan" class="form-select form-select-sm" style="width:140px;">
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
      </form>
      <button class="btn btn-brand" data-bs-toggle="modal" data-bs-target="#modalProsesGaji">
        <i class="fa-solid fa-gears me-1"></i> Proses Gaji <?= nama_bulan($bulan) ?> <?= $tahun ?>
      </button>
    </div>
  </div>
</div>

<div class="row g-3 mb-3">
  <div class="col-xl-3 col-md-6">
    <div class="card stat-card">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="icon-box bg-icon-blue"><i class="fa-solid fa-sack-dollar"></i></div>
        <div>
          <div class="stat-label">Total Gaji Bulan Ini</div>
          <div class="fs-6 fw-bold"><?= format_rupiah($totalGajiBulanIni) ?></div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-xl-3 col-md-6">
    <div class="card stat-card">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="icon-box bg-icon-green"><i class="fa-solid fa-users"></i></div>
        <div>
          <div class="stat-label">Jumlah Karyawan</div>
          <div class="stat-value"><?= (int) $totalKaryawanAktif ?></div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-xl-3 col-md-6">
    <div class="card stat-card">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="icon-box bg-icon-yellow"><i class="fa-solid fa-circle-check"></i></div>
        <div>
          <div class="stat-label">Sudah Diproses</div>
          <div class="stat-value"><?= (int) $sudahDiproses ?></div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-xl-3 col-md-6">
    <div class="card stat-card">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="icon-box bg-icon-red"><i class="fa-solid fa-clock"></i></div>
        <div>
          <div class="stat-label">Belum Diproses</div>
          <div class="stat-value"><?= (int) $belumDiproses ?></div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <span>Rekap Penggajian</span>
    <div class="d-flex gap-2">
      <a href="<?= url('laporan/export-excel?bulan=' . $bulan . '&tahun=' . $tahun) ?>" class="btn btn-sm btn-outline-brand"><i class="fa-solid fa-file-excel me-1"></i>Export Excel</a>
    </div>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead>
          <tr>
            <th>NIP</th><th>Nama</th><th>Gaji Pokok</th><th>Tunjangan</th><th>Potongan</th><th>Total Bersih</th><th>Status</th><th class="text-end">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($daftarPayroll)): ?>
            <tr><td colspan="8" class="text-center text-muted py-4">Belum ada data payroll untuk periode ini.</td></tr>
          <?php else: ?>
            <?php foreach ($daftarPayroll as $p): ?>
              <tr>
                <td><?= e($p['nip'] ?? '-') ?></td>
                <td><?= e($p['name']) ?></td>
                <td><?= format_rupiah($p['gaji_pokok']) ?></td>
                <td><?= format_rupiah($p['tunjangan']) ?></td>
                <td class="text-danger">- <?= format_rupiah($p['potongan']) ?></td>
                <td class="fw-bold"><?= format_rupiah($p['total']) ?></td>
                <td><span class="badge badge-status-disetujui">Diproses</span></td>
                <td class="text-end">
                  <a href="<?= url('payroll/edit/' . $p['id']) ?>" class="btn btn-sm btn-outline-secondary" title="Edit Gaji">
                    <i class="fa-solid fa-pen"></i>
                  </a>
                  <a href="<?= url('payroll/cetak/' . $p['id']) ?>" target="_blank" class="btn btn-sm btn-outline-brand" title="Cetak">
                    <i class="fa-solid fa-print"></i>
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<div class="modal fade" id="modalProsesGaji" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <form method="POST" action="<?= url('payroll/proses') ?>">
        <div class="modal-header">
          <h5 class="modal-title">Proses Gaji - <?= nama_bulan($bulan) ?> <?= $tahun ?></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="bulan" value="<?= $bulan ?>">
          <input type="hidden" name="tahun" value="<?= $tahun ?>">
          <p class="text-muted small">Masukkan rincian tunjangan &amp; potongan (opsional) untuk masing-masing karyawan aktif. Gaji pokok otomatis diambil dari data karyawan. BPJS Kesehatan/Ketenagakerjaan &amp; PPh 21 dihitung otomatis dari Pengaturan Sistem, dan bisa diubah lagi lewat tombol "Edit Gaji" setelah diproses. Karyawan yang sudah diproses pada periode ini akan dilewati otomatis.</p>
          <div class="table-responsive">
            <table class="table table-sm align-middle">
              <thead>
                <tr>
                  <th>Nama</th>
                  <th>Gaji Pokok</th>
                  <th>Tunj. Jabatan</th>
                  <th>Tunj. Transport</th>
                  <th>Tunj. BPJS</th>
                  <th>Tunj. Sakit</th>
                  <th>Tunj. Lainnya</th>
                  <th>Potongan</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($daftarKaryawanAktif as $k): if (!$k['status_aktif']) continue; ?>
                  <tr>
                    <td class="text-nowrap"><?= e($k['name']) ?></td>
                    <td class="text-nowrap"><?= format_rupiah($k['gaji_pokok']) ?></td>
                    <td><input type="number" name="tunjangan_jabatan[<?= $k['user_id'] ?>]" class="form-control form-control-sm" value="0" min="0" style="min-width:110px;"></td>
                    <td><input type="number" name="tunjangan_transport[<?= $k['user_id'] ?>]" class="form-control form-control-sm" value="0" min="0" style="min-width:110px;"></td>
                    <td><input type="number" name="tunjangan_bpjs[<?= $k['user_id'] ?>]" class="form-control form-control-sm" value="0" min="0" style="min-width:110px;"></td>
                    <td><input type="number" name="tunjangan_sakit[<?= $k['user_id'] ?>]" class="form-control form-control-sm" value="0" min="0" style="min-width:110px;"></td>
                    <td><input type="number" name="tunjangan_lainnya[<?= $k['user_id'] ?>]" class="form-control form-control-sm" value="0" min="0" style="min-width:110px;"></td>
                    <td><input type="number" name="potongan[<?= $k['user_id'] ?>]" class="form-control form-control-sm" value="0" min="0" style="min-width:110px;"></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-brand" onclick="return confirm('Proses gaji untuk periode ini?')">Proses Sekarang</button>
        </div>
      </form>
    </div>
  </div>
</div>
