<?php if (AuthHelper::isHrOrAdmin()): ?>

  <div class="card">
    <div class="card-header">Daftar Pengajuan Resign / Offboarding</div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead>
            <tr><th>Karyawan</th><th>Tgl Pengajuan</th><th>Tgl Efektif</th><th>Alasan</th><th>Status</th><th class="text-end">Aksi</th></tr>
          </thead>
          <tbody>
            <?php if (empty($daftar)): ?>
              <tr><td colspan="6" class="text-center text-muted py-4">Belum ada pengajuan resign.</td></tr>
            <?php else: ?>
              <?php foreach ($daftar as $r): ?>
                <tr>
                  <td class="d-flex align-items-center gap-2">
                    <img src="<?= $r['foto'] ? UPLOAD_URL . 'foto_profil/' . e($r['foto']) : 'https://ui-avatars.com/api/?background=2f6fed&color=fff&name=' . urlencode($r['name']) ?>" class="avatar-sm">
                    <?= e($r['name']) ?>
                  </td>
                  <td><?= format_tanggal($r['tanggal_pengajuan']) ?></td>
                  <td><?= format_tanggal($r['tanggal_efektif']) ?></td>
                  <td><?= e($r['alasan']) ?></td>
                  <td>
                    <?php $map = ['pending'=>'pending','diproses'=>'info','selesai'=>'aktif','ditolak'=>'ditolak']; ?>
                    <span class="badge badge-status-<?= $map[$r['status']] ?>"><?= ucfirst($r['status']) ?></span>
                  </td>
                  <td class="text-end">
                    <button class="btn btn-sm btn-outline-brand" data-bs-toggle="modal" data-bs-target="#modalDetail<?= $r['id'] ?>">
                      <i class="fa-solid fa-eye"></i> Detail
                    </button>
                  </td>
                </tr>

                <div class="modal fade" id="modalDetail<?= $r['id'] ?>" tabindex="-1">
                  <div class="modal-dialog modal-dialog-scrollable">
                    <div class="modal-content">
                      <form method="POST" action="<?= url('resign/proses/' . $r['id']) ?>">
                        <div class="modal-header">
                          <h5 class="modal-title">Detail Resign: <?= e($r['name']) ?></h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                          <p><strong>Alasan:</strong><br><?= nl2br(e($r['alasan'])) ?></p>
                          <p><strong>Exit Interview:</strong><br><?= $r['exit_interview'] ? nl2br(e($r['exit_interview'])) : '<span class="text-muted">Belum diisi</span>' ?></p>
                          <div class="mb-3">
                            <label class="form-label">Status Proses</label>
                            <select name="status" class="form-select">
                              <option value="pending" <?= $r['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                              <option value="diproses" <?= $r['status'] === 'diproses' ? 'selected' : '' ?>>Diproses</option>
                              <option value="selesai" <?= $r['status'] === 'selesai' ? 'selected' : '' ?>>Selesai (Nonaktifkan Karyawan)</option>
                              <option value="ditolak" <?= $r['status'] === 'ditolak' ? 'selected' : '' ?>>Ditolak</option>
                            </select>
                          </div>
                          <div class="mb-1">
                            <label class="form-label">Catatan HRD</label>
                            <textarea name="catatan_hrd" class="form-control" rows="3"><?= e($r['catatan_hrd'] ?? '') ?></textarea>
                          </div>
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                          <button type="submit" class="btn btn-brand">Simpan</button>
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

<?php else: ?>

  <?php if ($pengajuanAktif): ?>
    <div class="alert alert-info">
      <i class="fa-solid fa-circle-info me-1"></i>
      Anda memiliki pengajuan resign yang sedang <strong><?= e($pengajuanAktif['status']) ?></strong>,
      efektif per <?= format_tanggal($pengajuanAktif['tanggal_efektif']) ?>.
    </div>
  <?php else: ?>
    <div class="d-flex justify-content-end mb-3">
      <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalAjukanResign">
        <i class="fa-solid fa-door-open me-1"></i> Ajukan Resign
      </button>
    </div>
  <?php endif; ?>

  <div class="card">
    <div class="card-header">Riwayat Pengajuan Resign Saya</div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead><tr><th>Tgl Pengajuan</th><th>Tgl Efektif</th><th>Alasan</th><th>Status</th><th>Catatan HRD</th></tr></thead>
          <tbody>
            <?php if (empty($daftar)): ?>
              <tr><td colspan="5" class="text-center text-muted py-4">Belum pernah mengajukan resign.</td></tr>
            <?php else: ?>
              <?php foreach ($daftar as $r): ?>
                <tr>
                  <td><?= format_tanggal($r['tanggal_pengajuan']) ?></td>
                  <td><?= format_tanggal($r['tanggal_efektif']) ?></td>
                  <td><?= e($r['alasan']) ?></td>
                  <td>
                    <?php $map = ['pending'=>'pending','diproses'=>'info','selesai'=>'aktif','ditolak'=>'ditolak']; ?>
                    <span class="badge badge-status-<?= $map[$r['status']] ?>"><?= ucfirst($r['status']) ?></span>
                  </td>
                  <td><?= e($r['catatan_hrd'] ?? '-') ?></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modalAjukanResign" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <form method="POST" action="<?= url('resign/ajukan') ?>">
          <div class="modal-header">
            <h5 class="modal-title">Ajukan Pengunduran Diri</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Tanggal Efektif Resign <span class="text-danger">*</span></label>
              <input type="date" name="tanggal_efektif" class="form-control" min="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Alasan Resign <span class="text-danger">*</span></label>
              <textarea name="alasan" class="form-control" rows="3" required></textarea>
            </div>
            <div class="mb-1">
              <label class="form-label">Exit Interview (opsional)</label>
              <textarea name="exit_interview" class="form-control" rows="3" placeholder="Masukan, kesan, dan saran untuk perusahaan..."></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Yakin ingin mengajukan resign?')">Kirim Pengajuan</button>
          </div>
        </form>
      </div>
    </div>
  </div>

<?php endif; ?>
