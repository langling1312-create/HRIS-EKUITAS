<div class="row g-3 mb-3">
  <div class="col-xl-3 col-md-6">
    <div class="card stat-card">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="icon-box bg-icon-blue"><i class="fa-solid fa-users"></i></div>
        <div>
          <div class="stat-label">Total Karyawan</div>
          <div class="stat-value"><?= (int) $totalKaryawan ?></div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-xl-3 col-md-6">
    <div class="card stat-card">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="icon-box bg-icon-green"><i class="fa-solid fa-user-check"></i></div>
        <div>
          <div class="stat-label">Karyawan Aktif</div>
          <div class="stat-value"><?= (int) $karyawanAktif ?></div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-xl-3 col-md-6">
    <div class="card stat-card">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="icon-box bg-icon-yellow"><i class="fa-solid fa-calendar-days"></i></div>
        <div>
          <div class="stat-label">Pengajuan Cuti Pending</div>
          <div class="stat-value"><?= (int) $karyawanCuti ?></div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-xl-3 col-md-6">
    <div class="card stat-card">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="icon-box bg-icon-red"><i class="fa-solid fa-user-xmark"></i></div>
        <div>
          <div class="stat-label">Karyawan Non-Aktif</div>
          <div class="stat-value"><?= (int) $karyawanNonAktif ?></div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="card mb-3">
  <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-2">
    <form method="GET" action="<?= url('karyawan') ?>" class="d-flex gap-2">
      <div class="sb-search" style="min-width:240px;">
        <i class="fa-solid fa-search"></i>
        <input type="text" name="q" placeholder="Cari nama / NIP karyawan..." value="<?= e($keyword) ?>">
      </div>
      <button class="btn btn-sm btn-outline-secondary" type="submit">Cari</button>
    </form>
    <button class="btn btn-brand" data-bs-toggle="modal" data-bs-target="#modalTambahKaryawan">
      <i class="fa-solid fa-plus me-1"></i> Tambah Karyawan
    </button>
  </div>
</div>

