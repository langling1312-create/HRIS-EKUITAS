<?php if (AuthHelper::isHrOrAdmin()): ?>

  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
      <span>Persetujuan Cuti</span>
      <select class="form-select form-select-sm" style="width:170px;" onchange="filterStatus(this.value)">
        <option value="">Semua Status</option>
        <option value="pending">Pending</option>
        <option value="disetujui">Disetujui</option>
        <option value="ditolak">Ditolak</option>
      </select>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="tabelCuti">
          <thead>
            <tr>
              <th>Karyawan</th><th>Jenis</th><th>Mulai</th><th>Selesai</th><th>Alasan</th><th>Lampiran</th><th>Surat</th><th>Progres</th><th>Status</th><th class="text-end">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($daftarCuti)): ?>
              <tr><td colspan="10" class="text-center text-muted py-4">Belum ada pengajuan cuti.</td></tr>
            <?php else: ?>
              <?php foreach ($daftarCuti as $c): ?>
                <tr data-status="<?= e($c['status']) ?>">
                  <td class="d-flex align-items-center gap-2">
                    <img src="<?= $c['foto'] ? UPLOAD_URL . 'foto_profil/' . e($c['foto']) : 'https://ui-avatars.com/api/?background=2f6fed&color=fff&name=' . urlencode($c['name']) ?>" class="avatar-sm">
                    <?= e($c['name']) ?>
                  </td>
                  <td class="text-capitalize"><?= e($c['jenis_cuti']) ?></td>
                  <td><?= format_tanggal($c['tanggal_mulai']) ?></td>
                  <td><?= format_tanggal($c['tanggal_selesai']) ?></td>
                  <td><?= e($c['alasan']) ?></td>
                  <td>
                    <?php if (!empty($c['surat_sakit'])): ?>
                      <?php $extLampiran = strtolower(pathinfo($c['surat_sakit'], PATHINFO_EXTENSION)); ?>
                      <button type="button" class="btn btn-sm btn-outline-secondary"
                              data-bs-toggle="modal" data-bs-target="#modalLampiranCuti"
                              data-file="<?= UPLOAD_URL . 'surat_sakit/' . e($c['surat_sakit']) ?>"
                              data-ext="<?= e($extLampiran) ?>"
                              data-nama="<?= e($c['name']) ?>">
                        <i class="fa-solid fa-paperclip me-1"></i>Lihat
                      </button>
                    <?php else: ?>
                      <span class="text-muted small">-</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <a href="<?= url('cuti/cetak/' . $c['id']) ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary" title="Lihat/Cetak Surat Permohonan Cuti">
                      <i class="fa-solid fa-file-lines me-1"></i>Surat
                    </a>
                  </td>
                  <td class="small"><?= progress_tahap_cuti($c) ?></td>
                  <td><?= badge_tahap_cuti($c) ?></td>
                  <td class="text-end">
                    <?php if ($c['status'] === 'pending' && $c['tahap_sekarang'] === 'hrd'): ?>
                      <form method="POST" action="<?= url('cuti/setujui/' . $c['id']) ?>" class="d-inline">
                        <button class="btn btn-sm btn-success" onclick="return confirm('Setujui pengajuan cuti ini?')" title="Setujui">
                          <i class="fa-solid fa-check"></i>
                        </button>
                      </form>
                      <form method="POST" action="<?= url('cuti/tolak/' . $c['id']) ?>" class="d-inline">
                        <button class="btn btn-sm btn-danger" onclick="return confirm('Tolak pengajuan cuti ini?')" title="Tolak">
                          <i class="fa-solid fa-xmark"></i>
                        </button>
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

  <!-- Modal Preview Lampiran (foto/PDF surat sakit) -->
  <div class="modal fade" id="modalLampiranCuti" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Lampiran <span id="lampiranNamaKaryawan"></span></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body text-center" id="lampiranBody" style="min-height:200px;">
          <!-- Diisi otomatis lewat JS: <img> untuk jpg/jpeg/png, <iframe> untuk pdf -->
        </div>
        <div class="modal-footer">
          <a href="#" target="_blank" rel="noopener" id="lampiranBukaTabBaru" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-up-right-from-square me-1"></i>Buka di tab baru
          </a>
        </div>
      </div>
    </div>
  </div>

  <script>
  function filterStatus(status) {
    document.querySelectorAll('#tabelCuti tbody tr').forEach(function (row) {
      if (!status || row.dataset.status === status) {
        row.style.display = '';
      } else {
        row.style.display = 'none';
      }
    });
  }

  document.getElementById('modalLampiranCuti').addEventListener('show.bs.modal', function (event) {
    var btn = event.relatedTarget;
    var fileUrl = btn.getAttribute('data-file');
    var ext = btn.getAttribute('data-ext');
    var nama = btn.getAttribute('data-nama') || '';
    var body = document.getElementById('lampiranBody');
    var imgExt = ['jpg', 'jpeg', 'png'];

    document.getElementById('lampiranNamaKaryawan').textContent = nama ? '- ' + nama : '';
    document.getElementById('lampiranBukaTabBaru').setAttribute('href', fileUrl);

    if (imgExt.indexOf(ext) !== -1) {
      body.innerHTML = '<img src="' + fileUrl + '" alt="Lampiran surat sakit" style="max-width:100%; max-height:70vh; border-radius:8px;" onerror="this.replaceWith(Object.assign(document.createElement(\'div\'), {className:\'text-danger small py-4\', textContent:\'File lampiran tidak bisa ditampilkan (kemungkinan rusak/kosong atau sudah terhapus). Coba minta karyawan mengunggah ulang.\'}))">';
    } else {
      body.innerHTML = '<iframe src="' + fileUrl + '" style="width:100%; height:70vh; border:0;"></iframe>';
    }
  });
  </script>

