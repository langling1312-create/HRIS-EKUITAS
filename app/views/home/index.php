<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="theme-color" content="#0c1638">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="format-detection" content="telephone=no">
<title><?= e(APP_NAME) ?> - Sistem Informasi Manajemen SDM</title>
<link rel="icon" type="image/png" href="<?= asset('img/favicon-32.png') ?>">
<link rel="apple-touch-icon" href="<?= asset('img/favicon-180.png') ?>">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link href="<?= asset('css/style.css') ?>" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light bg-white navbar-landing sticky-top py-3">
  <div class="container">
    <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="<?= url('') ?>">
      <span class="brand-icon" style="width:32px;height:32px;border-radius:.5rem;display:inline-flex;align-items:center;justify-content:center;overflow:hidden;"><img src="<?= asset('img/logo-icon.png') ?>" alt="Ekuitas University" style="width:100%;height:100%;object-fit:contain;"></span>
      EKUITAS HRIS
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse justify-content-end" id="navMenu">
      <ul class="navbar-nav align-items-lg-center gap-2">
        <li class="nav-item"><a class="nav-link" href="#fitur">Fitur</a></li>
        <li class="nav-item"><a class="nav-link" href="#tentang">Tentang</a></li>
        <li class="nav-item">
          <a class="btn btn-outline-brand btn-sm px-3" href="<?= url('auth/login') ?>"><i class="fa-solid fa-right-to-bracket me-1"></i>Login</a>
        </li>
        <li class="nav-item">
          <a class="btn btn-brand btn-sm px-3" href="<?= url('auth/register') ?>"><i class="fa-solid fa-user-plus me-1"></i>Register</a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<section class="hero">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-lg-6 mb-5 mb-lg-0">
        <span class="badge bg-white text-white bg-opacity-25 mb-3 px-3 py-2" style="font-weight:600;">
          <i class="fa-solid fa-star me-1"></i> HRIS Terlengkap untuk EKUITAS
        </span>
        <h1 class="display-5 fw-bold mb-3">Kelola SDM Lebih Mudah &amp; Efisien</h1>
        <p class="lead opacity-75 mb-4">
          HRIS membantu EKUITAS mengelola karyawan, absensi, penggajian, dan laporan
          dalam satu sistem terintegrasi untuk meningkatkan produktivitas bisnis.
        </p>
        <a href="<?= url('auth/register') ?>" class="btn btn-light btn-lg px-4 me-2 fw-semibold">Mulai Sekarang <i class="fa-solid fa-arrow-right ms-1"></i></a>
        <a href="#fitur" class="btn btn-outline-light btn-lg px-4">Pelajari Lebih Lanjut</a>
        <div class="row mt-5 g-3">
          <div class="col-4">
            <div class="fw-bold fs-5">13+</div>
            <div class="small opacity-75">Modul Terintegrasi</div>
          </div>
          <div class="col-4">
            <div class="fw-bold fs-5">100%</div>
            <div class="small opacity-75">PHP Native</div>
          </div>
          <div class="col-4">
            <div class="fw-bold fs-5">24/7</div>
            <div class="small opacity-75">Akses Online</div>
          </div>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="bg-white rounded-4 shadow-lg p-3 text-dark">
          <div class="d-flex justify-content-between align-items-center mb-3 px-2">
            <span class="fw-bold small d-flex align-items-center"><img src="<?= asset('img/logo-icon.png') ?>" alt="Ekuitas University" style="width:18px;height:18px;object-fit:contain;" class="me-1">HRIS Dashboard</span>
            <div class="d-flex gap-2">
              <span class="badge bg-light text-muted">Preview</span>
            </div>
          </div>
          <div class="row g-2 px-2">
            <div class="col-6">
              <div class="border rounded-3 p-2">
                <div class="text-muted small">Total Karyawan</div>
                <div class="fw-bold fs-5 text-primary">248</div>
              </div>
            </div>
            <div class="col-6">
              <div class="border rounded-3 p-2">
                <div class="text-muted small">Hadir Hari Ini</div>
                <div class="fw-bold fs-5 text-success">201</div>
              </div>
            </div>
          </div>
          <div class="px-2 mt-2">
            <div class="border rounded-3 p-2" style="height:140px;display:flex;align-items:flex-end;gap:6px;">
              <?php $bars = [40,55,35,70,60,90,50]; foreach ($bars as $h): ?>
                <div style="flex:1;background:var(--blue);border-radius:4px 4px 0 0;height:<?= $h ?>%;"></div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section id="fitur" class="py-5">
  <div class="container">
    <div class="text-center mb-5">
      <h2 class="fw-bold">Fitur Utama</h2>
      <p class="text-muted">Semua kebutuhan HR dalam satu aplikasi</p>
    </div>
    <div class="row g-4">
      <div class="col-md-4">
        <div class="feature-icon"><i class="fa-solid fa-clock"></i></div>
        <h5 class="fw-bold">Absensi Online</h5>
        <p class="text-muted">Pantau kehadiran karyawan secara online berbasis web dan mobile, dilengkapi fitur lokasi dan foto validasi diri.</p>
      </div>
      <div class="col-md-4">
        <div class="feature-icon"><i class="fa-solid fa-calendar-days"></i></div>
        <h5 class="fw-bold">Cuti &amp; Izin</h5>
        <p class="text-muted">Ajukan cuti secara online, pantau sisa cuti, dan lihat status persetujuan secara real-time.</p>
      </div>
      <div class="col-md-4">
        <div class="feature-icon"><i class="fa-solid fa-sack-dollar"></i></div>
        <h5 class="fw-bold">Penggajian Otomatis</h5>
        <p class="text-muted">Hitung gaji, tunjangan, potongan, dan lembur secara otomatis. Hasil akurat, proses cepat, dan slip gaji dapat diakses kapan saja.</p>
      </div>
      <div class="col-md-4">
        <div class="feature-icon"><i class="fa-solid fa-users"></i></div>
        <h5 class="fw-bold">Manajemen Karyawan</h5>
        <p class="text-muted">Kelola data karyawan secara terpusat: tambah, edit, hapus, dan cari dengan mudah.</p>
      </div>
      <div class="col-md-4">
        <div class="feature-icon"><i class="fa-solid fa-chart-column"></i></div>
        <h5 class="fw-bold">Laporan Real-time</h5>
        <p class="text-muted">Dapatkan laporan SDM secara real-time dan visualisasi data interaktif untuk pengambilan keputusan lebih cepat &amp; tepat.</p>
      </div>
      <div class="col-md-4">
        <div class="feature-icon"><i class="fa-solid fa-shield-halved"></i></div>
        <h5 class="fw-bold">Role &amp; Permission</h5>
        <p class="text-muted">Kontrol akses sesuai peran: Admin, HR, dan Karyawan, lengkap dengan log aktivitas untuk audit.</p>
      </div>
    </div>
  </div>
