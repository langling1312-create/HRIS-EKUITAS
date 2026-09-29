<?php
if (!AuthHelper::check()) {
    header('Location: ' . BASE_URL . 'auth/login');
    exit;
}
require_once APP_ROOT . '/models/Notifikasi.php';
$notifikasiModel = new Notifikasi();
$jumlahBelumBaca = $notifikasiModel->countBelumDibaca(AuthHelper::id());
$notifTerbaru = $notifikasiModel->terbaru(AuthHelper::id(), 5);

// Badge jumlah cuti yang menunggu persetujuan Anda (khusus Kepala Unit / Pimpinan Unit).
$antreanApprovalSidebar = 0;
if (AuthHelper::isKepalaUnit() || AuthHelper::isPimpinanUnit()) {
    require_once APP_ROOT . '/models/Cuti.php';
    $cutiModelSidebar = new Cuti();
    $antreanApprovalSidebar = $cutiModelSidebar->countQueueForApprover(AuthHelper::id());
}

function navActive(string $needle, string $url): string
{
    return (strpos($url, $needle) !== false) ? 'active' : '';
}
$reqUrl = $_GET['url'] ?? '';
$fotoUrl = AuthHelper::foto()
    ? UPLOAD_URL . 'foto_profil/' . AuthHelper::foto()
    : 'https://ui-avatars.com/api/?background=2f6fed&color=fff&name=' . urlencode(AuthHelper::name());
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="theme-color" content="#0c1638">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="EKUITAS HRIS">
<meta name="format-detection" content="telephone=no">
<title><?= e($title ?? 'Dashboard') ?> - <?= e(APP_NAME) ?></title>
<link rel="icon" type="image/png" href="<?= asset('img/favicon-32.png') ?>">
<link rel="apple-touch-icon" href="<?= asset('img/favicon-180.png') ?>">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.css" rel="stylesheet">
<link href="<?= asset('css/style.css') ?>" rel="stylesheet">
</head>
<body>

<div class="sb-overlay" id="sbOverlay"></div>


