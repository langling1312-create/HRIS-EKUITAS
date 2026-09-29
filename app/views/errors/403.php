<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>403 - Akses Ditolak</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
</head>
<body class="d-flex align-items-center justify-content-center" style="min-height:100vh; background:#f4f6f9;">
  <div class="text-center">
    <i class="fa-solid fa-lock fa-4x text-danger mb-3"></i>
    <h1 class="fw-bold">403</h1>
    <p class="text-muted mb-4">Anda tidak memiliki akses ke halaman ini.</p>
    <a href="<?= defined('BASE_URL') ? BASE_URL . 'dashboard' : '/' ?>" class="btn btn-primary" style="background:#2f6fed;border:none;">Kembali ke Dashboard</a>
  </div>
</body>
</html>