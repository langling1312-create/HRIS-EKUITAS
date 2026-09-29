<div class="row g-3 mb-3">
  <div class="col-md-6">
    <div class="card stat-card">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="icon-box bg-icon-blue"><i class="fa-solid fa-briefcase"></i></div>
        <div>
          <div class="stat-label">Lowongan Terbuka</div>
          <div class="stat-value"><?= count(array_filter($daftarLowongan, fn($l) => $l['status'] === 'buka')) ?></div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card stat-card">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="icon-box bg-icon-yellow"><i class="fa-solid fa-user-plus"></i></div>
        <div>
          <div class="stat-label">Pelamar Baru</div>
          <div class="stat-value"><?= (int) $pelamarBaru ?></div>
        </div>
      </div>
    </div>
  </div>
</div>

<ul class="nav nav-tabs mb-3">
  <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabLowongan">Lowongan</button></li>
  <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabPelamar">Pelamar</button></li>
</ul>

<div class="tab-content">
  <div class="tab-pane fade show active" id="tabLowongan">
    <div class="d-flex justify-content-end mb-3">
      <button class="btn btn-brand" data-bs-toggle="modal" data-bs-target="#modalLowongan">
        <i class="fa-solid fa-plus me-1"></i> Buka Lowongan
      </button>
    </div>
    <div class="card">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead><tr><th>Judul</th><th>Departemen</th><th>Pelamar</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
            <tbody>
              <?php if (empty($daftarLowongan)): ?>
                <tr><td colspan="5" class="text-center text-muted py-4">Belum ada lowongan.</td></tr>
              <?php else: ?>
                <?php foreach ($daftarLowongan as $l): ?>
                  <tr>
                    <td class="fw-semibold"><?= e($l['judul']) ?></td>
                    <td><?= e($l['departemen_nama'] ?? '-') ?></td>
                    <td><?= (int) $l['total_pelamar'] ?> orang</td>
                    <td><span class="badge badge-status-<?= $l['status'] === 'buka' ? 'aktif' : 'nonaktif' ?>"><?= ucfirst($l['status']) ?></span></td>
                    <td class="text-end">
                      <?php if ($l['status'] === 'buka'): ?>
                        <form method="POST" action="<?= url('rekrutmen/tutup-lowongan/' . $l['id']) ?>" class="d-inline">
                          <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Tutup lowongan ini?')">Tutup</button>
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
  </div>

  <div class="tab-pane fade" id="tabPelamar">
    <div class="d-flex justify-content-end mb-3">
      <button class="btn btn-brand" data-bs-toggle="modal" data-bs-target="#modalPelamar">
        <i class="fa-solid fa-plus me-1"></i> Tambah Pelamar
      </button>
    </div>
    <div class="card">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead><tr><th>Nama</th><th>Lowongan</th><th>Email</th><th>No HP</th><th>CV</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
            <tbody>
              <?php if (empty($daftarPelamar)): ?>
                <tr><td colspan="7" class="text-center text-muted py-4">Belum ada pelamar.</td></tr>
              <?php else: ?>
                <?php foreach ($daftarPelamar as $p): ?>
                  <tr>
                    <td><?= e($p['nama']) ?></td>
                    <td><?= e($p['lowongan_judul']) ?></td>
                    <td><?= e($p['email']) ?></td>
                    <td><?= e($p['no_hp'] ?? '-') ?></td>
                    <td><?php if ($p['cv_file']): ?><a href="<?= UPLOAD_URL . 'kontrak/' . e($p['cv_file']) ?>" target="_blank">Lihat CV</a><?php else: ?>-<?php endif; ?></td>
                    <td>
                      <?php $map = ['baru'=>'info','interview'=>'pending','diterima'=>'aktif','ditolak'=>'ditolak']; ?>
                      <span class="badge badge-status-<?= $map[$p['status']] ?>"><?= ucfirst($p['status']) ?></span>
                    </td>
                    <td class="text-end">
                      <?php if (!in_array($p['status'], ['diterima', 'ditolak'], true)): ?>
                        <div class="btn-group btn-group-sm">
                          <form method="POST" action="<?= url('rekrutmen/update-status-pelamar/' . $p['id']) ?>" class="d-inline">
                            <input type="hidden" name="status" value="interview">
                            <button class="btn btn-outline-brand" title="Jadwalkan Interview"><i class="fa-solid fa-comments"></i></button>
                          </form>
                          <form method="POST" action="<?= url('rekrutmen/update-status-pelamar/' . $p['id']) ?>" class="d-inline">
                            <input type="hidden" name="status" value="diterima">
                            <button class="btn btn-outline-success" onclick="return confirm('Terima pelamar ini? Akun karyawan baru akan otomatis dibuat.')" title="Terima"><i class="fa-solid fa-check"></i></button>
                          </form>
                          <form method="POST" action="<?= url('rekrutmen/update-status-pelamar/' . $p['id']) ?>" class="d-inline">
                            <input type="hidden" name="status" value="ditolak">
                            <button class="btn btn-outline-danger" title="Tolak"><i class="fa-solid fa-xmark"></i></button>
                          </form>
                        </div>
                      <?php else: ?>
                        <span class="text-muted small">Selesai</span>
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
</div>

<!-- Modal Buka Lowongan -->
<div class="modal fade" id="modalLowongan" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="<?= url('rekrutmen/store-lowongan') ?>">
        <div class="modal-header">
          <h5 class="modal-title">Buka Lowongan Baru</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Judul Lowongan <span class="text-danger">*</span></label>
            <input type="text" name="judul" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Departemen</label>
            <select name="departemen_id" class="form-select">
              <option value="">- Pilih -</option>
              <?php foreach ($daftarDepartemen as $d): ?>
                <option value="<?= $d['id'] ?>"><?= e($d['nama']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-1">
            <label class="form-label">Deskripsi</label>
            <textarea name="deskripsi" class="form-control" rows="3"></textarea>
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

<!-- Modal Tambah Pelamar -->
<div class="modal fade" id="modalPelamar" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="<?= url('rekrutmen/tambah-pelamar') ?>" enctype="multipart/form-data">
        <div class="modal-header">
          <h5 class="modal-title">Tambah Pelamar</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Lowongan <span class="text-danger">*</span></label>
            <select name="lowongan_id" class="form-select" required>
              <option value="">- Pilih lowongan -</option>
              <?php foreach ($daftarLowongan as $l): if ($l['status'] !== 'buka') continue; ?>
                <option value="<?= $l['id'] ?>"><?= e($l['judul']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Nama Pelamar <span class="text-danger">*</span></label>
            <input type="text" name="nama" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Email <span class="text-danger">*</span></label>
            <input type="email" name="email" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">No. HP</label>
            <input type="text" name="no_hp" class="form-control">
          </div>
          <div class="mb-1">
            <label class="form-label">Upload CV</label>
            <input type="file" name="cv" class="form-control" accept=".pdf,.doc,.docx">
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