<nav class="sb-sidenav" id="sbSidenav">
  <div class="brand">
    <span class="brand-icon"><img src="<?= asset('img/logo-icon.png') ?>" alt="Ekuitas University"></span>EKUITAS HRIS
  </div>

  <a href="<?= url('profil') ?>" class="user-mini text-decoration-none">
    <img src="<?= $fotoUrl ?>" alt="">
    <div>
      <div class="name"><?= e(AuthHelper::name()) ?></div>
      <div class="role"><?= e(role_label(AuthHelper::role())) ?></div>
    </div>
  </a>

  <div class="nav-heading">Menu Utama</div>
  <a href="<?= url('dashboard') ?>" class="nav-link <?= navActive('dashboard', $reqUrl) ?>">
    <i class="fa-solid fa-gauge"></i> Dashboard
  </a>

  <?php if (AuthHelper::isStaff()): ?>
    <a href="<?= url('absensi') ?>" class="nav-link <?= ($reqUrl === 'absensi' || strpos($reqUrl, 'absensi?') === 0) ? 'active' : '' ?>">
      <i class="fa-solid fa-clock"></i> Absensi
    </a>
    <a href="<?= url('absensi/rekap-saya') ?>" class="nav-link <?= navActive('absensi/rekap-saya', $reqUrl) ?>">
      <i class="fa-solid fa-clipboard-list"></i> Rekap Absensi
    </a>
    <a href="<?= url('cuti') ?>" class="nav-link <?= navActive('cuti', $reqUrl) ?>">
      <i class="fa-solid fa-calendar-days"></i> Cuti &amp; Izin
      <?php if (!empty($antreanApprovalSidebar)): ?>
        <span class="badge bg-warning text-dark ms-1"><?= $antreanApprovalSidebar ?></span>
      <?php endif; ?>
    </a>
    <a href="<?= url('payroll') ?>" class="nav-link <?= navActive('payroll', $reqUrl) ?>">
      <i class="fa-solid fa-file-invoice-dollar"></i> Slip Gaji
    </a>
    <a href="<?= url('shift') ?>" class="nav-link <?= navActive('shift', $reqUrl) ?>">
      <i class="fa-solid fa-calendar-week"></i> Jadwal Shift
    </a>
  <?php endif; ?>

  <?php if (AuthHelper::isHrOrAdmin()): ?>
    <a href="<?= url('karyawan') ?>" class="nav-link <?= navActive('karyawan', $reqUrl) ?>">
      <i class="fa-solid fa-users"></i> Karyawan
    </a>
    <a href="<?= url('absensi') ?>" class="nav-link <?= navActive('absensi', $reqUrl) ?>">
      <i class="fa-solid fa-clock"></i> Rekap Absensi
    </a>
    <a href="<?= url('cuti') ?>" class="nav-link <?= navActive('cuti', $reqUrl) ?>">
      <i class="fa-solid fa-calendar-days"></i> Cuti &amp; Izin
    </a>
    <a href="<?= url('payroll') ?>" class="nav-link <?= navActive('payroll', $reqUrl) ?>">
      <i class="fa-solid fa-money-check-dollar"></i> Penggajian
    </a>
    <a href="<?= url('shift') ?>" class="nav-link <?= navActive('shift', $reqUrl) ?>">
      <i class="fa-solid fa-calendar-week"></i> Shift &amp; Roster
    </a>
    <a href="<?= url('laporan') ?>" class="nav-link <?= navActive('laporan', $reqUrl) ?>">
      <i class="fa-solid fa-chart-column"></i> Laporan
    </a>
    <a href="<?= url('departemen') ?>" class="nav-link <?= navActive('departemen', $reqUrl) ?>">
      <i class="fa-solid fa-sitemap"></i> Unit Kerja
    </a>
  <?php endif; ?>

  <div class="nav-heading">Modul HR Lainnya</div>
  <a href="<?= url('reimbursement') ?>" class="nav-link <?= navActive('reimbursement', $reqUrl) ?>">
    <i class="fa-solid fa-receipt"></i> Reimbursement
  </a>
  <a href="<?= url('kasbon') ?>" class="nav-link <?= navActive('kasbon', $reqUrl) ?>">
    <i class="fa-solid fa-hand-holding-dollar"></i> Kasbon
  </a>
  <a href="<?= url('aset') ?>" class="nav-link <?= navActive('aset', $reqUrl) ?>">
    <i class="fa-solid fa-boxes-stacked"></i> <?= AuthHelper::isHrOrAdmin() ? 'Manajemen Aset' : 'Aset Saya' ?>
  </a>
  <a href="<?= url('kpi') ?>" class="nav-link <?= navActive('kpi', $reqUrl) ?>">
    <i class="fa-solid fa-chart-line"></i> Kinerja / KPI
  </a>
  <a href="<?= url('pelatihan') ?>" class="nav-link <?= navActive('pelatihan', $reqUrl) ?>">
    <i class="fa-solid fa-graduation-cap"></i> Pelatihan
  </a>
  <a href="<?= url('pengumuman') ?>" class="nav-link <?= navActive('pengumuman', $reqUrl) ?>">
    <i class="fa-solid fa-bullhorn"></i> Pengumuman
  </a>
  <?php if (AuthHelper::isHrOrAdmin()): ?>
    <a href="<?= url('rekrutmen') ?>" class="nav-link <?= navActive('rekrutmen', $reqUrl) ?>">
      <i class="fa-solid fa-user-tie"></i> Rekrutmen
    </a>
  <?php endif; ?>
  <a href="<?= url('resign') ?>" class="nav-link <?= navActive('resign', $reqUrl) ?>">
    <i class="fa-solid fa-door-open"></i> <?= AuthHelper::isHrOrAdmin() ? 'Resign & Offboarding' : 'Ajukan Resign' ?>
  </a>

  <?php if (AuthHelper::isAdmin()): ?>
    <div class="nav-heading">Pengaturan</div>
    <a href="<?= url('pengaturan') ?>" class="nav-link <?= navActive('pengaturan', $reqUrl) ?>">
      <i class="fa-solid fa-shield-halved"></i> Role &amp; Permission
    </a>
    <a href="<?= url('log') ?>" class="nav-link <?= navActive('log', $reqUrl) ?>">
      <i class="fa-solid fa-clipboard-list"></i> Log Aktivitas
    </a>
  <?php endif; ?>

  <div class="nav-footer">
    <a href="<?= url('auth/logout') ?>" class="nav-link text-warning mb-0">
      <i class="fa-solid fa-right-from-bracket"></i> Keluar
    </a>
  </div>
