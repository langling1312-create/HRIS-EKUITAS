<?php if (AuthHelper::isHrOrAdmin()): ?>

  <div class="d-flex justify-content-end mb-3">
    <button class="btn btn-brand" data-bs-toggle="modal" data-bs-target="#modalTambahTarget">
      <i class="fa-solid fa-plus me-1"></i> Tetapkan Target KPI
    </button>
  </div>

  <div class="card">
    <div class="card-header">Daftar KPI Karyawan</div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead>
            <tr><th>Karyawan</th><th>Periode</th><th>Target</th><th>Nilai</th><th>Status</th><th class="text-end">Aksi</th></tr>
          </thead>
          <tbody>
            <?php if (empty($daftar)): ?>
              <tr><td colspan="6" class="text-center text-muted py-4">Belum ada data KPI.</td></tr>
            <?php else: ?>
              <?php foreach ($daftar as $k): ?>
                <tr>
                  <td class="d-flex align-items-center gap-2">
                    <img src="<?= $k['foto'] ? UPLOAD_URL . 'foto_profil/' . e($k['foto']) : 'https://ui-avatars.com/api/?background=2f6fed&color=fff&name=' . urlencode($k['name']) ?>" class="avatar-sm">
                    <?= e($k['name']) ?>
                  </td>
                  <td><?= e($k['periode']) ?></td>
                  <td><?= e($k['deskripsi_target']) ?> <?= $k['target_value'] ? '(' . e($k['target_value']) . ')' : '' ?></td>
                  <td><?= $k['nilai'] !== null ? (int) $k['nilai'] . '/100' : '-' ?></td>
                  <td><span class="badge badge-status-<?= $k['status'] === 'dinilai' ? 'aktif' : 'pending' ?>"><?= $k['status'] === 'dinilai' ? 'Dinilai' : 'Berjalan' ?></span></td>
                  <td class="text-end">
                    <button class="btn btn-sm btn-outline-brand" data-bs-toggle="modal" data-bs-target="#modalNilai<?= $k['id'] ?>">
                      <i class="fa-solid fa-star"></i> Beri Nilai
                    </button>
                  </td>
                </tr>

                <div class="modal fade" id="modalNilai<?= $k['id'] ?>" tabindex="-1">
                  <div class="modal-dialog">
                    <div class="modal-content">
                      <form method="POST" action="<?= url('kpi/beri-nilai/' . $k['id']) ?>">
                        <div class="modal-header">
                          <h5 class="modal-title">Nilai KPI: <?= e($k['name']) ?></h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                          <p class="text-muted small"><?= e($k['deskripsi_target']) ?></p>
                          <div class="mb-3">
                            <label class="form-label">Nilai (0-100)</label>
                            <input type="number" name="nilai" class="form-control" min="0" max="100" value="<?= (int) ($k['nilai'] ?? 80) ?>" required>
                          </div>
                          <div class="mb-1">
                            <label class="form-label">Catatan Atasan</label>
                            <textarea name="catatan_atasan" class="form-control" rows="3"><?= e($k['catatan_atasan'] ?? '') ?></textarea>
                          </div>
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                          <button type="submit" class="btn btn-brand">Simpan Nilai</button>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modalTambahTarget" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <form method="POST" action="<?= url('kpi/tambah-target') ?>">
          <div class="modal-header">
            <h5 class="modal-title">Tetapkan Target KPI</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Karyawan <span class="text-danger">*</span></label>
              <select name="user_id" class="form-select" required>
                <option value="">- Pilih karyawan -</option>
                <?php foreach ($daftarKaryawan as $k): ?>
                  <option value="<?= $k['user_id'] ?>"><?= e($k['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Periode</label>
              <input type="text" name="periode" class="form-control" placeholder="Contoh: Q1 2026" value="<?= date('Y') ?>">
            </div>
            <div class="mb-3">
              <label class="form-label">Deskripsi Target <span class="text-danger">*</span></label>
              <textarea name="deskripsi_target" class="form-control" rows="2" required></textarea>
            </div>
            <div class="mb-1">
              <label class="form-label">Target Terukur (opsional)</label>
              <input type="text" name="target_value" class="form-control" placeholder="Contoh: 95% kepuasan pelanggan">
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

<?php else: ?>

  <div class="row g-3 mb-3">
    <div class="col-md-6">
      <div class="card stat-card">
        <div class="card-body d-flex align-items-center gap-3">
          <div class="icon-box bg-icon-purple"><i class="fa-solid fa-star"></i></div>
          <div>
            <div class="stat-label">Rata-rata Nilai Kinerja</div>
            <div class="stat-value"><?= $rataRata > 0 ? round($rataRata, 1) : '-' ?></div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-6 d-flex align-items-center justify-content-md-end">
      <button class="btn btn-brand" data-bs-toggle="modal" data-bs-target="#modalTargetSaya">
        <i class="fa-solid fa-plus me-1"></i> Tambah Target Pribadi
      </button>
    </div>
  </div>

  <div class="card">
    <div class="card-header">Target &amp; Penilaian KPI Saya</div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead><tr><th>Periode</th><th>Target</th><th>Nilai</th><th>Catatan Atasan</th><th>Status</th></tr></thead>
          <tbody>
            <?php if (empty($daftar)): ?>
              <tr><td colspan="5" class="text-center text-muted py-4">Belum ada target KPI.</td></tr>
            <?php else: ?>
              <?php foreach ($daftar as $k): ?>
                <tr>
                  <td><?= e($k['periode']) ?></td>
                  <td><?= e($k['deskripsi_target']) ?> <?= $k['target_value'] ? '(' . e($k['target_value']) . ')' : '' ?></td>
                  <td><?= $k['nilai'] !== null ? (int) $k['nilai'] . '/100' : '-' ?></td>
                  <td><?= e($k['catatan_atasan'] ?? '-') ?></td>
                  <td><span class="badge badge-status-<?= $k['status'] === 'dinilai' ? 'aktif' : 'pending' ?>"><?= $k['status'] === 'dinilai' ? 'Dinilai' : 'Berjalan' ?></span></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modalTargetSaya" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <form method="POST" action="<?= url('kpi/tambah-target') ?>">
          <div class="modal-header">
            <h5 class="modal-title">Tambah Target Pribadi</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Periode</label>
              <input type="text" name="periode" class="form-control" placeholder="Contoh: Q1 2026" value="<?= date('Y') ?>">
            </div>
            <div class="mb-3">
              <label class="form-label">Deskripsi Target <span class="text-danger">*</span></label>
              <textarea name="deskripsi_target" class="form-control" rows="2" required></textarea>
            </div>
            <div class="mb-1">
              <label class="form-label">Target Terukur (opsional)</label>
              <input type="text" name="target_value" class="form-control" placeholder="Contoh: Selesaikan 10 proyek">
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

<?php endif; ?>
