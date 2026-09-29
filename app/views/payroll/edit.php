<div class="card mb-3">
  <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
    <span>Edit Gaji - <?= e($slip['name']) ?> (<?= nama_bulan((int) $slip['bulan']) ?> <?= $slip['tahun'] ?>)</span>
    <a href="<?= url('payroll?bulan=' . $slip['bulan'] . '&tahun=' . $slip['tahun']) ?>" class="btn btn-sm btn-outline-secondary">
      <i class="fa-solid fa-arrow-left me-1"></i> Kembali
    </a>
  </div>
</div>

<div class="row g-3">
  <div class="col-lg-8">
    <form method="POST" action="<?= url('payroll/update/' . $slip['id']) ?>" id="formEditGaji">
      <div class="card mb-3">
        <div class="card-header">Data Karyawan</div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label text-muted small mb-1">Nama</label>
              <div class="fw-semibold"><?= e($slip['name']) ?></div>
            </div>
            <div class="col-md-3">
              <label class="form-label text-muted small mb-1">NIP</label>
              <div class="fw-semibold"><?= e($slip['nip'] ?? '-') ?></div>
            </div>
            <div class="col-md-3">
              <label class="form-label text-muted small mb-1">Jabatan</label>
              <div class="fw-semibold"><?= e($slip['jabatan'] ?? '-') ?></div>
            </div>
          </div>
        </div>
      </div>

      <div class="card mb-3">
        <div class="card-header">Gaji Pokok &amp; Rincian Tunjangan</div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Gaji Pokok</label>
              <input type="number" min="0" class="form-control calc" name="gaji_pokok" value="<?= (float) $slip['gaji_pokok'] ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Tunjangan Jabatan</label>
              <input type="number" min="0" class="form-control calc" name="tunjangan_jabatan" value="<?= (float) ($slip['tunjangan_jabatan'] ?? 0) ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Tunjangan Transport / Makan</label>
              <input type="number" min="0" class="form-control calc" name="tunjangan_transport" value="<?= (float) ($slip['tunjangan_transport'] ?? 0) ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Tunjangan BPJS</label>
              <input type="number" min="0" class="form-control calc" name="tunjangan_bpjs" value="<?= (float) ($slip['tunjangan_bpjs'] ?? 0) ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Tunjangan Sakit</label>
              <input type="number" min="0" class="form-control calc" name="tunjangan_sakit" value="<?= (float) ($slip['tunjangan_sakit'] ?? 0) ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Tunjangan Lainnya</label>
              <input type="number" min="0" class="form-control calc" name="tunjangan_lainnya" value="<?= (float) ($slip['tunjangan_lainnya'] ?? 0) ?>">
            </div>
          </div>
        </div>
      </div>

      <div class="card mb-3">
        <div class="card-header">Potongan</div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Potongan Lain-lain</label>
              <input type="number" min="0" class="form-control calc" name="potongan" value="<?= (float) $slip['potongan'] ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Cicilan Kasbon</label>
              <input type="number" min="0" class="form-control calc" name="potongan_kasbon" value="<?= (float) ($slip['potongan_kasbon'] ?? 0) ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">BPJS Kesehatan</label>
              <input type="number" min="0" class="form-control calc" name="bpjs_kesehatan" value="<?= (float) ($slip['bpjs_kesehatan'] ?? 0) ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">BPJS Ketenagakerjaan (JHT)</label>
              <input type="number" min="0" class="form-control calc" name="bpjs_ketenagakerjaan" value="<?= (float) ($slip['bpjs_ketenagakerjaan'] ?? 0) ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">PPh 21</label>
              <input type="number" min="0" class="form-control calc" name="pph21" value="<?= (float) ($slip['pph21'] ?? 0) ?>">
            </div>
          </div>
        </div>
      </div>

      <div class="d-flex justify-content-end gap-2 mb-4">
        <a href="<?= url('payroll?bulan=' . $slip['bulan'] . '&tahun=' . $slip['tahun']) ?>" class="btn btn-secondary">Batal</a>
        <button type="submit" class="btn btn-brand"><i class="fa-solid fa-floppy-disk me-1"></i> Simpan Perubahan</button>
      </div>
    </form>
  </div>

  <div class="col-lg-4">
    <div class="card" style="position: sticky; top: 1rem;">
      <div class="card-header">Ringkasan</div>
      <div class="card-body">
        <table class="table table-sm mb-0">
          <tbody>
            <tr><td>Gaji Pokok</td><td class="text-end" id="ringkasGajiPokok">Rp 0</td></tr>
            <tr><td>Total Tunjangan</td><td class="text-end" id="ringkasTunjangan">Rp 0</td></tr>
            <tr><td class="text-danger">Potongan</td><td class="text-end text-danger" id="ringkasPotongan">- Rp 0</td></tr>
            <tr><td class="text-danger">BPJS Kesehatan</td><td class="text-end text-danger" id="ringkasBpjsKes">- Rp 0</td></tr>
            <tr><td class="text-danger">BPJS Ketenagakerjaan</td><td class="text-end text-danger" id="ringkasBpjsJht">- Rp 0</td></tr>
            <tr><td class="text-danger">PPh 21</td><td class="text-end text-danger" id="ringkasPph21">- Rp 0</td></tr>
            <tr><td class="text-danger">Kasbon</td><td class="text-end text-danger" id="ringkasKasbon">- Rp 0</td></tr>
            <tr class="border-top"><td class="fw-bold">Total Diterima</td><td class="text-end fw-bold" id="ringkasTotal">Rp 0</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
(function () {
  const form = document.getElementById('formEditGaji');

  function formatRupiah(angka) {
    angka = Math.round(angka || 0);
    return 'Rp ' + angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
  }

  function num(name) {
    const el = form.querySelector('[name="' + name + '"]');
    return el ? (parseFloat(el.value) || 0) : 0;
  }

  function hitungUlang() {
    const gajiPokok = num('gaji_pokok');
    const tunjangan = num('tunjangan_jabatan') + num('tunjangan_transport') + num('tunjangan_bpjs') + num('tunjangan_sakit') + num('tunjangan_lainnya');
    const potongan = num('potongan');
    const bpjsKes = num('bpjs_kesehatan');
    const bpjsJht = num('bpjs_ketenagakerjaan');
    const pph21 = num('pph21');
    const kasbon = num('potongan_kasbon');
    const total = Math.max(0, gajiPokok + tunjangan - potongan - bpjsKes - bpjsJht - pph21 - kasbon);

    document.getElementById('ringkasGajiPokok').textContent = formatRupiah(gajiPokok);
    document.getElementById('ringkasTunjangan').textContent = formatRupiah(tunjangan);
    document.getElementById('ringkasPotongan').textContent = '- ' + formatRupiah(potongan);
    document.getElementById('ringkasBpjsKes').textContent = '- ' + formatRupiah(bpjsKes);
    document.getElementById('ringkasBpjsJht').textContent = '- ' + formatRupiah(bpjsJht);
    document.getElementById('ringkasPph21').textContent = '- ' + formatRupiah(pph21);
    document.getElementById('ringkasKasbon').textContent = '- ' + formatRupiah(kasbon);
    document.getElementById('ringkasTotal').textContent = formatRupiah(total);
  }

  form.querySelectorAll('.calc').forEach(function (el) {
    el.addEventListener('input', hitungUlang);
  });

  hitungUlang();
})();
</script>