</section>

<section id="tentang" class="py-5" style="background:#f4f6fb;">
  <div class="container text-center">
    <h2 class="fw-bold mb-3">Tentang HRIS</h2>
    <p class="text-muted mx-auto" style="max-width:700px;">
      HRIS dikembangkan untuk membantu perusahaan mengelola sumber daya manusia secara efisien,
      mulai dari absensi harian, pengajuan cuti, penggajian, hingga pelaporan analitik dalam satu sistem yang terintegrasi.
    </p>
  </div>
</section>

<footer style="background:var(--navy);color:rgba(255,255,255,.7);" class="pt-5 pb-3">
  <div class="container">
    <div class="row g-4">
      <div class="col-lg-4">
        <div class="d-flex align-items-center gap-2 mb-2">
          <span style="width:32px;height:32px;border-radius:.5rem;display:inline-flex;align-items:center;justify-content:center;overflow:hidden;"><img src="<?= asset('img/logo-icon.png') ?>" alt="Ekuitas University" style="width:100%;height:100%;object-fit:contain;"></span>
          <span class="fw-bold text-white">EKUITAS HRIS</span>
        </div>
        <p class="small">Solusi HRIS terintegrasi untuk membantu mengelola SDM EKUITAS lebih mudah, efisien, dan terukur.</p>
      </div>
      <div class="col-lg-2 col-6">
        <h6 class="text-white">Produk</h6>
        <ul class="list-unstyled small">
          <li>Absensi</li><li>Penggajian</li><li>Cuti &amp; Izin</li><li>Laporan</li>
        </ul>
      </div>
      <div class="col-lg-2 col-6">
        <h6 class="text-white">Perusahaan</h6>
        <ul class="list-unstyled small">
          <li>Tentang Kami</li><li>Fitur</li><li>Karir</li>
        </ul>
      </div>
      <div class="col-lg-2 col-6">
        <h6 class="text-white">Bantuan</h6>
        <ul class="list-unstyled small">
          <li>Pusat Bantuan</li><li>Panduan</li><li>FAQ</li>
        </ul>
      </div>
    </div>
    <hr class="border-secondary">
    <p class="text-center small mb-0">&copy; <?= date('Y') ?> HRIS - EKUITAS HRIS All rights reserved.</p>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
