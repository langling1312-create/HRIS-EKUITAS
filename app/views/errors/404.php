<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>404 - Halaman Tidak Ditemukan</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
</head>
<body class="d-flex align-items-center justify-content-center" style="min-height:100vh; background:#f4f6f9;">
  <div class="text-center">
    <i class="fa-solid fa-triangle-exclamation fa-4x text-warning mb-3"></i>
    <h1 class="fw-bold">404</h1>
    <p class="text-muted mb-4">Halaman yang Anda cari tidak ditemukan.</p>
    <a href="<?= defined('BASE_URL') ? BASE_URL : '/' ?>" class="btn btn-primary" style="background:#2f6fed;border:none;">Kembali ke Beranda</a>
  </div>
</body>
</html>