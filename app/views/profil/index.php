<div class="row g-3">
  <div class="col-lg-4">
    <div class="card mb-3">
      <div class="card-body text-center">
        <form method="POST" action="<?= url('profil/update') ?>" enctype="multipart/form-data" id="formFoto">
          <input type="hidden" name="name" value="<?= e($user['name']) ?>">
          <input type="hidden" name="no_hp" value="<?= e($user['no_hp']) ?>">
          <input type="hidden" name="alamat" value="<?= e($user['alamat']) ?>">
          <div class="position-relative d-inline-block mb-3">
            <img src="<?= $user['foto'] ? UPLOAD_URL . 'foto_profil/' . e($user['foto']) : 'https://ui-avatars.com/api/?background=2f6fed&color=fff&size=128&name=' . urlencode($user['name']) ?>" class="avatar-md">
            <label for="inputFoto" class="btn btn-brand rounded-circle position-absolute" style="width:34px;height:34px;padding:0;bottom:0;right:0;">
              <i class="fa-solid fa-camera"></i>
            </label>
            <input type="file" name="foto" id="inputFoto" class="d-none" accept="image/*" onchange="document.getElementById('formFoto').submit()">
          </div>
        </form>
        <h5 class="fw-bold mb-1"><?= e($user['name']) ?></h5>
        <span class="badge badge-status-info mb-2"><?= e(role_label($user['role'])) ?></span>
        <?php if ($karyawan): ?>
          <p class="small text-muted mb-0"><?= e($karyawan['jabatan']) ?></p>
          <p class="small text-muted mb-2">NIP: <?= e($karyawan['nip']) ?></p>
          <p class="small text-muted mb-0">
            <i class="fa-regular fa-calendar me-1"></i>Bergabung sejak <?= format_tanggal($karyawan['tgl_bergabung']) ?>
          </p>
        <?php endif; ?>
      </div>
    </div>

    <div class="card">
      <div class="card-header d-flex align-items-center gap-2">
        <i class="fa-solid fa-shield-halved text-primary"></i> Keamanan Akun
      </div>
      <div class="card-body">
        <p class="text-muted small">Jaga keamanan akun Anda dengan password yang kuat dan jangan bagikan ke siapa pun.</p>
        <button class="btn btn-outline-brand btn-sm w-100" data-bs-toggle="modal" data-bs-target="#modalUbahPassword">
          <i class="fa-solid fa-key me-1"></i> Ubah Password
        </button>
      </div>
    </div>
  </div>

  <div class="col-lg-8">
    <div class="card mb-3">
      <div class="card-header">Edit Profil</div>
      <div class="card-body">
        <form method="POST" action="<?= url('profil/update') ?>">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Nama Lengkap</label>
              <input type="text" name="name" class="form-control" value="<?= e($user['name']) ?>" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Email</label>
              <input type="email" class="form-control" value="<?= e($user['email']) ?>" disabled>
            </div>
            <div class="col-md-6">
              <label class="form-label">No. HP</label>
              <input type="text" name="no_hp" class="form-control" value="<?= e($user['no_hp']) ?>" placeholder="08xx-xxxx-xxxx">
            </div>
            <div class="col-md-6">
              <label class="form-label">Tempat, Tanggal Lahir</label>
              <input type="text" class="form-control" value="<?= $karyawan && $karyawan['tempat_lahir'] ? e($karyawan['tempat_lahir']) . ', ' . format_tanggal($karyawan['tanggal_lahir']) : '-' ?>" disabled>
            </div>
            <div class="col-12">
              <label class="form-label">Alamat</label>
              <textarea name="alamat" class="form-control" rows="2" placeholder="Alamat lengkap"><?= e($user['alamat']) ?></textarea>
            </div>
          </div>
          <button type="submit" class="btn btn-brand mt-3">
            <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Perubahan
          </button>
        </form>
      </div>
    </div>

    <div class="card">
      <div class="card-header d-flex align-items-center gap-2">
        <i class="fa-solid fa-clock-rotate-left text-primary"></i> Aktivitas Terakhir
      </div>
      <div class="card-body p-0">
        <?php if (empty($aktivitas)): ?>
          <p class="text-muted small mb-0 p-3">Belum ada aktivitas tercatat.</p>
        <?php else: ?>
          <ul class="list-unstyled mb-0">
            <?php foreach ($aktivitas as $a): ?>
              <li class="d-flex justify-content-between align-items-center border-bottom px-3 py-2 small">
                <div>
                  <i class="fa-solid fa-circle text-success" style="font-size:.5rem;"></i>
                  <span class="ms-2"><?= e($a['aktivitas']) ?></span>
                  <div class="text-muted ms-3" style="font-size:.72rem;"><?= e($a['ip_address'] ?? '-') ?></div>
                </div>
                <span class="text-muted" style="font-size:.72rem;"><?= format_tanggal($a['created_at'], 'd M Y H:i') ?></span>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- Modal Ubah Password -->
<div class="modal fade" id="modalUbahPassword" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="<?= url('profil/ganti-password') ?>">
        <div class="modal-header">
          <h5 class="modal-title">Ubah Password</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Password Lama</label>
            <input type="password" name="password_lama" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Password Baru</label>
            <input type="password" name="password_baru" class="form-control" required minlength="6">
          </div>
          <div class="mb-3">
            <label class="form-label">Konfirmasi Password Baru</label>
            <input type="password" name="konfirmasi_password" class="form-control" required minlength="6">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-brand">Simpan Password</button>
        </div>
      </form>
    </div>
  </div>
</div>
