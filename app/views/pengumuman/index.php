<?php if (AuthHelper::isHrOrAdmin()): ?>
  <div class="d-flex justify-content-end gap-2 mb-3">
    <button class="btn btn-outline-brand" data-bs-toggle="modal" data-bs-target="#modalBuatPolling">
      <i class="fa-solid fa-square-poll-vertical me-1"></i> Buat Polling
    </button>
    <button class="btn btn-brand" data-bs-toggle="modal" data-bs-target="#modalBuatPengumuman">
      <i class="fa-solid fa-bullhorn me-1"></i> Buat Pengumuman
    </button>
  </div>
<?php endif; ?>

<div class="row g-3">
  <div class="col-lg-7">
    <div class="card">
      <div class="card-header">Papan Pengumuman</div>
      <div class="card-body p-0">
        <?php if (empty($daftarPengumuman)): ?>
          <p class="text-muted small p-3 mb-0">Belum ada pengumuman.</p>
        <?php else: ?>
          <ul class="list-unstyled mb-0">
            <?php foreach ($daftarPengumuman as $p): ?>
              <li class="border-bottom px-3 py-3">
                <div class="d-flex justify-content-between align-items-start">
                  <h6 class="fw-bold mb-1"><i class="fa-solid fa-bullhorn text-primary me-2"></i><?= e($p['judul']) ?></h6>
                  <span class="text-muted small"><?= format_tanggal($p['created_at'], 'd M Y') ?></span>
                </div>
                <p class="mb-1 small"><?= nl2br(e($p['isi'])) ?></p>
                <p class="text-muted mb-0" style="font-size:.72rem;">Oleh <?= e($p['pembuat'] ?? 'Sistem') ?></p>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="col-lg-5">
    <div class="card">
      <div class="card-header">Polling</div>
      <div class="card-body">
        <?php if (empty($daftarPolling)): ?>
          <p class="text-muted small mb-0">Belum ada polling.</p>
        <?php else: ?>
          <?php foreach ($daftarPolling as $poll): ?>
            <div class="border rounded p-3 mb-3">
              <div class="d-flex justify-content-between align-items-start mb-2">
                <h6 class="fw-semibold mb-0"><?= e($poll['pertanyaan']) ?></h6>
                <span class="badge badge-status-<?= $poll['status'] === 'aktif' ? 'aktif' : 'nonaktif' ?>"><?= ucfirst($poll['status']) ?></span>
              </div>

              <?php if ($poll['sudah_vote'] || $poll['status'] !== 'aktif'): ?>
                <?php foreach ($poll['opsi'] as $opsi): ?>
                  <?php $persen = $poll['total_vote'] > 0 ? round($opsi['jumlah_vote'] / $poll['total_vote'] * 100) : 0; ?>
                  <div class="mb-2">
                    <div class="d-flex justify-content-between small mb-1">
                      <span><?= e($opsi['opsi_text']) ?></span>
                      <span class="text-muted"><?= $persen ?>% (<?= (int) $opsi['jumlah_vote'] ?>)</span>
                    </div>
                    <div class="progress" style="height:8px;">
                      <div class="progress-bar" style="width:<?= $persen ?>%; background:var(--blue);"></div>
                    </div>
                  </div>
                <?php endforeach; ?>
                <p class="text-muted small mb-0 mt-2"><?= (int) $poll['total_vote'] ?> suara total</p>
              <?php else: ?>
                <form method="POST" action="<?= url('pengumuman/vote/' . $poll['id']) ?>">
                  <?php foreach ($poll['opsi'] as $opsi): ?>
                    <div class="form-check mb-1">
                      <input class="form-check-input" type="radio" name="opsi_id" value="<?= $opsi['id'] ?>" id="opsi<?= $opsi['id'] ?>" required>
                      <label class="form-check-label small" for="opsi<?= $opsi['id'] ?>"><?= e($opsi['opsi_text']) ?></label>
                    </div>
                  <?php endforeach; ?>
                  <button type="submit" class="btn btn-sm btn-brand mt-2">Kirim Suara</button>
                </form>
              <?php endif; ?>

              <?php if (AuthHelper::isHrOrAdmin() && $poll['status'] === 'aktif'): ?>
                <form method="POST" action="<?= url('pengumuman/tutup-polling/' . $poll['id']) ?>" class="mt-2">
                  <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Tutup polling ini?')">Tutup Polling</button>
                </form>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php if (AuthHelper::isHrOrAdmin()): ?>
  <div class="modal fade" id="modalBuatPengumuman" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <form method="POST" action="<?= url('pengumuman/store-pengumuman') ?>">
          <div class="modal-header">
            <h5 class="modal-title">Buat Pengumuman</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Judul <span class="text-danger">*</span></label>
              <input type="text" name="judul" class="form-control" required>
            </div>
            <div class="mb-1">
              <label class="form-label">Isi Pengumuman <span class="text-danger">*</span></label>
              <textarea name="isi" class="form-control" rows="4" required></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-brand">Publikasikan</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modalBuatPolling" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <form method="POST" action="<?= url('pengumuman/store-polling') ?>" id="formPolling">
          <div class="modal-header">
            <h5 class="modal-title">Buat Polling</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Pertanyaan <span class="text-danger">*</span></label>
              <input type="text" name="pertanyaan" class="form-control" required>
            </div>
            <label class="form-label">Opsi Jawaban (minimal 2)</label>
            <div id="opsiWrapper">
              <input type="text" name="opsi[]" class="form-control mb-2" placeholder="Opsi 1" required>
              <input type="text" name="opsi[]" class="form-control mb-2" placeholder="Opsi 2" required>
            </div>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="tambahOpsi()">+ Tambah Opsi</button>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-brand">Buat Polling</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
  function tambahOpsi() {
    var wrapper = document.getElementById('opsiWrapper');
    var input = document.createElement('input');
    input.type = 'text';
    input.name = 'opsi[]';
    input.className = 'form-control mb-2';
    input.placeholder = 'Opsi tambahan';
    wrapper.appendChild(input);
  }
  </script>
<?php endif; ?>