</nav>

<div class="sb-content">
  <div class="sb-topnav">
    <div class="d-flex align-items-center gap-3">
      <button class="btn-toggle" id="sidebarToggle"><i class="fa-solid fa-bars"></i></button>
      <div>
        <p class="breadcrumb-title mb-0"><?= e($title ?? '') ?></p>
        <p class="breadcrumb-sub mb-0 breadcrumb-trail">
          <a href="<?= url('dashboard') ?>">Dashboard</a>
          <?php if (($title ?? '') !== 'Dashboard'): ?> / <span class="current"><?= e($title ?? '') ?></span><?php endif; ?>
        </p>
      </div>
    </div>
    <div class="d-flex align-items-center gap-2">
      <div class="sb-search d-none d-lg-flex">
        <i class="fa-solid fa-search"></i>
        <input type="text" placeholder="Cari karyawan, menu...">
      </div>
      <div class="dropdown">
        <button class="icon-btn" type="button" data-bs-toggle="dropdown">
          <i class="fa-solid fa-bell"></i>
          <?php if ($jumlahBelumBaca > 0): ?>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:.6rem;">
              <?= $jumlahBelumBaca ?>
            </span>
          <?php endif; ?>
        </button>
        <div class="dropdown-menu dropdown-menu-end p-2" style="width:320px;">
          <h6 class="dropdown-header">Notifikasi Terbaru</h6>
          <?php if (empty($notifTerbaru)): ?>
            <p class="text-muted small px-2 mb-1">Tidak ada notifikasi.</p>
          <?php else: ?>
            <?php foreach ($notifTerbaru as $n): ?>
              <div class="dropdown-item small border-bottom py-2 <?= $n['dibaca'] ? '' : 'fw-bold' ?>">
                <?= e($n['pesan']) ?>
                <div class="text-muted" style="font-size:.7rem;"><?= format_tanggal($n['created_at'], 'd M Y H:i') ?></div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
      <div class="dropdown">
        <button class="btn d-flex align-items-center gap-2 border-0" type="button" data-bs-toggle="dropdown">
          <img src="<?= $fotoUrl ?>" class="avatar-sm">
          <span class="d-none d-md-flex flex-column align-items-start lh-1">
            <span class="fw-semibold" style="font-size:.85rem;"><?= e(AuthHelper::name()) ?></span>
            <span class="text-muted" style="font-size:.72rem;"><?= e(role_label(AuthHelper::role())) ?></span>
          </span>
          <i class="fa-solid fa-chevron-down small text-muted"></i>
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
          <li><a class="dropdown-item" href="<?= url('profil') ?>"><i class="fa-solid fa-user me-2"></i>Profil Saya</a></li>
          <li><hr class="dropdown-divider"></li>
          <li><a class="dropdown-item text-danger" href="<?= url('auth/logout') ?>"><i class="fa-solid fa-right-from-bracket me-2"></i>Logout</a></li>
        </ul>
      </div>
    </div>
  </div>

  <div class="sb-main">
    <?php
      $flashSuccess = SessionHelper::flash('success');
      $flashError = SessionHelper::flash('error');
    ?>
    <?php if ($flashSuccess): ?>
      <div class="alert alert-success alert-dismissible alert-auto-hide fade show" role="alert">
        <i class="fa-solid fa-circle-check me-2"></i><?= $flashSuccess ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>
    <?php if ($flashError): ?>
      <div class="alert alert-danger alert-dismissible alert-auto-hide fade show" role="alert">
        <i class="fa-solid fa-triangle-exclamation me-2"></i><?= $flashError ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <?= $content ?>
  </div>

  <footer class="text-center text-muted small py-3">
    &copy; <?= date('Y') ?> HRIS - EKUITAS HRIS Indonesia. All rights reserved.
  </footer>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="<?= asset('js/script.js') ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const bellButton = document.querySelector('[data-bs-toggle="dropdown"] .fa-bell');
    if (bellButton) {
        bellButton.closest('button').addEventListener('click', function() {
            fetch('<?= url("dashboard/bacaSemua") ?>', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            }).then(() => {
                const badge = bellButton.closest('button').querySelector('.badge');
                if (badge) badge.remove();
            });
        });
    }
});
</script>
<?php if (!empty($extraScript)) echo $extraScript; ?>
</body>
</html>