<div class="card mb-3">
  <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
      <div class="fw-semibold">Unit Kerja &amp; Alur Persetujuan Cuti</div>
      <div class="text-muted small">
        Tentukan siapa Kepala Unit dan Pimpinan Unit tiap unit kerja. Alur cuti karyawan akan berjalan:
        <strong>Kepala Unit &rarr; Pimpinan Unit &rarr; HRD</strong>.
        Kepala Unit &amp; Pimpinan Unit adalah <strong>akun login tersendiri</strong> (role khusus) &mdash; HRIS Terlengkap untuk Perusahaan Modern
        buat akunnya dulu lewat menu <strong>Karyawan</strong> (pilih Role = Kepala Unit / Pimpinan Unit),
        lalu tunjuk di sini sebagai penanggung jawab unit. Kedua posisi tetap boleh dikosongkan;
        jika kosong, tahap tersebut otomatis dilewati.
      </div>
    </div>
    <button class="btn btn-brand" data-bs-toggle="modal" data-bs-target="#modalTambahUnit">
      <i class="fa-solid fa-plus me-1"></i> Tambah Unit
    </button>
  </div>
</div>

<div class="card">
  <div class="card-header">Daftar Unit Kerja</div>
  <div class="card-body p-0"> HRIS Terlengkap untuk EKUITAS
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead>
          <tr>
            <th>Unit / Departemen</th>
            <th>Kepala Unit</th>
            <th>Pimpinan Unit</th>
            <th class="text-end">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($daftarDepartemen)): ?>
            <tr><td colspan="4" class="text-center text-muted py-4">Belum ada unit/departemen.</td></tr>
          <?php else: ?>
            <?php foreach ($daftarDepartemen as $d): ?>
              <tr>
                <td class="fw-semibold"><?= e($d['nama']) ?></td>
                <td><?= $d['kepala_unit_nama'] ? e($d['kepala_unit_nama']) : '<span class="text-muted">- (dilewati)</span>' ?></td>
                <td><?= $d['pimpinan_unit_nama'] ? e($d['pimpinan_unit_nama']) : '<span class="text-muted">- (dilewati)</span>' ?></td>
                <td class="text-end">
                  <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalPejabat<?= $d['id'] ?>">
                    <i class="fa-solid fa-user-gear"></i> Atur Pejabat
                  </button>
                </td>
              </tr>

              <div class="modal fade" id="modalPejabat<?= $d['id'] ?>" tabindex="-1">
                <div class="modal-dialog">
                  <div class="modal-content">
                    <form method="POST" action="<?= url('departemen/pejabat/' . $d['id']) ?>">
                      <div class="modal-header">
                        <h5 class="modal-title">Atur Pejabat Approval - <?= e($d['nama']) ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                      </div>
                      <div class="modal-body">
                        <div class="mb-3">
                          <label class="form-label">Kepala Unit</label>
                          <select name="kepala_unit_id" class="form-select">
                            <option value="">- Tidak ada (dilewati) -</option>
                            <?php foreach ($kandidatKepalaUnit as $u): ?>
                              <option value="<?= $u['id'] ?>" <?= $d['kepala_unit_id'] == $u['id'] ? 'selected' : '' ?>>
                                <?= e($u['name']) ?> (<?= e($u['email']) ?>)
                              </option>
                            <?php endforeach; ?>
                          </select>
                          <?php if (empty($kandidatKepalaUnit)): ?>
                            <div class="form-text text-warning">Belum ada akun ber-role Kepala Unit. Buat dulu lewat menu Karyawan.</div>
                          <?php endif; ?>
                        </div>
                        <div class="mb-1">
                          <label class="form-label">Pimpinan Unit</label>
                          <select name="pimpinan_unit_id" class="form-select">
                            <option value="">- Tidak ada (dilewati) -</option>
                            <?php foreach ($kandidatPimpinanUnit as $u): ?>
                              <option value="<?= $u['id'] ?>" <?= $d['pimpinan_unit_id'] == $u['id'] ? 'selected' : '' ?>>
                                <?= e($u['name']) ?> (<?= e($u['email']) ?>)
                              </option>
                            <?php endforeach; ?>
                          </select>
                          <?php if (empty($kandidatPimpinanUnit)): ?>
                            <div class="form-text text-warning">Belum ada akun ber-role Pimpinan Unit. Buat dulu lewat menu Karyawan.</div>
                          <?php endif; ?>
                        </div>
                        <div class="text-muted small mt-2">
                          Kosongkan salah satu / kedua kolom jika unit ini tidak memiliki posisi tersebut.
                          Pengajuan cuti akan langsung lanjut ke tahap berikutnya.
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

<div class="modal fade" id="modalTambahUnit" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="<?= url('departemen/store') ?>">
        <div class="modal-header">
          <h5 class="modal-title">Tambah Unit Kerja</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-1">
            <label class="form-label">Nama Unit / Departemen <span class="text-danger">*</span></label>
            <input type="text" name="nama" class="form-control" required>
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
