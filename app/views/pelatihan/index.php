<?php if (AuthHelper::isHrOrAdmin()): ?>

  <div class="d-flex justify-content-end mb-3">
    <button class="btn btn-brand" data-bs-toggle="modal" data-bs-target="#modalTambahPelatihan">
      <i class="fa-solid fa-plus me-1"></i> Buat Pelatihan
    </button>
  </div>

  <div class="row g-3">
    <?php if (empty($daftarPelatihan)): ?>
      <div class="col-12"><div class="card"><div class="card-body text-center text-muted py-4">Belum ada pelatihan.</div></div></div>
    <?php else: ?>
      <?php foreach ($daftarPelatihan as $p): ?>
        <div class="col-lg-6">
          <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
              <span><?= e($p['judul']) ?></span>
              <span class="badge badge-status-info"><?= (int) $p['total_peserta'] ?> peserta</span>
            </div>
            <div class="card-body">
              <p class="text-muted small mb-2"><?= e($p['deskripsi']) ?></p>
              <p class="small mb-2">
                <i class="fa-regular fa-calendar me-1"></i>
                <?= $p['tanggal_mulai'] ? format_tanggal($p['tanggal_mulai']) : '-' ?>
                s/d
                <?= $p['tanggal_selesai'] ? format_tanggal($p['tanggal_selesai']) : '-' ?>
              </p>
              <?php if ($p['materi_file']): ?>
                <p class="small mb-3"><a href="<?= UPLOAD_URL . 'kontrak/' . e($p['materi_file']) ?>" target="_blank"><i class="fa-solid fa-paperclip me-1"></i>Materi Pelatihan</a></p>
              <?php endif; ?>
              <div class="table-responsive">
                <table class="table table-sm mb-0">
                  <thead><tr><th>Peserta</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
                  <tbody>
                    <?php if (empty($p['peserta'])): ?>
                      <tr><td colspan="3" class="text-muted small">Belum ada peserta terdaftar.</td></tr>
                    <?php else: ?>
                      <?php foreach ($p['peserta'] as $ps): ?>
                        <tr>
                          <td><?= e($ps['name']) ?></td>
                          <td><span class="badge badge-status-<?= $ps['status'] === 'selesai' ? 'aktif' : 'pending' ?>"><?= ucfirst($ps['status']) ?></span></td>
                          <td class="text-end">
                            <?php if ($ps['status'] !== 'selesai'): ?>
                              <form method="POST" action="<?= url('pelatihan/tandai-selesai/' . $ps['id']) ?>">
                                <button class="btn btn-sm btn-outline-brand">Tandai Selesai</button>
                              </form>
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
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <div class="modal fade" id="modalTambahPelatihan" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <form method="POST" action="<?= url('pelatihan/store') ?>" enctype="multipart/form-data">
          <div class="modal-header">
            <h5 class="modal-title">Buat Pelatihan Baru</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Judul <span class="text-danger">*</span></label>
              <input type="text" name="judul" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Deskripsi</label>
              <textarea name="deskripsi" class="form-control" rows="3"></textarea>
            </div>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">Tanggal Mulai</label>
                <input type="date" name="tanggal_mulai" class="form-control">
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Tanggal Selesai</label>
                <input type="date" name="tanggal_selesai" class="form-control">
              </div>
            </div>
            <div class="mb-1">
              <label class="form-label">Materi (opsional)</label>
              <input type="file" name="materi" class="form-control" accept=".pdf,.doc,.docx,.ppt,.pptx">
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

  <div class="row g-3">
    <?php if (empty($daftarPelatihan)): ?>
      <div class="col-12"><div class="card"><div class="card-body text-center text-muted py-4">Belum ada pelatihan tersedia.</div></div></div>
    <?php else: ?>
      <?php foreach ($daftarPelatihan as $p): ?>
        <div class="col-lg-6">
          <div class="card h-100">
            <div class="card-header"><?= e($p['judul']) ?></div>
            <div class="card-body">
              <p class="text-muted small mb-2"><?= e($p['deskripsi']) ?></p>
              <p class="small mb-3">
                <i class="fa-regular fa-calendar me-1"></i>
                <?= $p['tanggal_mulai'] ? format_tanggal($p['tanggal_mulai']) : '-' ?>
                s/d
                <?= $p['tanggal_selesai'] ? format_tanggal($p['tanggal_selesai']) : '-' ?>
              </p>
              <?php if ($p['materi_file']): ?>
                <p class="small mb-3"><a href="<?= UPLOAD_URL . 'kontrak/' . e($p['materi_file']) ?>" target="_blank"><i class="fa-solid fa-paperclip me-1"></i>Materi Pelatihan</a></p>
              <?php endif; ?>
              <?php if (in_array($p['id'], $idTerdaftar ?? [], true)): ?>
                <span class="badge badge-status-aktif"><i class="fa-solid fa-check me-1"></i>Anda sudah terdaftar</span>
              <?php else: ?>
                <form method="POST" action="<?= url('pelatihan/daftar/' . $p['id']) ?>">
                  <button class="btn btn-sm btn-brand"><i class="fa-solid fa-plus me-1"></i>Daftar Pelatihan</button>
                </form>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

<?php endif; ?>
