<?php if (AuthHelper::isHrOrAdmin()): ?>

  <div class="card">
    <div class="card-header">Daftar Klaim Reimbursement</div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead>
            <tr><th>Karyawan</th><th>Jenis</th><th>Jumlah</th><th>Deskripsi</th><th>Bukti</th><th>Status</th><th class="text-end">Aksi</th></tr>
          </thead>
          <tbody>
            <?php if (empty($daftar)): ?>
              <tr><td colspan="7" class="text-center text-muted py-4">Belum ada pengajuan klaim.</td></tr>
            <?php else: ?>
              <?php foreach ($daftar as $r): ?>
                <tr>
                  <td class="d-flex align-items-center gap-2">
                    <img src="<?= $r['foto'] ? UPLOAD_URL . 'foto_profil/' . e($r['foto']) : 'https://ui-avatars.com/api/?background=2f6fed&color=fff&name=' . urlencode($r['name']) ?>" class="avatar-sm">
                    <?= e($r['name']) ?>
                  </td>
                  <td><?= e($r['jenis']) ?></td>
                  <td class="fw-semibold"><?= format_rupiah($r['jumlah']) ?></td>
                  <td><?= e($r['deskripsi']) ?></td>
                  <td>
                    <?php if ($r['bukti_file']): ?>
                      <a href="<?= UPLOAD_URL . 'kontrak/' . e($r['bukti_file']) ?>" target="_blank"><i class="fa-solid fa-paperclip"></i> Lihat</a>
                    <?php else: ?>-<?php endif; ?>
                  </td>
                  <td><?= badge_status_cuti($r['status']) ?></td>
                  <td class="text-end">
                    <?php if ($r['status'] === 'pending'): ?>
                      <form method="POST" action="<?= url('reimbursement/setujui/' . $r['id']) ?>" class="d-inline">
                        <button class="btn btn-sm btn-success" onclick="return confirm('Setujui klaim ini?')"><i class="fa-solid fa-check"></i></button>
                      </form>
                      <form method="POST" action="<?= url('reimbursement/tolak/' . $r['id']) ?>" class="d-inline">
                        <button class="btn btn-sm btn-danger" onclick="return confirm('Tolak klaim ini?')"><i class="fa-solid fa-xmark"></i></button>
                      </form>
                    <?php else: ?>
                      <span class="text-muted small">-</span>
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

<?php else: ?>

  <div class="d-flex justify-content-end mb-3">
    <button class="btn btn-brand" data-bs-toggle="modal" data-bs-target="#modalAjukanKlaim">
      <i class="fa-solid fa-plus me-1"></i> Ajukan Klaim
    </button>
  </div>

  <div class="card">
    <div class="card-header">Riwayat Klaim Saya</div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead><tr><th>Jenis</th><th>Jumlah</th><th>Deskripsi</th><th>Status</th><th>Catatan</th><th>Diajukan</th></tr></thead>
          <tbody>
            <?php if (empty($daftar)): ?>
              <tr><td colspan="6" class="text-center text-muted py-4">Belum ada pengajuan klaim.</td></tr>
            <?php else: ?>
              <?php foreach ($daftar as $r): ?>
                <tr>
                  <td><?= e($r['jenis']) ?></td>
                  <td class="fw-semibold"><?= format_rupiah($r['jumlah']) ?></td>
                  <td><?= e($r['deskripsi']) ?></td>
                  <td><?= badge_status_cuti($r['status']) ?></td>
                  <td><?= e($r['catatan_approval'] ?? '-') ?></td>
                  <td><?= format_tanggal($r['created_at'], 'd M Y') ?></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modalAjukanKlaim" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <form method="POST" action="<?= url('reimbursement/ajukan') ?>" enctype="multipart/form-data">
          <div class="modal-header">
            <h5 class="modal-title">Ajukan Klaim Reimbursement</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Jenis Klaim <span class="text-danger">*</span></label>
              <select name="jenis" class="form-select" required>
                <option value="Biaya Operasional">Biaya Operasional</option>
                <option value="Kesehatan">Kesehatan</option>
                <option value="Perjalanan Dinas">Perjalanan Dinas</option>
                <option value="Lainnya">Lainnya</option>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Jumlah <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text">Rp</span>
                <input type="number" name="jumlah" class="form-control" min="1" required>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label">Deskripsi</label>
              <textarea name="deskripsi" class="form-control" rows="3"></textarea>
            </div>
            <div class="mb-1">
              <label class="form-label">Upload Bukti / Struk</label>
              <input type="file" name="bukti" class="form-control" accept="image/*,.pdf">
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-brand">Kirim Pengajuan</button>
          </div>
        </form>
      </div>
    </div>
  </div>

<?php endif; ?>
