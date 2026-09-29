<div class="card mb-3">
  <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div class="d-flex align-items-center gap-3">
      <div class="icon-box bg-icon-blue"><i class="fa-solid fa-wallet"></i></div>
      <div>
        <div class="stat-label">Total Pendapatan (Year to Date)</div>
        <div class="stat-value"><?= format_rupiah($totalYtd) ?></div>
        <div class="stat-sub">Januari - Desember <?= $tahun ?></div>
      </div>
    </div>
    <form method="GET" action="<?= url('payroll') ?>" class="d-flex gap-2">
      <select name="bulan" class="form-select form-select-sm" style="width:150px;">
        <option value="0">Semua Bulan</option>
        <?php for ($b = 1; $b <= 12; $b++): ?>
          <option value="<?= $b ?>" <?= $b == $bulan ? 'selected' : '' ?>><?= nama_bulan($b) ?></option>
        <?php endfor; ?>
      </select>
      <select name="tahun" class="form-select form-select-sm" style="width:120px;">
        <?php for ($t = (int) date('Y') - 2; $t <= (int) date('Y') + 1; $t++): ?>
          <option value="<?= $t ?>" <?= $t == $tahun ? 'selected' : '' ?>><?= $t ?></option>
        <?php endfor; ?>
      </select>
      <button class="btn btn-sm btn-outline-secondary" type="submit">Tampilkan</button>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-header">Daftar Slip Gaji</div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead>
          <tr><th>Periode</th><th>Gaji Pokok</th><th>Tunjangan</th><th>Potongan</th><th>Total Bersih</th><th class="text-end">Aksi</th></tr>
        </thead>
        <tbody>
          <?php if (empty($daftarSlip)): ?>
            <tr><td colspan="6" class="text-center text-muted py-4">Belum ada slip gaji untuk periode ini.</td></tr>
          <?php else: ?>
            <?php foreach ($daftarSlip as $s): ?>
              <tr>
                <td class="fw-semibold"><?= nama_bulan($s['bulan']) ?> <?= $s['tahun'] ?></td>
                <td><?= format_rupiah($s['gaji_pokok']) ?></td>
                <td><?= format_rupiah($s['tunjangan']) ?></td>
                <td class="text-danger">- <?= format_rupiah($s['potongan']) ?></td>
                <td class="fw-bold"><?= format_rupiah($s['total']) ?></td>
                <td class="text-end">
                  <a href="<?= url('payroll/cetak/' . $s['id']) ?>" target="_blank" class="btn btn-sm btn-brand">
                    <i class="fa-solid fa-download me-1"></i> Download PDF
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