<?php else: ?>

  <?php if ((AuthHelper::isKepalaUnit() || AuthHelper::isPimpinanUnit()) && !empty($antreanApproval)): ?>
  <div class="card mb-3">
    <div class="card-header">
      Menunggu Persetujuan Anda
      <span class="badge bg-warning text-dark ms-1"><?= count($antreanApproval) ?></span>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead>
            <tr><th>Karyawan</th><th>Jenis</th><th>Mulai</th><th>Selesai</th><th>Alasan</th><th>Lampiran</th><th>Surat</th><th class="text-end">Aksi</th></tr>
          </thead>
          <tbody>
            <?php foreach ($antreanApproval as $c): ?>
              <tr>
                <td class="d-flex align-items-center gap-2">
                  <img src="<?= $c['foto'] ? UPLOAD_URL . 'foto_profil/' . e($c['foto']) : 'https://ui-avatars.com/api/?background=2f6fed&color=fff&name=' . urlencode($c['name']) ?>" class="avatar-sm">
                  <?= e($c['name']) ?>
                </td>
                <td class="text-capitalize"><?= e($c['jenis_cuti']) ?></td>
                <td><?= format_tanggal($c['tanggal_mulai']) ?></td>
                <td><?= format_tanggal($c['tanggal_selesai']) ?></td>
                <td><?= e($c['alasan']) ?></td>
                <td>
                  <?php if (!empty($c['surat_sakit'])): ?>
                    <?php $extLampiran = strtolower(pathinfo($c['surat_sakit'], PATHINFO_EXTENSION)); ?>
                    <button type="button" class="btn btn-sm btn-outline-secondary"
                            data-bs-toggle="modal" data-bs-target="#modalLampiranCuti"
                            data-file="<?= UPLOAD_URL . 'surat_sakit/' . e($c['surat_sakit']) ?>"
                            data-ext="<?= e($extLampiran) ?>"
                            data-nama="<?= e($c['name']) ?>">
                      <i class="fa-solid fa-paperclip me-1"></i>Lihat
                    </button>
                  <?php else: ?>
                    <span class="text-muted small">-</span>
                  <?php endif; ?>
                </td>
                <td>
                  <a href="<?= url('cuti/cetak/' . $c['id']) ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary" title="Lihat/Cetak Surat Permohonan Cuti">
                    <i class="fa-solid fa-file-lines me-1"></i>Surat
                  </a>
                </td>
                <td class="text-end">
                  <form method="POST" action="<?= url('cuti/setujui-unit/' . $c['id']) ?>" class="d-inline">
                    <button class="btn btn-sm btn-success" onclick="return confirm('Setujui pengajuan cuti ini?')" title="Setujui">
                      <i class="fa-solid fa-check"></i>
                    </button>
                  </form>
                  <form method="POST" action="<?= url('cuti/tolak-unit/' . $c['id']) ?>" class="d-inline">
                    <button class="btn btn-sm btn-danger" onclick="return confirm('Tolak pengajuan cuti ini?')" title="Tolak">
                      <i class="fa-solid fa-xmark"></i>
                    </button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <div class="row g-3 mb-3">
    <div class="col-md-6">
      <div class="card stat-card">
        <div class="card-body d-flex align-items-center gap-3">
          <div class="icon-box bg-icon-blue"><i class="fa-solid fa-calendar-days"></i></div>
          <div>
            <div class="stat-label">Sisa Cuti Tahunan</div>
            <div class="stat-value"><?= (int) $sisaCuti['sisa_hari'] ?> Hari</div>
            <div class="stat-sub">Periode 1 Januari - 31 Desember <?= date('Y') ?></div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-6 d-flex align-items-center justify-content-md-end">
      <button class="btn btn-brand" data-bs-toggle="modal" data-bs-target="#modalAjukanCuti">
        <i class="fa-solid fa-plus me-1"></i> Ajukan Cuti
      </button>
    </div>
  </div>

  <div class="card">
    <div class="card-header">Riwayat Pengajuan Cuti</div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead>
            <tr><th>Jenis</th><th>Mulai</th><th>Selesai</th><th>Alasan</th><th>Lampiran</th><th>Surat</th><th>Progres</th><th>Status</th><th>Diajukan</th></tr>
          </thead>
          <tbody>
            <?php if (empty($daftarCuti)): ?>
              <tr><td colspan="9" class="text-center text-muted py-4">Belum ada pengajuan cuti.</td></tr>
            <?php else: ?>
              <?php foreach ($daftarCuti as $c): ?>
                <tr>
                  <td class="text-capitalize"><?= e($c['jenis_cuti']) ?></td>
                  <td><?= format_tanggal($c['tanggal_mulai']) ?></td>
                  <td><?= format_tanggal($c['tanggal_selesai']) ?></td>
                  <td><?= e($c['alasan']) ?></td>
                  <td>
                    <?php if (!empty($c['surat_sakit'])): ?>
                      <?php $extLampiran = strtolower(pathinfo($c['surat_sakit'], PATHINFO_EXTENSION)); ?>
                      <button type="button" class="btn btn-sm btn-outline-secondary"
                              data-bs-toggle="modal" data-bs-target="#modalLampiranCuti"
                              data-file="<?= UPLOAD_URL . 'surat_sakit/' . e($c['surat_sakit']) ?>"
                              data-ext="<?= e($extLampiran) ?>">
                        <i class="fa-solid fa-paperclip me-1"></i>Lihat
                      </button>
                    <?php else: ?>
                      <span class="text-muted small">-</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <a href="<?= url('cuti/cetak/' . $c['id']) ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary" title="Lihat/Cetak Surat Permohonan Cuti">
                      <i class="fa-solid fa-file-lines me-1"></i>Surat
                    </a>
                  </td>
                  <td class="small"><?= progress_tahap_cuti($c) ?></td>
                  <td><?= badge_tahap_cuti($c) ?></td>
                  <td><?= format_tanggal($c['created_at'], 'd M Y') ?></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Modal Preview Lampiran (foto/PDF surat sakit) -->
  <div class="modal fade" id="modalLampiranCuti" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Lampiran <span id="lampiranNamaKaryawan"></span></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body text-center" id="lampiranBody" style="min-height:200px;">
          <!-- Diisi otomatis lewat JS: <img> untuk jpg/jpeg/png, <iframe> untuk pdf -->
        </div>
        <div class="modal-footer">
          <a href="#" target="_blank" rel="noopener" id="lampiranBukaTabBaru" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-up-right-from-square me-1"></i>Buka di tab baru
          </a>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modalAjukanCuti" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <!-- enctype wajib ada agar file surat sakit bisa terupload -->
        <form method="POST" action="<?= url('cuti/ajukan') ?>" id="formAjukanCuti" enctype="multipart/form-data">
          <div class="modal-header">
            <h5 class="modal-title">Ajukan Cuti Baru</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="alert alert-info small py-2">Sisa cuti Anda saat ini: <strong><?= (int) $sisaCuti['sisa_hari'] ?> hari</strong></div>
            
            <div class="mb-3">
              <label class="form-label">Jenis Cuti <span class="text-danger">*</span></label>
              <select name="jenis_cuti" id="jenisCutiSelect" class="form-select" required>
                <option value="tahunan">Tahunan</option>
                <option value="sakit">Sakit</option>
                <option value="penting">Kepentingan Penting</option>
                <option value="spesial">Cuti Spesial</option>
                <option value="khusus">Cuti Khusus</option>
              </select>
            </div>

            <!-- Input Upload Surat Sakit (Muncul otomatis jika pilih 'sakit') -->
            <div class="mb-3" id="wrapperSuratSakit" style="display: none;">
              <label class="form-label">Upload Surat Sakit (PDF/Gambar) <span class="text-danger">*</span></label>
              <input type="file" name="surat_sakit" id="inputSuratSakit" class="form-control" accept=".pdf, .jpg, .jpeg, .png">
              <div class="form-text text-muted small">Wajib melampirkan surat keterangan dokter.</div>
            </div>

            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">Tanggal Mulai <span class="text-danger">*</span></label>
                <input type="date" name="tanggal_mulai" id="tglMulai" class="form-control" min="<?= date('Y-m-d') ?>" required>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Tanggal Selesai <span class="text-danger">*</span></label>
                <input type="date" name="tanggal_selesai" id="tglSelesai" class="form-control" min="<?= date('Y-m-d') ?>" required>
              </div>
            </div>
            
            <div class="mb-3">
              <label class="form-label">Lama Cuti</label>
              <input type="text" class="form-control" id="lamaCuti" value="0 Hari" disabled>
              <div id="infoAturanCuti" class="form-text text-muted small"></div>
            </div>

            <div class="mb-1">
              <label class="form-label">Alasan <span class="text-danger">*</span></label>
              <textarea name="alasan" id="alasanCuti" class="form-control" rows="3" maxlength="500" required></textarea>
              <div class="text-end small text-muted"><span id="charCount">0</span>/500</div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-brand"><i class="fa-solid fa-paper-plane me-1"></i> Kirim Pengajuan</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
  document.addEventListener('DOMContentLoaded', function () {
    var tglMulai = document.getElementById('tglMulai');
    var tglSelesai = document.getElementById('tglSelesai');
    var lamaCuti = document.getElementById('lamaCuti');
    var alasanCuti = document.getElementById('alasanCuti');
    var charCount = document.getElementById('charCount');
    var jenisCutiSelect = document.getElementById('jenisCutiSelect');
    var wrapperSuratSakit = document.getElementById('wrapperSuratSakit');
    var inputSuratSakit = document.getElementById('inputSuratSakit');
    var infoAturanCuti = document.getElementById('infoAturanCuti');

    function hitungLama() {
      var jenis = jenisCutiSelect.value;

      if (jenis === 'khusus') {
        if (tglMulai.value) {
          tglSelesai.value = tglMulai.value;
          tglSelesai.readOnly = true;
          lamaCuti.value = '1 Hari';
        }
        return;
      } else {
        tglSelesai.readOnly = false;
      }

      if (tglMulai.value && tglSelesai.value) {
        var mulai = new Date(tglMulai.value);
        var selesai = new Date(tglSelesai.value);
        var diff = Math.round((selesai - mulai) / 86400000) + 1;
        lamaCuti.value = diff > 0 ? diff + ' Hari' : '0 Hari';
      } else {
        lamaCuti.value = '0 Hari';
      }
    }

    jenisCutiSelect.addEventListener('change', function () {
      var jenis = this.value;

      if (jenis === 'sakit') {
        wrapperSuratSakit.style.display = 'block';
        inputSuratSakit.required = true;
      } else {
        wrapperSuratSakit.style.display = 'none';
        inputSuratSakit.required = false;
        inputSuratSakit.value = '';
      }

      if (jenis === 'spesial') {
        infoAturanCuti.textContent = 'Maksimal: 90 hari untuk Perempuan (Melahirkan), 2 hari untuk Laki-laki.';
      } else if (jenis === 'khusus') {
        infoAturanCuti.textContent = 'Cuti Khusus ditetapkan selama 1 hari.';
        if (tglMulai.value) {
          tglSelesai.value = tglMulai.value;
        }
      } else {
        infoAturanCuti.textContent = '';
      }
      hitungLama();
    });

    if (tglMulai && tglSelesai) {
      tglMulai.addEventListener('change', hitungLama);
      tglSelesai.addEventListener('change', hitungLama);
    }
    
    if (alasanCuti && charCount) {
      alasanCuti.addEventListener('input', function () {
        charCount.textContent = alasanCuti.value.length;
      });
    }

    var modalLampiran = document.getElementById('modalLampiranCuti');
    if (modalLampiran) {
      modalLampiran.addEventListener('show.bs.modal', function (event) {
        var btn = event.relatedTarget;
        var fileUrl = btn.getAttribute('data-file');
        var ext = btn.getAttribute('data-ext');
        var nama = btn.getAttribute('data-nama') || '';
        var body = document.getElementById('lampiranBody');
        var imgExt = ['jpg', 'jpeg', 'png'];

        document.getElementById('lampiranNamaKaryawan').textContent = nama ? '- ' + nama : '';
        document.getElementById('lampiranBukaTabBaru').setAttribute('href', fileUrl);

        if (imgExt.indexOf(ext) !== -1) {
          body.innerHTML = '<img src="' + fileUrl + '" alt="Lampiran surat sakit" style="max-width:100%; max-height:70vh; border-radius:8px;" onerror="this.replaceWith(Object.assign(document.createElement(\'div\'), {className:\'text-danger small py-4\', textContent:\'File lampiran tidak bisa ditampilkan (kemungkinan rusak/kosong atau sudah terhapus). Coba minta karyawan mengunggah ulang.\'}))">';
        } else {
          body.innerHTML = '<iframe src="' + fileUrl + '" style="width:100%; height:70vh; border:0;"></iframe>';
        }
      });
    }
  });
  </script>

<?php endif; ?>