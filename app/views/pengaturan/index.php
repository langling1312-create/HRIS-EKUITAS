<?php
$menuIcon = [
    'dashboard'  => 'fa-gauge',
    'karyawan'   => 'fa-users',
    'absensi'    => 'fa-clock',
    'cuti'       => 'fa-calendar-days',
    'payroll'    => 'fa-money-check-dollar',
    'laporan'    => 'fa-chart-column',
    'pengaturan' => 'fa-shield-halved',
    'log'        => 'fa-clipboard-list',
];
$menuLabel = [
    'dashboard'  => 'Dashboard',
    'karyawan'   => 'Manajemen Karyawan',
    'absensi'    => 'Absensi',
    'cuti'       => 'Cuti',
    'payroll'    => 'Penggajian',
    'laporan'    => 'Laporan',
    'pengaturan' => 'Pengaturan Role',
    'log'        => 'Log Aktivitas',
];
$menuDesc = [
    'dashboard'  => 'Akses ke dashboard utama',
    'karyawan'   => 'Kelola data karyawan',
    'absensi'    => 'Lihat dan kelola data absensi',
    'cuti'       => 'Kelola pengajuan dan data cuti',
    'payroll'    => 'Akses data dan proses penggajian',
    'laporan'    => 'Lihat dan unduh laporan',
    'pengaturan' => 'Kelola role dan permission pengguna',
    'log'        => 'Lihat log aktivitas pengguna',
];
?>
<ul class="nav nav-tabs mb-3" role="tablist">
  <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabRole" type="button">Role &amp; Permission</button></li>
  <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabSistem" type="button">Pengaturan Umum</button></li>
</ul>

<div class="tab-content">
  <div class="tab-pane fade show active" id="tabRole">
    <div class="card mb-3">
      <div class="card-header">
        Kelola role pengguna dan atur akses permission untuk setiap modul sistem sesuai tanggung jawab masing-masing.
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table align-middle mb-0">
            <thead>
              <tr>
                <th>Role</th>
                <?php foreach ($daftarMenu as $menu): ?>
                  <th class="text-center"><?= e($menuLabel[$menu]) ?></th>
                <?php endforeach; ?>
                <th class="text-end">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($roles as $role): ?>
                <tr>
                  <td>
                    <span class="badge badge-status-info"><i class="fa-solid fa-shield me-1"></i><?= e(role_label($role)) ?></span>
                  </td>
                  <?php foreach ($daftarMenu as $menu): ?>
                    <td class="text-center">
                      <?php if ($role === 'admin' || !empty($permissions[$role][$menu])): ?>
                        <i class="fa-solid fa-circle-check text-success"></i>
                      <?php else: ?>
                        <i class="fa-regular fa-circle text-muted opacity-50"></i>
                      <?php endif; ?>
                    </td>
                  <?php endforeach; ?>
                  <td class="text-end">
                    <?php if ($role !== 'admin'): ?>
                      <button class="btn btn-sm btn-outline-brand" data-bs-toggle="modal" data-bs-target="#modalEditRole_<?= $role ?>">
                        <i class="fa-solid fa-pen me-1"></i>Edit
                      </button>
                    <?php else: ?>
                      <span class="text-muted small">Akses Penuh</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <div class="tab-pane fade" id="tabSistem">
    <div class="card">
      <div class="card-header">Pengaturan Umum Sistem</div>
      <div class="card-body">
        <form method="POST" action="<?= url('pengaturan/simpan-sistem') ?>">
          <div class="mb-3">
            <label class="form-label">Nama Perusahaan</label>
            <input type="text" name="nama_perusahaan" class="form-control" value="<?= e($pengaturan['nama_perusahaan'] ?? '') ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">Tahun Anggaran</label>
            <input type="text" name="tahun_anggaran" class="form-control" value="<?= e($pengaturan['tahun_anggaran'] ?? '') ?>">
          </div>
          <hr>
          <p class="fw-semibold mb-2"><i class="fa-solid fa-file-invoice-dollar text-primary me-1"></i>Pajak &amp; BPJS (Payroll Otomatis)</p>
          <div class="row">
            <div class="col-md-4 mb-3">
              <label class="form-label">Persentase BPJS Kesehatan (%)</label>
              <input type="number" step="0.1" name="persen_bpjs_kesehatan" class="form-control" value="<?= e($pengaturan['persen_bpjs_kesehatan'] ?? '1') ?>">
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label">Persentase BPJS Ketenagakerjaan (%)</label>
              <input type="number" step="0.1" name="persen_bpjs_jht" class="form-control" value="<?= e($pengaturan['persen_bpjs_jht'] ?? '2') ?>">
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label">Persentase PPh 21 (%)</label>
              <input type="number" step="0.1" name="persen_pph21" class="form-control" value="<?= e($pengaturan['persen_pph21'] ?? '5') ?>">
            </div>
          </div>
          <p class="text-muted small">Persentase ini akan otomatis dihitung dari gaji pokok setiap kali HR memproses gaji bulanan di menu Penggajian.</p>
          <hr>
          <p class="fw-semibold mb-2"><i class="fa-solid fa-utensils text-primary me-1"></i>Uang Makan Harian</p>
          <div class="row">
            <div class="col-md-4 mb-3">
              <label class="form-label">Nominal Uang Makan per Hari (Rp)</label>
              <input type="number" step="1000" min="0" name="uang_makan_harian" class="form-control" value="<?= e($pengaturan['uang_makan_harian'] ?? '40000') ?>">
            </div>
          </div>
          <p class="text-muted small">
            Karyawan yang check-in dengan keterlambatan <strong>lebih dari 30 menit</strong> tidak akan
            mendapatkan uang makan pada hari itu. Nominal di atas otomatis dipotong dari gaji bulanan
            karyawan bersangkutan (kolom "Potongan" pada menu Penggajian).
          </p>
          <button type="submit" class="btn btn-brand">
            <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Pengaturan
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Modal Edit Role (per role selain admin) -->
<?php foreach ($roles as $role): if ($role === 'admin') continue; ?>
  <div class="modal fade" id="modalEditRole_<?= $role ?>" tabindex="-1">
    <div class="modal-dialog modal-dialog-scrollable">
      <div class="modal-content">
        <form method="POST" action="<?= url('pengaturan/simpan-role') ?>">
          <input type="hidden" name="role" value="<?= e($role) ?>">
          <div class="modal-header">
            <h5 class="modal-title">Edit Role: <?= e(role_label($role)) ?></h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <label class="form-label small text-muted">Permission Akses</label>
            <p class="text-muted small">Atur akses untuk setiap modul sistem sesuai role ini.</p>
            <?php foreach ($daftarMenu as $menu): ?>
              <div class="d-flex align-items-center justify-content-between border-bottom py-2">
                <div class="d-flex align-items-center gap-2">
                  <span class="icon-box bg-icon-blue" style="width:34px;height:34px;font-size:.85rem;"><i class="fa-solid <?= $menuIcon[$menu] ?>"></i></span>
                  <div>
                    <div class="fw-semibold small"><?= e($menuLabel[$menu]) ?></div>
                    <div class="text-muted" style="font-size:.72rem;"><?= e($menuDesc[$menu]) ?></div>
                  </div>
                </div>
                <div class="form-check form-switch mb-0">
                  <input class="form-check-input" type="checkbox" name="menu[]" value="<?= $menu ?>"
                    <?= !empty($permissions[$role][$menu]) ? 'checked' : '' ?>>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-brand">Save Changes</button>
          </div>
        </form>
      </div>
    </div>
  </div>
<?php endforeach; ?>