<div class="card">
  <div class="card-header">Daftar Karyawan</div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead>
          <tr>
            <th>NIP</th><th>Nama</th><th>Jabatan</th><th>Departemen</th><th>Role</th><th>Gaji Pokok</th><th>Status</th><th class="text-end">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($daftarKaryawan)): ?>
            <tr><td colspan="8" class="text-center text-muted py-4">Belum ada data karyawan.</td></tr>
          <?php else: ?>
            <?php foreach ($daftarKaryawan as $k): ?>
              <tr>
                <td><?= e($k['nip']) ?></td>
                <td class="d-flex align-items-center gap-2">
                  <img src="<?= $k['foto'] ? UPLOAD_URL . 'foto_profil/' . e($k['foto']) : 'https://ui-avatars.com/api/?background=2f6fed&color=fff&name=' . urlencode($k['name']) ?>" class="avatar-sm">
                  <?= e($k['name']) ?>
                </td>
                <td><?= e($k['jabatan']) ?></td>
                <td><?= e($k['departemen_nama'] ?? '-') ?></td>
                <td><span class="badge badge-status-info"><?= e(role_label($k['role'])) ?></span></td>
                <td><?= format_rupiah($k['gaji_pokok']) ?></td>
                <td>
                  <?= $k['status_aktif'] ? '<span class="badge badge-status-aktif">Aktif</span>' : '<span class="badge badge-status-nonaktif">Non-aktif</span>' ?>
                </td>
                <td class="text-end">
                  <button class="btn btn-sm btn-outline-brand" data-bs-toggle="modal" data-bs-target="#modalEdit<?= $k['id'] ?>" title="Edit">
                    <i class="fa-solid fa-pen"></i>
                  </button>
                  <form method="POST" action="<?= url('karyawan/delete/' . $k['id']) ?>" class="d-inline">
                    <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Yakin ingin menghapus karyawan ini?')" title="Hapus">
                      <i class="fa-solid fa-trash"></i>
                    </button>
                  </form>
                </td>
              </tr>

              <!-- Modal Edit -->
              <div class="modal fade" id="modalEdit<?= $k['id'] ?>" tabindex="-1">
                <div class="modal-dialog">
                  <div class="modal-content">
                    <form method="POST" action="<?= url('karyawan/update/' . $k['id']) ?>">
                      <div class="modal-header">
                        <h5 class="modal-title">Edit Karyawan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                      </div>
                      <div class="modal-body">
                        <div class="mb-3">
                          <label class="form-label">Nama Lengkap</label>
                          <input type="text" name="name" class="form-control" value="<?= e($k['name']) ?>" required>
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Email</label>
                          <input type="email" name="email" class="form-control" value="<?= e($k['email']) ?>" required>
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Jabatan</label>
                          <input type="text" name="jabatan" class="form-control" value="<?= e($k['jabatan']) ?>" required>
                        </div>
                        <div class="row">
                          <div class="col-md-6 mb-3">
                            <label class="form-label">Departemen</label>
                            <select name="departemen_id" class="form-select">
                              <option value="">- Pilih -</option>
                              <?php foreach ($daftarDepartemen as $d): ?>
                                <option value="<?= $d['id'] ?>" <?= $d['id'] == $k['departemen_id'] ? 'selected' : '' ?>><?= e($d['nama']) ?></option>
                              <?php endforeach; ?>
                            </select>
                          </div>
                          <div class="col-md-6 mb-3">
                            <label class="form-label">Jenis Kelamin</label>
                            <select name="jenis_kelamin" class="form-select">
                              <option value="L" <?= $k['jenis_kelamin'] == 'L' ? 'selected' : '' ?>>Laki-laki</option>
                              <option value="P" <?= $k['jenis_kelamin'] == 'P' ? 'selected' : '' ?>>Perempuan</option>
                            </select>
                          </div>
                        </div>
                        
                        <!-- TAMBAHAN: Status Kepegawaian di Modal Edit -->
                        <div class="mb-3">
                          <label class="form-label">Status Kepegawaian <span class="text-danger">*</span></label>
                          <select name="status_kepegawaian" class="form-select" required>
                            <option value="Tetap" <?= (isset($k['status_kepegawaian']) && $k['status_kepegawaian'] == 'Tetap') ? 'selected' : '' ?>>Karyawan Tetap</option>
                            <option value="Kontrak" <?= (isset($k['status_kepegawaian']) && $k['status_kepegawaian'] == 'Kontrak') ? 'selected' : '' ?>>Karyawan Kontrak</option>
                          </select>
                        </div>

                        <div class="mb-3">
                          <label class="form-label">Role Akun</label>
                          <select name="role" class="form-select">
                            <option value="karyawan" <?= $k['role'] == 'karyawan' ? 'selected' : '' ?>>Karyawan</option>
                            <option value="kepala_unit" <?= $k['role'] == 'kepala_unit' ? 'selected' : '' ?>>Kepala Unit</option>
                            <option value="pimpinan_unit" <?= $k['role'] == 'pimpinan_unit' ? 'selected' : '' ?>>Pimpinan Unit</option>
                            <?php if (AuthHelper::isAdmin()): ?>
                              <option value="hr" <?= $k['role'] == 'hr' ? 'selected' : '' ?>>HR</option>
                              <option value="admin" <?= $k['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                            <?php endif; ?>
                          </select>
                          <div class="form-text">Pilih "Kepala Unit"/"Pimpinan Unit" jika karyawan ini akan ditunjuk sebagai pejabat approval cuti di menu Unit Kerja.</div>
                        </div>
                        <div class="row">
                          <div class="col-md-6 mb-3">
                            <label class="form-label">Gaji Pokok</label>
                            <div class="input-group">
                              <span class="input-group-text">Rp</span>
                              <input type="number" name="gaji_pokok" class="form-control" value="<?= (int) $k['gaji_pokok'] ?>" required>
                            </div>
                          </div>
                          <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <select name="status_aktif" class="form-select">
                              <option value="1" <?= $k['status_aktif'] ? 'selected' : '' ?>>Aktif</option>
                              <option value="0" <?= !$k['status_aktif'] ? 'selected' : '' ?>>Non-aktif</option>
                            </select>
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
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal Tambah -->
<div class="modal fade" id="modalTambahKaryawan" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="<?= url('karyawan/store') ?>">
        <div class="modal-header">
          <h5 class="modal-title">Tambah Karyawan</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">NIP <span class="text-danger">*</span></label>
              <input type="text" name="nip" class="form-control" placeholder="Masukkan NIP" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
              <input type="text" name="name" class="form-control" placeholder="Masukkan nama lengkap" required>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Email <span class="text-danger">*</span></label>
            <input type="email" name="email" class="form-control" placeholder="Masukkan email" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Password <span class="text-danger">*</span></label>
            <input type="text" name="password" class="form-control" value="123456" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Jabatan <span class="text-danger">*</span></label>
            <input type="text" name="jabatan" class="form-control" placeholder="Pilih jabatan" required>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Departemen <span class="text-danger">*</span></label>
              <select name="departemen_id" class="form-select">
                <option value="">- Pilih departemen -</option>
                <?php foreach ($daftarDepartemen as $d): ?>
                  <option value="<?= $d['id'] ?>"><?= e($d['nama']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Jenis Kelamin</label>
              <select name="jenis_kelamin" class="form-select">
                <option value="L">Laki-laki</option>
                <option value="P">Perempuan</option>
              </select>
            </div>
          </div>

          <!-- TAMBAHAN: Status Kepegawaian di Modal Tambah -->
          <div class="mb-3">
            <label class="form-label">Status Kepegawaian <span class="text-danger">*</span></label>
            <select name="status_kepegawaian" class="form-select" required>
              <option value="">-- Pilih Status Kepegawaian --</option>
              <option value="Tetap">Karyawan Tetap</option>
              <option value="Kontrak">Karyawan Kontrak</option>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label">Role Akun <span class="text-danger">*</span></label>
            <select name="role" class="form-select">
              <option value="karyawan">Karyawan</option>
              <option value="kepala_unit">Kepala Unit</option>
              <option value="pimpinan_unit">Pimpinan Unit</option>
              <?php if (AuthHelper::isAdmin()): ?>
                <option value="hr">HR</option>
                <option value="admin">Admin</option>
              <?php endif; ?>
            </select>
            <div class="form-text">Pilih "Kepala Unit"/"Pimpinan Unit" jika karyawan ini akan ditunjuk sebagai pejabat approval cuti di menu Unit Kerja.</div>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Gaji Pokok <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text">Rp</span>
                <input type="number" name="gaji_pokok" class="form-control" value="0" required>
              </div>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Tanggal Bergabung</label>
              <input type="date" name="tgl_bergabung" class="form-control" value="<?= date('Y-m-d') ?>">
            </div>
          </div>
          <div class="mb-1">
            <label class="form-label">Status <span class="text-danger">*</span></label>
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" checked disabled>
              <label class="form-check-label small text-muted">Aktif (default saat karyawan baru ditambahkan)</label>
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