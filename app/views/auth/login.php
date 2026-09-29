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
<title>Login - <?= e(APP_NAME) ?></title>
<link rel="icon" type="image/png" href="<?= asset('img/favicon-32.png') ?>">
<link rel="apple-touch-icon" href="<?= asset('img/favicon-180.png') ?>">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link href="<?= asset('css/style.css') ?>" rel="stylesheet">
</head>
<body>
<div class="auth-wrapper">
  <div class="card auth-card w-100">
    <div class="row g-0">
      <div class="col-md-5 auth-side d-none d-md-flex">
        <span class="brand-icon-lg"><img src="<?= asset('img/logo-icon.png') ?>" alt="Ekuitas University"></span>
        <h3 class="fw-bold mb-1">EKUITAS HRIS</h3>
        <p class="mb-0 opacity-75">Solusi cerdas kelola SDM untuk organisasi modern.</p>
        <ul class="feat-list">
          <li><i class="fa-solid fa-check"></i> Absensi online berbasis lokasi &amp; foto</li>
          <li><i class="fa-solid fa-check"></i> Penggajian otomatis &amp; slip digital</li>
          <li><i class="fa-solid fa-check"></i> Laporan &amp; analitik real-time</li>
        </ul>
      </div>
      <div class="col-md-7">
        <div class="p-4 p-md-5">
          <h4 class="fw-bold mb-1">Selamat Datang Kembali</h4>
          <p class="text-muted mb-4">Silakan masuk untuk melanjutkan ke dashboard Anda.</p>

          <?php $error = SessionHelper::flash('error'); $success = SessionHelper::flash('success'); ?>
          <?php if ($error): ?><div class="alert alert-danger py-2"><?= $error ?></div><?php endif; ?>
          <?php if ($success): ?><div class="alert alert-success py-2"><?= $success ?></div><?php endif; ?>

          <form method="POST" action="<?= url('auth/login') ?>">
            <div class="mb-3">
              <label class="form-label fw-semibold">Email</label>
              <div class="input-group">
                <span class="input-group-text bg-white"><i class="fa-regular fa-envelope"></i></span>
                <input type="email" name="email" class="form-control" placeholder="nama@perusahaan.com" required>
              </div>
            </div>
            <div class="mb-2">
              <label class="form-label fw-semibold">Password</label>
              <div class="input-group">
                <span class="input-group-text bg-white"><i class="fa-solid fa-lock"></i></span>
                <input type="password" name="password" class="form-control" placeholder="Password" required>
              </div>
            </div>
            <button type="submit" class="btn btn-brand w-100 py-2 mt-3">
              <i class="fa-solid fa-right-to-bracket me-1"></i> Login
            </button>
          </form>

          <div class="text-center text-muted my-3 small">atau</div>

          <p class="text-center text-muted mb-0">
            Belum punya akun? <a href="<?= url('auth/register') ?>" class="fw-semibold">Daftar</a>
          </p>
          <p class="text-center mt-2">
            <a href="<?= url('') ?>" class="text-muted small"><i class="fa-solid fa-arrow-left me-1"></i>Kembali ke Beranda</a>
          </p>

          <div class="mt-4 p-3 bg-light rounded small text-muted">
            <strong>Akun Demo:</strong><br>
            Admin: admin@hris.com / 123456<br>
            HR: hr@hris.com / 123456<br>
            Karyawan: karyawan@hris.com / 123456
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
