<?php if (AuthHelper::isHrOrAdmin()): ?>

  <div class="card">
    <div class="card-header">Daftar Pengajuan Kasbon</div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead>
            <tr><th>Karyawan</th><th>Jumlah</th><th>Tenor</th><th>Cicilan/Bulan</th><th>Sisa Cicilan</th><th>Status</th><th class="text-end">Aksi</th></tr>
          </thead>
          <tbody>
            <?php if (empty($daftar)): ?>
              <tr><td colspan="7" class="text-center text-muted py-4">Belum ada pengajuan kasbon.</td></tr>
            <?php else: ?>
              <?php foreach ($daftar as $k): ?>
                <tr>
                  <td class="d-flex align-items-center gap-2">
                    <img src="<?= $k['foto'] ? UPLOAD_URL . 'foto_profil/' . e($k['foto']) : 'https://ui-avatars.com/api/?background=2f6fed&color=fff&name=' . urlencode($k['name']) ?>" class="avatar-sm">
                    <?= e($k['name']) ?>
                  </td>
                  <td class="fw-semibold"><?= format_rupiah($k['jumlah']) ?></td>
                  <td><?= (int) $k['tenor_bulan'] ?> bulan</td>
                  <td><?= format_rupiah($k['cicilan_per_bulan']) ?></td>
                  <td><?= (int) $k['sisa_cicilan'] ?>x</td>
                  <td><span class="badge badge-status-<?= $k['status'] === 'lunas' ? 'aktif' : $k['status'] ?>"><?= ucfirst($k['status']) ?></span></td>
                  <td class="text-end">
                    <?php if ($k['status'] === 'pending'): ?>
                      <form method="POST" action="<?= url('kasbon/setujui/' . $k['id']) ?>" class="d-inline">
                        <button class="btn btn-sm btn-success" onclick="return confirm('Setujui kasbon ini?')"><i class="fa-solid fa-check"></i></button>
                      </form>
                      <form method="POST" action="<?= url('kasbon/tolak/' . $k['id']) ?>" class="d-inline">
                        <button class="btn btn-sm btn-danger" onclick="return confirm('Tolak kasbon ini?')"><i class="fa-solid fa-xmark"></i></button>
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

  <div class="row g-3 mb-3">
    <div class="col-md-6">
      <div class="card stat-card">
        <div class="card-body d-flex align-items-center gap-3">
          <div class="icon-box bg-icon-red"><i class="fa-solid fa-hand-holding-dollar"></i></div>
          <div>
            <div class="stat-label">Sisa Kasbon Aktif</div>
            <div class="stat-value fs-5"><?= format_rupiah($sisaAktif) ?></div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-6 d-flex align-items-center justify-content-md-end">
      <button class="btn btn-brand" data-bs-toggle="modal" data-bs-target="#modalAjukanKasbon">
        <i class="fa-solid fa-plus me-1"></i> Ajukan Kasbon
      </button>
    </div>
  </div>

  <div class="card">
    <div class="card-header">Riwayat Kasbon Saya</div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead><tr><th>Jumlah</th><th>Tenor</th><th>Cicilan/Bulan</th><th>Sisa Cicilan</th><th>Status</th><th>Diajukan</th></tr></thead>
          <tbody>
            <?php if (empty($daftar)): ?>
              <tr><td colspan="6" class="text-center text-muted py-4">Belum ada pengajuan kasbon.</td></tr>
            <?php else: ?>
              <?php foreach ($daftar as $k): ?>
                <tr>
                  <td class="fw-semibold"><?= format_rupiah($k['jumlah']) ?></td>
                  <td><?= (int) $k['tenor_bulan'] ?> bulan</td>
                  <td><?= format_rupiah($k['cicilan_per_bulan']) ?></td>
                  <td><?= (int) $k['sisa_cicilan'] ?>x</td>
                  <td><span class="badge badge-status-<?= $k['status'] === 'lunas' ? 'aktif' : $k['status'] ?>"><?= ucfirst($k['status']) ?></span></td>
                  <td><?= format_tanggal($k['created_at'], 'd M Y') ?></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modalAjukanKasbon" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <form method="POST" action="<?= url('kasbon/ajukan') ?>">
          <div class="modal-header">
            <h5 class="modal-title">Ajukan Kasbon</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Jumlah Kasbon <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text">Rp</span>
                <input type="number" name="jumlah" id="jumlahKasbon" class="form-control" min="1" required>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label">Tenor (bulan) <span class="text-danger">*</span></label>
              <select name="tenor_bulan" id="tenorKasbon" class="form-select" required>
                <option value="1">1 Bulan</option>
                <option value="2">2 Bulan</option>
                <option value="3" selected>3 Bulan</option>
                <option value="6">6 Bulan</option>
                <option value="12">12 Bulan</option>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Estimasi Cicilan / Bulan</label>
              <input type="text" id="estimasiCicilan" class="form-control" value="Rp 0" disabled>
            </div>
            <div class="mb-1">
              <label class="form-label">Alasan</label>
              <textarea name="alasan" class="form-control" rows="3"></textarea>
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

  <script>
  document.addEventListener('DOMContentLoaded', function () {
    var jumlah = document.getElementById('jumlahKasbon');
    var tenor = document.getElementById('tenorKasbon');
    var estimasi = document.getElementById('estimasiCicilan');
    function hitung() {
      var j = parseFloat(jumlah.value) || 0;
      var t = parseInt(tenor.value) || 1;
      var cicilan = Math.round(j / t);
      estimasi.value = 'Rp ' + cicilan.toLocaleString('id-ID');
    }
    if (jumlah && tenor) {
      jumlah.addEventListener('input', hitung);
      tenor.addEventListener('change', hitung);
    }
  });
  </script>

<?php endif; ?>
