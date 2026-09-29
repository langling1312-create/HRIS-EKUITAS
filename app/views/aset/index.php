<?php if (AuthHelper::isHrOrAdmin()): ?>

  <div class="row g-3 mb-3">
    <div class="col-md-4">
      <div class="card stat-card">
        <div class="card-body d-flex align-items-center gap-3">
          <div class="icon-box bg-icon-green"><i class="fa-solid fa-box"></i></div>
          <div><div class="stat-label">Tersedia</div><div class="stat-value"><?= (int) $totalTersedia ?></div></div>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card stat-card">
        <div class="card-body d-flex align-items-center gap-3">
          <div class="icon-box bg-icon-blue"><i class="fa-solid fa-hand-holding"></i></div>
          <div><div class="stat-label">Dipinjam</div><div class="stat-value"><?= (int) $totalDipinjam ?></div></div>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card stat-card">
        <div class="card-body d-flex align-items-center gap-3">
          <div class="icon-box bg-icon-red"><i class="fa-solid fa-triangle-exclamation"></i></div>
          <div><div class="stat-label">Rusak / Perbaikan</div><div class="stat-value"><?= (int) $totalRusak ?></div></div>
        </div>
      </div>
    </div>
  </div>

  <div class="d-flex justify-content-end mb-3">
    <button class="btn btn-brand" data-bs-toggle="modal" data-bs-target="#modalTambahAset">
      <i class="fa-solid fa-plus me-1"></i> Tambah Aset
    </button>
  </div>

  <div class="card">
    <div class="card-header">Daftar Inventaris</div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead>
            <tr><th>Kode</th><th>Nama Aset</th><th>Kategori</th><th>Status</th><th>Dipinjam Oleh</th><th class="text-end">Aksi</th></tr>
          </thead>
          <tbody>
            <?php if (empty($daftar)): ?>
              <tr><td colspan="6" class="text-center text-muted py-4">Belum ada data aset.</td></tr>
            <?php else: ?>
              <?php foreach ($daftar as $a): ?>
                <tr>
                  <td><?= e($a['kode_aset']) ?></td>
                  <td><?= e($a['nama_aset']) ?></td>
                  <td><?= e($a['kategori'] ?? '-') ?></td>
                  <td>
                    <?php
                      $statusMap = ['tersedia' => 'aktif', 'dipinjam' => 'info', 'rusak' => 'ditolak', 'perbaikan' => 'pending'];
                    ?>
                    <span class="badge badge-status-<?= $statusMap[$a['status']] ?? 'info' ?>"><?= ucfirst($a['status']) ?></span>
                  </td>
                  <td><?= e($a['peminjam_nama'] ?? '-') ?></td>
                  <td class="text-end">
                    <?php if ($a['status'] === 'tersedia'): ?>
                      <button class="btn btn-sm btn-outline-brand" data-bs-toggle="modal" data-bs-target="#modalPinjam<?= $a['id'] ?>" title="Pinjamkan">
                        <i class="fa-solid fa-hand-holding"></i>
                      </button>
                      <form method="POST" action="<?= url('aset/tandai-rusak/' . $a['id']) ?>" class="d-inline">
                        <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Tandai aset ini rusak?')" title="Tandai Rusak"><i class="fa-solid fa-screwdriver-wrench"></i></button>
                      </form>
                    <?php elseif ($a['status'] === 'dipinjam'): ?>
                      <form method="POST" action="<?= url('aset/kembalikan/' . $a['id']) ?>" class="d-inline">
                        <button class="btn btn-sm btn-outline-brand" onclick="return confirm('Tandai aset ini sudah dikembalikan?')"><i class="fa-solid fa-rotate-left"></i> Kembalikan</button>
                      </form>
                    <?php else: ?>
                      <form method="POST" action="<?= url('aset/kembalikan/' . $a['id']) ?>" class="d-inline">
                        <button class="btn btn-sm btn-outline-brand" onclick="return confirm('Tandai aset tersedia kembali?')">Tersedia Lagi</button>
                      </form>
                    <?php endif; ?>
                  </td>
                </tr>

                <?php if ($a['status'] === 'tersedia'): ?>
                  <div class="modal fade" id="modalPinjam<?= $a['id'] ?>" tabindex="-1">
                    <div class="modal-dialog">
                      <div class="modal-content">
                        <form method="POST" action="<?= url('aset/pinjamkan/' . $a['id']) ?>">
                          <div class="modal-header">
                            <h5 class="modal-title">Pinjamkan Aset: <?= e($a['nama_aset']) ?></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                          </div>
                          <div class="modal-body">
                            <label class="form-label">Pilih Karyawan</label>
                            <select name="user_id" class="form-select" required>
                              <option value="">- Pilih karyawan -</option>
                              <?php foreach ($daftarKaryawan as $k): ?>
                                <option value="<?= $k['user_id'] ?>"><?= e($k['name']) ?> (<?= e($k['nip']) ?>)</option>
                              <?php endforeach; ?>
                            </select>
                          </div>
                          <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-brand">Pinjamkan</button>
                          </div>
                        </form>
                      </div>
                    </div>
                  </div>
                <?php endif; ?>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modalTambahAset" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <form method="POST" action="<?= url('aset/store') ?>">
          <div class="modal-header">
            <h5 class="modal-title">Tambah Aset</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Kode Aset <span class="text-danger">*</span></label>
              <input type="text" name="kode_aset" class="form-control" placeholder="Contoh: AST-001" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Nama Aset <span class="text-danger">*</span></label>
              <input type="text" name="nama_aset" class="form-control" placeholder="Contoh: Laptop Dell Latitude" required>
            </div>
            <div class="mb-1">
              <label class="form-label">Kategori</label>
              <input type="text" name="kategori" class="form-control" placeholder="Contoh: Elektronik, Kendaraan">
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

  <div class="card">
    <div class="card-header">Aset yang Sedang Saya Pinjam</div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead><tr><th>Kode</th><th>Nama Aset</th><th>Kategori</th><th>Tanggal Pinjam</th><th>Status</th></tr></thead>
          <tbody>
            <?php if (empty($daftar)): ?>
              <tr><td colspan="5" class="text-center text-muted py-4">Anda tidak sedang meminjam aset apapun.</td></tr>
            <?php else: ?>
              <?php foreach ($daftar as $a): ?>
                <tr>
                  <td><?= e($a['kode_aset']) ?></td>
                  <td><?= e($a['nama_aset']) ?></td>
                  <td><?= e($a['kategori'] ?? '-') ?></td>
                  <td><?= format_tanggal($a['tanggal_pinjam']) ?></td>
                  <td>
                    <?php $statusMap = ['tersedia' => 'aktif', 'dipinjam' => 'info', 'rusak' => 'ditolak', 'perbaikan' => 'pending']; ?>
                    <span class="badge badge-status-<?= $statusMap[$a['status']] ?? 'info' ?>"><?= ucfirst($a['status']) ?></span>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

<?php endif; ?>
