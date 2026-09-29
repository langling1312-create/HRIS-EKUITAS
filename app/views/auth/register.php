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
<title>Register - <?= e(APP_NAME) ?></title>
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
        <h3 class="fw-bold mb-1">Solusi Cerdas untuk<br>Manajemen SDM Modern</h3>
        <p class="mb-0 opacity-75">Kelola karyawan, absensi, payroll, dan data perusahaan dalam satu platform terintegrasi.</p>
        <ul class="feat-list">
          <li><i class="fa-solid fa-shield"></i> Aman &amp; Terpercaya — data karyawan terlindungi dengan keamanan terbaik</li>
          <li><i class="fa-solid fa-bolt"></i> Efisien &amp; Praktis — otomatisasi proses HR untuk produktivitas</li>
          <li><i class="fa-solid fa-chart-line"></i> Insight Berharga — laporan &amp; analitik untuk keputusan tepat</li>
        </ul>
      </div>
      <div class="col-md-7">
        <div class="p-4 p-md-5">
          <h4 class="fw-bold mb-1">Buat Akun Baru</h4>
          <p class="text-muted mb-4">Daftar untuk mulai menggunakan HRIS.</p>

          <?php $error = SessionHelper::flash('error'); ?>
          <?php if ($error): ?><div class="alert alert-danger py-2"><?= $error ?></div><?php endif; ?>

          <form method="POST" action="<?= url('auth/register') ?>">
            <div class="mb-3">
              <label class="form-label fw-semibold">Nama Lengkap</label>
              <div class="input-group">
                <span class="input-group-text bg-white"><i class="fa-regular fa-user"></i></span>
                <input type="text" name="name" class="form-control" value="<?= e(old('name')) ?>" placeholder="Masukkan nama lengkap Anda" required>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Email</label>
              <div class="input-group">
                <span class="input-group-text bg-white"><i class="fa-regular fa-envelope"></i></span>
                <input type="email" name="email" class="form-control" value="<?= e(old('email')) ?>" placeholder="Masukkan email Anda" required>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold">Password</label>
                <div class="input-group">
                  <span class="input-group-text bg-white"><i class="fa-solid fa-lock"></i></span>
                  <input type="password" name="password" class="form-control" placeholder="Minimal 6 karakter" required>
                </div>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold">Konfirmasi Password</label>
                <div class="input-group">
                  <span class="input-group-text bg-white"><i class="fa-solid fa-lock"></i></span>
                  <input type="password" name="confirm_password" class="form-control" placeholder="Ulangi password" required>
                </div>
              </div>
            </div>
            <button type="submit" class="btn btn-brand w-100 py-2 mt-2">
              <i class="fa-solid fa-user-plus me-1"></i> Daftar Sekarang
            </button>
          </form>

          <div class="text-center text-muted my-3 small">atau</div>

          <p class="text-center text-muted mb-0">
            Sudah punya akun? <a href="<?= url('auth/login') ?>" class="fw-semibold">Login di sini</a>
          </p>
          <p class="text-center mt-2">
            <a href="<?= url('') ?>" class="text-muted small"><i class="fa-solid fa-arrow-left me-1"></i>Kembali ke Beranda</a>
          </p>
        </div>
      </div>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
