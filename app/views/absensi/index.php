<div class="card mb-3">
  <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
      <div class="fw-bold">Status Hari Ini - <?= format_tanggal(date('Y-m-d')) ?></div>
      <div class="text-muted small">Waktu sekarang: <span class="fw-bold text-primary" id="jamSekarang"><?= date('H:i:s') ?></span></div>
    </div>
    <div class="d-flex gap-2 align-items-center">
      <?php if ($absenHariIni && in_array($absenHariIni['status'], ['izin', 'sakit'], true)): ?>
        <?= badge_status_absensi($absenHariIni['status']) ?>
        <span class="text-muted small"><?= e($absenHariIni['lokasi']) ?></span>
      <?php else: ?>
        <?php if (!$absenHariIni): ?>
          <span class="btn btn-checkin disabled"><i class="fa-solid fa-right-to-bracket me-1"></i> Check-in <?= date('H:i') ?></span>
        <?php else: ?>
          <span class="btn btn-checkin"><i class="fa-solid fa-right-to-bracket me-1"></i> Check-in <?= e($absenHariIni['check_in']) ?></span>
        <?php endif; ?>
        <?php if (!empty($absenHariIni['check_out'])): ?>
          <span class="btn btn-checkout"><i class="fa-solid fa-right-from-bracket me-1"></i> Check-out <?= e($absenHariIni['check_out']) ?></span>
        <?php endif; ?>
      <?php endif; ?>
      <?php if (!$absenHariIni): ?>
        <button class="btn btn-outline-brand" data-bs-toggle="modal" data-bs-target="#modalIzinSakit">
          <i class="fa-solid fa-notes-medical me-1"></i> Ajukan Izin/Sakit
        </button>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php if ($absenHariIni && in_array($absenHariIni['status'], ['izin', 'sakit'], true)): ?>

  <div class="card mb-3">
    <div class="card-body">
      <p class="mb-2"><i class="fa-solid fa-circle-info text-info me-1"></i> Anda mengajukan <strong><?= e(ucfirst($absenHariIni['status'])) ?></strong> untuk hari ini.</p>
      <?php 
        $fileBukti = $absenHariIni['foto_in'] ?? $absenHariIni['bukti_sakit'] ?? null;
      ?>
      <?php if ($fileBukti): ?>
        <?php $ext = strtolower(pathinfo($fileBukti, PATHINFO_EXTENSION)); ?>
        <div class="mb-2 fw-semibold small">Bukti / Berkas Pendukung:</div>
        <?php if ($ext === 'pdf'): ?>
          <a href="<?= UPLOAD_URL . 'absensi/' . e($fileBukti) ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
            <i class="fa-solid fa-file-pdf me-1"></i> Lihat File PDF
          </a>
        <?php else: ?>
          <a href="<?= UPLOAD_URL . 'absensi/' . e($fileBukti) ?>" target="_blank">
            <img src="<?= UPLOAD_URL . 'absensi/' . e($fileBukti) ?>" class="rounded" style="max-width:220px;">
          </a>
        <?php endif; ?>
      <?php endif; ?>
    </div>
  </div>

<?php else: ?>

<div class="row g-3 mb-3">
  <div class="col-lg-6">
    <div class="card h-100">
      <div class="card-header">Foto Webcam</div>
      <div class="card-body text-center webcam-box">
        <?php if (!$absenHariIni): ?>
          <?php if ($jadwalHariIni): ?>
            <div class="alert alert-info small text-start mb-3">
              <i class="fa-solid fa-calendar-check me-1"></i>
              Shift Anda hari ini (dijadwalkan HR): <strong><?= e($jadwalHariIni['nama_shift']) ?>
              (<?= e(substr($jadwalHariIni['jam_mulai'], 0, 5)) ?> - <?= e(substr($jadwalHariIni['jam_selesai'], 0, 5)) ?>)</strong>.
              Check-in setelah 1 jam dari jam mulai akan otomatis tercatat <strong>ALFA</strong>.
              Keterlambatan dikenakan potongan gaji <strong>Rp 10.000 per 10 menit</strong> keterlambatan
              (dibulatkan ke atas, mis. telat 11 menit = potongan Rp 20.000).
            </div>
          <?php endif; ?>

          <video id="video" autoplay playsinline muted class="mb-2" style="width:100%; max-height:240px; object-fit:cover; background:#000;"></video>
          <canvas id="canvas" class="d-none"></canvas>
          <div id="cameraError" class="alert alert-warning small d-none"></div>

          <div class="form-check text-start mb-2">
            <input class="form-check-input" type="checkbox" id="toggleFoto" checked>
            <label class="form-check-label small" for="toggleFoto">Gunakan foto (webcam) saat absen</label>
          </div>
          <div class="small text-muted text-start mb-2" id="ketFotoOpsional">
            Foto bersifat opsional. Anda tetap bisa check-in tanpa foto dengan mencentang/menghapus centang di atas.
          </div>

          <?php if ($jadwalHariIni): ?>
            <form method="POST" action="<?= url('absensi/checkin') ?>" id="formCheckin"
                  data-jam-mulai-shift="<?= e(substr($jadwalHariIni['jam_mulai'], 0, 5)) ?>">
              <input type="hidden" name="foto" id="fotoInInput">
              <input type="hidden" name="lokasi" id="lokasiInInput">
              <button type="submit" class="btn btn-brand w-100 mt-2" id="btnCheckin" disabled>
                <i class="fa-solid fa-camera me-1"></i> Ambil Foto &amp; Check-in
              </button>
            </form>
          <?php else: ?>
            <div class="alert alert-warning small text-start mb-0">
              <i class="fa-solid fa-triangle-exclamation me-1"></i>
              Anda belum dijadwalkan shift kerja hari ini oleh HR/Admin, sehingga belum bisa check-in.
              Silakan hubungi HR jika ini tidak sesuai.
            </div>
          <?php endif; ?>

        <?php elseif (empty($absenHariIni['check_out'])): ?>
          <?php if ($absenHariIni['foto_in'] && !in_array($absenHariIni['status'], ['izin', 'sakit'])): ?>
            <img src="<?= UPLOAD_URL . 'absensi/' . e($absenHariIni['foto_in']) ?>" class="rounded mb-2" style="max-width:220px;">
          <?php endif; ?>
          <video id="video" autoplay playsinline muted class="mb-2" style="width:100%; max-height:240px; object-fit:cover; background:#000;"></video>
          <canvas id="canvas" class="d-none"></canvas>
          <div id="cameraError" class="alert alert-warning small d-none"></div>

          <div class="form-check text-start mb-2">
            <input class="form-check-input" type="checkbox" id="toggleFoto" checked>
            <label class="form-check-label small" for="toggleFoto">Gunakan foto (webcam) saat absen</label>
          </div>
          <div class="small text-muted text-start mb-2" id="ketFotoOpsional">
            Foto bersifat opsional. Anda tetap bisa check-out tanpa foto dengan mencentang/menghapus centang di atas.
          </div>

          <form method="POST" action="<?= url('absensi/checkout') ?>" id="formCheckout">
            <input type="hidden" name="foto" id="fotoOutInput">
            <button type="submit" class="btn btn-checkout w-100 mt-2" id="btnCheckout" disabled>
              <i class="fa-solid fa-camera me-1"></i> Ambil Foto &amp; Check-out
            </button>
          </form>
        <?php else: ?>
          <div class="d-flex justify-content-center gap-2">
            <?php if ($absenHariIni['foto_in'] && !in_array($absenHariIni['status'], ['izin', 'sakit'])): ?>
              <img src="<?= UPLOAD_URL . 'absensi/' . e($absenHariIni['foto_in']) ?>" class="rounded" style="max-width:150px;"
                   onerror="this.replaceWith(Object.assign(document.createElement('div'), {className:'text-muted small border rounded p-3', textContent:'Foto check-in tidak tersedia'}))">
            <?php endif; ?>
            <?php if ($absenHariIni['foto_out']): ?>
              <img src="<?= UPLOAD_URL . 'absensi/' . e($absenHariIni['foto_out']) ?>" class="rounded" style="max-width:150px;"
                   onerror="this.replaceWith(Object.assign(document.createElement('div'), {className:'text-muted small border rounded p-3', textContent:'Foto check-out tidak tersedia'}))">
            <?php endif; ?>
          </div>
          <p class="text-success mt-3 mb-0"><i class="fa-solid fa-circle-check me-1"></i>Absensi hari ini selesai.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="col-lg-6">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span>Lokasi</span>
        <span class="badge bg-secondary" id="lokasiBadge" style="display:none;">Mendeteksi...</span>
      </div>
      <div class="card-body">
        <div id="mapLokasi" class="map-live mb-2"></div>
        <div class="small text-muted" id="locationStatus">
          <?= $absenHariIni && $absenHariIni['lokasi'] ? '<i class="fa-solid fa-location-dot text-primary me-1"></i>' . e($absenHariIni['lokasi']) : 'Mendeteksi lokasi...' ?>
        </div>
        <?php if (!$absenHariIni): ?>
          <button type="button" id="btnRetryLokasi" class="btn btn-sm btn-outline-secondary mt-2" style="display:none;">
            <i class="fa-solid fa-rotate-right me-1"></i> Coba Deteksi Ulang
          </button>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php endif; ?>

<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
    <span>Riwayat Absensi</span>
    <form method="GET" action="<?= url('absensi') ?>" class="d-flex gap-2">
      <select name="bulan" class="form-select form-select-sm" style="width:130px;">
        <?php for ($b = 1; $b <= 12; $b++): ?>
          <option value="<?= $b ?>" <?= $b == $bulan ? 'selected' : '' ?>><?= nama_bulan($b) ?></option>
        <?php endfor; ?>
      </select>
      <select name="tahun" class="form-select form-select-sm" style="width:110px;">
        <?php for ($t = (int) date('Y') - 2; $t <= (int) date('Y') + 1; $t++): ?>
          <option value="<?= $t ?>" <?= $t == $tahun ? 'selected' : '' ?>><?= $t ?></option>
        <?php endfor; ?>
      </select>
      <button class="btn btn-sm btn-outline-secondary" type="submit">Filter</button>
      <a href="<?= url('absensi/export-excel?bulan=' . $bulan . '&tahun=' . $tahun) ?>" class="btn btn-sm btn-outline-brand">
        <i class="fa-solid fa-file-excel me-1"></i> Export Excel
      </a>
    </form>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead>
          <tr>
            <th>Tanggal</th>
            <th>Hari</th>
            <th>Shift</th>
            <th>Jam Masuk</th>
            <th>Jam Keluar</th>
            <th>Status</th>
            <th>Foto</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($riwayatAbsensi)): ?>
            <tr><td colspan="7" class="text-center text-muted py-4">Belum ada riwayat absensi pada periode ini.</td></tr>
          <?php else: ?>
            <?php foreach ($riwayatAbsensi as $r): ?>
              <tr>
                <td><?= format_tanggal($r['tanggal']) ?></td>
                <td><?= e(date('l', strtotime($r['tanggal']))) ?></td>
                <td><?= e($r['shift'] ?? '-') ?></td>
                <td><?= e($r['check_in'] ?? '-') ?></td>
                <td><?= e($r['check_out'] ?? '-') ?></td>
                <td>
                  <?php 
                    $status = strtolower(trim($r['status'] ?? ''));
                    if (empty($status)) {
                        $shift_parts = explode('-', $r['shift'] ?? '');
                        $jam_mulai = $shift_parts[0] ?? '08:00';
                        $check_in = $r['check_in'] ?? '00:00:00';
                        $status = ($check_in > $jam_mulai) ? 'terlambat' : 'hadir';
                    }

                    if ($status === 'terlambat') {
                        echo '<span class="badge bg-warning text-dark">Terlambat</span>';
                    } elseif ($status === 'hadir') {
                        echo '<span class="badge bg-success">Hadir</span>';
                    } elseif ($status === 'izin') {
                        echo '<span class="badge bg-info text-dark">Izin</span>';
                    } elseif ($status === 'sakit') {
                        echo '<span class="badge bg-primary">Sakit</span>';
                    } else {
                        echo '<span class="badge bg-secondary">' . e(ucfirst($status)) . '</span>';
                    }
                  ?>

                  <?php if (in_array($status, ['terlambat', 'alfa'], true) && !empty($r['potongan_gaji'])): ?>
                    <div class="text-danger small fw-semibold mt-1">
                      Pot. Gaji: Rp <?= number_format($r['potongan_gaji'], 0, ',', '.') ?>
                    </div>
                  <?php endif; ?>
                  <?php if (!empty($r['potongan_uang_makan'])): ?>
                    <div class="text-danger small fw-semibold mt-1">
                      <i class="fa-solid fa-utensils me-1"></i>Uang Makan Hangus: Rp <?= number_format($r['potongan_uang_makan'], 0, ',', '.') ?>
                    </div>
                  <?php endif; ?>
                </td>
                <td>
                  <?php 
                    // Ambil file dari foto_in atau bukti_sakit
                    $fileRow = $r['foto_in'] ?? $r['bukti_sakit'] ?? null;
                    $isIzinSakit = in_array($status, ['izin', 'sakit'], true);
                  ?>

                  <?php if (!empty($fileRow)): ?>
                    <?php if ($isIzinSakit): ?>
                      <?php $extRow = strtolower(pathinfo($fileRow, PATHINFO_EXTENSION)); ?>
                      <?php if ($extRow === 'pdf'): ?>
                        <a href="<?= UPLOAD_URL . 'absensi/' . e($fileRow) ?>" target="_blank" class="small">
                          <i class="fa-solid fa-file-pdf text-danger"></i> Lihat PDF
                        </a>
                      <?php else: ?>
                        <a href="<?= UPLOAD_URL . 'absensi/' . e($fileRow) ?>" target="_blank">
                          <img src="<?= UPLOAD_URL . 'absensi/' . e($fileRow) ?>" class="avatar-sm" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;" onerror="this.style.display='none';">
                        </a>
                      <?php endif; ?>
                    <?php else: ?>
                      <img src="<?= UPLOAD_URL . 'absensi/' . e($fileRow) ?>" class="avatar-sm" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;" onerror="this.style.display='none';">
                    <?php endif; ?>
                  <?php else: ?>
                    <span class="text-muted">-</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<div class="modal fade" id="modalIzinSakit" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="<?= url('absensi/izin') ?>" enctype="multipart/form-data" id="formIzinSakit">
        <div class="modal-header">
          <h5 class="modal-title">Ajukan Izin / Sakit</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Tanggal <span class="text-danger">*</span></label>
            <input type="text" class="form-control bg-light" value="<?= format_tanggal(date('Y-m-d')) ?>" readonly>
            <input type="hidden" name="tanggal" value="<?= date('Y-m-d') ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">Jenis <span class="text-danger">*</span></label>
            <select name="status" id="jenisIzinSakit" class="form-select" required>
              <option value="izin">Izin</option>
              <option value="sakit">Sakit</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Keterangan / Alasan <span class="text-danger">*</span></label>
            <textarea name="keterangan" class="form-control" rows="3" maxlength="500" required></textarea>
          </div>
          <div class="mb-1" id="grupBuktiSakit">
            <label class="form-label">Bukti Pendukung (Foto / PDF) <span id="labelBintangWajib" class="text-danger" style="display:none;">*</span></label>
            <input type="file" name="bukti_sakit" id="inputBuktiSakit" class="form-control" accept="image/*,.pdf" capture="environment">
            <div class="form-text" id="teksKeteranganFile">Format JPG/PNG/WEBP/PDF, maksimal 3MB. (Opsional untuk Izin, Wajib untuk Sakit).</div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-brand"><i class="fa-solid fa-paper-plane me-1"></i> Kirim Pengajuan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php
$extraScript = <<<'SCRIPT'
<script>
document.addEventListener('DOMContentLoaded', function () {
  var jamEl = document.getElementById('jamSekarang');
  if (jamEl) {
    setInterval(function () {
      jamEl.textContent = new Date().toLocaleTimeString('id-ID', { hour12: false });
    }, 1000);
  }

  var jenisIzinSakit = document.getElementById('jenisIzinSakit');
  var inputBuktiSakit = document.getElementById('inputBuktiSakit');
  var labelBintangWajib = document.getElementById('labelBintangWajib');
  var teksKeteranganFile = document.getElementById('teksKeteranganFile');

  function toggleBuktiSakit() {
    if (!jenisIzinSakit) return;
    var isSakit = jenisIzinSakit.value === 'sakit';
    
    if (inputBuktiSakit) {
      inputBuktiSakit.required = isSakit;
    }
    if (labelBintangWajib) {
      labelBintangWajib.style.display = isSakit ? 'inline' : 'none';
    }
    if (teksKeteranganFile) {
      teksKeteranganFile.textContent = isSakit 
        ? 'Format JPG/PNG/WEBP/PDF, Surat keterangan dokter wajib diunggah.' 
        : 'Format JPG/PNG/WEBP/PDF, opsional melampirkan berkas pendukung izin.';
    }
  }

  if (jenisIzinSakit) {
    jenisIzinSakit.addEventListener('change', toggleBuktiSakit);
    toggleBuktiSakit();
  }

  var video = document.getElementById('video');
  var canvas = document.getElementById('canvas');
  var cameraError = document.getElementById('cameraError');
  var btnIn = document.getElementById('btnCheckin');
  var btnOut = document.getElementById('btnCheckout');
  var formCheckin = document.getElementById('formCheckin');
  var toggleFoto = document.getElementById('toggleFoto');
  // Jam mulai shift sekarang datang dari jadwal yang diatur HR (lihat data-jam-mulai-shift
  // pada <form id="formCheckin">), bukan lagi dari pilihan manual karyawan.
  var jamMulaiShift = formCheckin ? formCheckin.getAttribute('data-jam-mulai-shift') : null;

  // Foto absen bersifat OPSIONAL: kalau toggle "Gunakan foto" dicentang, kamera
  // wajib siap dulu sebelum tombol aktif. Kalau toggle di-nonaktifkan (unchecked),
  // absen tetap bisa dilakukan tanpa menunggu/kirim foto sama sekali.
  function fotoDiperlukan() {
    return toggleFoto ? toggleFoto.checked : false;
  }

  function kameraSudahSiap() {
    return !fotoDiperlukan() || (video && video.srcObject !== null);
  }

  function hentikanKamera() {
    if (video && video.srcObject) {
      video.srcObject.getTracks().forEach(function (t) { t.stop(); });
      video.srcObject = null;
    }
  }

  function updateTeksTombol() {
    var pakaiFoto = fotoDiperlukan();
    if (btnIn) {
      btnIn.innerHTML = pakaiFoto
        ? '<i class="fa-solid fa-camera me-1"></i> Ambil Foto &amp; Check-in'
        : '<i class="fa-solid fa-right-to-bracket me-1"></i> Check-in Tanpa Foto';
    }
    if (btnOut) {
      btnOut.innerHTML = pakaiFoto
        ? '<i class="fa-solid fa-camera me-1"></i> Ambil Foto &amp; Check-out'
        : '<i class="fa-solid fa-right-from-bracket me-1"></i> Check-out Tanpa Foto';
    }
  }

  if (toggleFoto) {
    toggleFoto.addEventListener('change', function () {
      updateTeksTombol();
      if (toggleFoto.checked) {
        if (cameraError) cameraError.classList.add('d-none');
        initKamera();
      } else {
        hentikanKamera();
        if (cameraError) cameraError.classList.add('d-none');
      }
      updateCheckinLock();
      updateCheckoutLock();
    });
    updateTeksTombol();
  }

  function showError(msg) {
    if (cameraError) {
      cameraError.textContent = msg;
      cameraError.classList.remove('d-none');
    }
  }

  function updateCheckinLock() {
    if (!btnIn) return;
    var cameraReady = kameraSudahSiap();
    var lokasiSiap = typeof locationReady === 'undefined' ? true : locationReady;

    var isExpired = false;
    var warningMsg = "";

    if (jamMulaiShift) {
      var parts = jamMulaiShift.split(':');

      var now = new Date();
      var shiftTime = new Date();
      shiftTime.setHours(parseInt(parts[0], 10));
      shiftTime.setMinutes(parseInt(parts[1], 10));
      shiftTime.setSeconds(0);

      var batasWaktu = new Date(shiftTime.getTime() + (60 * 60 * 1000));

      if (now > batasWaktu) {
        isExpired = true;
        warningMsg = "Perhatian: Waktu check-in sudah lewat 1 jam dari jadwal shift. Anda tetap bisa check-in, namun status akan otomatis tercatat sebagai ALFA dan dikenakan potongan gaji 10%.";
      }
    }

    if (isExpired) {
      btnIn.disabled = !(cameraReady && lokasiSiap);
      showError(warningMsg);
    } else {
      if (cameraError && warningMsg === "") {
        cameraError.classList.add('d-none');
      }
      btnIn.disabled = !(cameraReady && lokasiSiap);
    }
  }

  function updateCheckoutLock() {
    if (!btnOut) return;
    btnOut.disabled = !kameraSudahSiap();
  }

  if (jamMulaiShift) {
    setInterval(updateCheckinLock, 1000);
  }

  // ==========================================================
  // AKSES KAMERA (Laptop/PC, Android, iOS/iPhone/iPad)
  // ==========================================================
  // Catatan penting: browser HANYA mengizinkan akses kamera & lokasi
  // pada "secure context", yaitu:
  //   - halaman yang diakses lewat HTTPS, ATAU
  //   - halaman yang diakses lewat http://localhost / http://127.0.0.1
  //     dari perangkat yang SAMA dengan server.
  // Kalau HRIS ini diakses dari HP (Android/iOS) memakai alamat IP
  // jaringan lokal seperti http://192.168.x.x/..., itu BUKAN secure
  // context, sehingga kamera akan otomatis diblokir oleh browser
  // (terutama di iOS Safari). Solusinya: akses lewat HTTPS
  // (aktifkan SSL di XAMPP/gunakan tool seperti ngrok/Cloudflare Tunnel),
  // atau install sertifikat lokal.
  function initKamera() {
    if (!video || !canvas) return;
    if (!fotoDiperlukan()) return; // toggle "gunakan foto" nonaktif, tidak perlu minta izin kamera

    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
      var isSecure = (window.isSecureContext === true) ||
                      location.protocol === 'https:' ||
                      location.hostname === 'localhost' ||
                      location.hostname === '127.0.0.1';
      if (!isSecure) {
        showError('Kamera diblokir browser karena halaman ini belum diakses lewat HTTPS. Buka halaman ini lewat alamat https:// (bukan http://IP-lokal) agar kamera bisa dipakai di HP maupun laptop, atau matikan opsi "Gunakan foto" di atas untuk tetap absen tanpa foto.');
      } else {
        showError('Browser ini tidak mendukung akses kamera (getUserMedia tidak tersedia). Gunakan versi terbaru Chrome/Safari/Edge, atau matikan opsi "Gunakan foto" di atas untuk tetap absen tanpa foto.');
      }
      updateCheckinLock();
      updateCheckoutLock();
      return;
    }

    function pasangStream(stream) {
      video.srcObject = stream;
      // Sebagian browser (terutama iOS Safari & beberapa Android) butuh
      // play() dipanggil eksplisit walau ada atribut autoplay.
      var playPromise = video.play();
      if (playPromise && typeof playPromise.catch === 'function') {
        playPromise.catch(function () {
          // Diamkan; video tetap akan tampil begitu user berinteraksi.
        });
      }
      if (cameraError) cameraError.classList.add('d-none');
      updateCheckinLock();
      updateCheckoutLock();
    }

    function tampilkanErrorAkses(err) {
      var pesan = 'Tidak dapat mengakses kamera. Pastikan browser mengizinkan akses kamera, atau matikan opsi "Gunakan foto" di atas untuk tetap absen tanpa foto.';
      var name = err && err.name ? err.name : '';
      if (name === 'NotAllowedError' || name === 'PermissionDeniedError') {
        pesan = 'Izin kamera ditolak. Buka pengaturan situs di browser Anda (ikon gembok di address bar), lalu izinkan akses Kamera untuk halaman ini, kemudian muat ulang. Atau, matikan opsi "Gunakan foto" di atas untuk tetap absen tanpa foto.';
      } else if (name === 'NotFoundError' || name === 'DevicesNotFoundError') {
        pesan = 'Kamera tidak ditemukan di perangkat ini. Pastikan perangkat memiliki kamera dan tidak sedang dipakai aplikasi lain, atau matikan opsi "Gunakan foto" di atas untuk tetap absen tanpa foto.';
      } else if (name === 'NotReadableError' || name === 'TrackStartError') {
        pesan = 'Kamera sedang dipakai aplikasi lain. Tutup aplikasi/tab lain yang memakai kamera, lalu muat ulang halaman. Atau matikan opsi "Gunakan foto" di atas untuk tetap absen tanpa foto.';
      } else if (name === 'OverconstrainedError' || name === 'ConstraintNotSatisfiedError') {
        pesan = 'Kamera perangkat tidak mendukung mode yang diminta. Mencoba kembali dengan pengaturan standar...';
      } else if (location.protocol !== 'https:' && location.hostname !== 'localhost' && location.hostname !== '127.0.0.1') {
        pesan = 'Kamera diblokir karena halaman belum diakses lewat HTTPS. Buka lewat alamat https:// agar kamera bisa dipakai di HP, atau matikan opsi "Gunakan foto" di atas untuk tetap absen tanpa foto.';
      }
      showError(pesan);
      updateCheckinLock();
      updateCheckoutLock();
    }

    // Coba kamera depan dulu (ideal untuk foto absen/selfie di HP),
    // dengan fallback bertingkat supaya tetap jalan di laptop/PC yang
    // webcam-nya tidak mendukung constraint facingMode.
    navigator.mediaDevices.getUserMedia({
      video: { facingMode: { ideal: 'user' } },
      audio: false
    })
      .then(pasangStream)
      .catch(function (err) {
        // Fallback 1: constraint sederhana tanpa facingMode (umum dipakai webcam laptop/PC)
        navigator.mediaDevices.getUserMedia({ video: true, audio: false })
          .then(pasangStream)
          .catch(function (err2) {
            tampilkanErrorAkses(err2 || err);
          });
      });
  }

  initKamera();
  updateCheckoutLock();

  function ambilFoto() {
    canvas.width = video.videoWidth || 320;
    canvas.height = video.videoHeight || 240;
    var ctx = canvas.getContext('2d');
    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
    return canvas.toDataURL('image/png');
  }

  var locationStatus = document.getElementById('locationStatus');
  var lokasiBadge = document.getElementById('lokasiBadge');
  var lokasiInInput = document.getElementById('lokasiInInput');
  var btnRetryLokasi = document.getElementById('btnRetryLokasi');
  var locationReady = false;
  var watchId = null;

  // ===== Peta real-time (Leaflet + OpenStreetMap) =====
  var mapEl = document.getElementById('mapLokasi');
  var peta = null;
  var markerPeta = null;
  var lingkaranAkurasi = null;

  function initPeta(lat, lng) {
    if (!mapEl || typeof L === 'undefined' || peta) return;
    peta = L.map(mapEl, { attributionControl: true, zoomControl: true }).setView([lat, lng], 17);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution: '&copy; OpenStreetMap contributors'
    }).addTo(peta);
    markerPeta = L.marker([lat, lng]).addTo(peta);
    lingkaranAkurasi = L.circle([lat, lng], { radius: 0, color: '#2563eb', fillColor: '#2563eb', fillOpacity: 0.12, weight: 1 }).addTo(peta);
  }

  function updatePetaPosisi(lat, lng, akurasi) {
    if (!mapEl || typeof L === 'undefined') return;
    if (!peta) {
      initPeta(lat, lng);
      return;
    }
    var latlng = [lat, lng];
    markerPeta.setLatLng(latlng);
    if (lingkaranAkurasi) {
      lingkaranAkurasi.setLatLng(latlng);
      lingkaranAkurasi.setRadius(akurasi || 0);
    }
    peta.panTo(latlng, { animate: true });
    // Ukuran container bisa berubah setelah render awal (mis. saat kartu baru terlihat)
    setTimeout(function () { peta.invalidateSize(); }, 100);
  }

  function ambilLokasi() {
    if (!navigator.geolocation || !locationStatus) {
      if (locationStatus) locationStatus.textContent = 'Browser tidak mendukung deteksi lokasi.';
      return;
    }

    // Wajib HTTPS (atau localhost) agar browser mengizinkan akses lokasi.
    var isSecure = window.isSecureContext || location.hostname === 'localhost' || location.hostname === '127.0.0.1';
    if (!isSecure) {
      locationStatus.innerHTML = '<i class="fa-solid fa-triangle-exclamation text-warning me-1"></i>Lokasi butuh koneksi HTTPS untuk aktif.';
      if (btnRetryLokasi) btnRetryLokasi.style.display = 'none';
      updateCheckinLock();
      return;
    }

    locationReady = false;
    locationStatus.innerHTML = '<span class="spinner-border spinner-border-sm text-primary me-1"></span>Mendeteksi lokasi...';
    if (lokasiBadge) {
      lokasiBadge.textContent = 'Mendeteksi...';
      lokasiBadge.className = 'badge bg-secondary';
      lokasiBadge.style.display = 'inline-block';
    }
    if (btnRetryLokasi) btnRetryLokasi.style.display = 'none';
    updateCheckinLock();

    if (watchId !== null) {
      navigator.geolocation.clearWatch(watchId);
      watchId = null;
    }

    function onSukses(pos) {
      var lat = pos.coords.latitude;
      var lng = pos.coords.longitude;
      var lokasi = lat.toFixed(6) + ', ' + lng.toFixed(6);
      locationStatus.innerHTML = '<i class="fa-solid fa-location-dot text-primary me-1"></i>GPS: ' + lokasi;
      if (lokasiBadge) {
        lokasiBadge.textContent = 'Live';
        lokasiBadge.className = 'badge bg-success';
        lokasiBadge.style.display = 'inline-block';
      }
      if (lokasiInInput) lokasiInInput.value = lokasi;
      locationReady = true;
      updateCheckinLock();
      updatePetaPosisi(lat, lng, pos.coords.accuracy);
    }

    function onGagal(err) {
      var pesan = 'Lokasi tidak dapat dideteksi.';
      if (err.code === err.PERMISSION_DENIED) {
        pesan = 'Akses lokasi ditolak. Izinkan lokasi di pengaturan browser lalu coba lagi.';
      } else if (err.code === err.POSITION_UNAVAILABLE) {
        pesan = 'Posisi tidak tersedia. Pastikan GPS/lokasi perangkat aktif.';
      } else if (err.code === err.TIMEOUT) {
        pesan = 'Waktu deteksi lokasi habis. Coba lagi.';
      }
      locationStatus.innerHTML = '<i class="fa-solid fa-triangle-exclamation text-warning me-1"></i>' + pesan;
      if (lokasiBadge) lokasiBadge.style.display = 'none';
      if (btnRetryLokasi) btnRetryLokasi.style.display = 'inline-block';
      locationReady = false;
      updateCheckinLock();
    }

    var opsiGeo = { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 };

    // Ambil posisi awal secepatnya, lalu pantau perubahan posisi secara terus-menerus (real-time)
    navigator.geolocation.getCurrentPosition(onSukses, onGagal, opsiGeo);
    watchId = navigator.geolocation.watchPosition(onSukses, onGagal, opsiGeo);
  }

  if (btnRetryLokasi) {
    btnRetryLokasi.addEventListener('click', ambilLokasi);
  }

  // Tampilkan peta real-time selama kartu "Lokasi" ada di halaman ini,
  // baik saat form check-in tersedia maupun belum (mis. belum dijadwalkan shift).
  if (mapEl) {
    ambilLokasi();
  }

  window.addEventListener('beforeunload', function () {
    if (watchId !== null) navigator.geolocation.clearWatch(watchId);
  });

  var formCheckin = document.getElementById('formCheckin');
  if (formCheckin) {
    formCheckin.addEventListener('submit', function (e) {
      e.preventDefault();
      var fotoInput = document.getElementById('fotoInInput');
      fotoInput.value = (fotoDiperlukan() && video && video.srcObject) ? ambilFoto() : '';
      formCheckin.submit();
    });
  }

  var formCheckoutEl = document.getElementById('formCheckout');
  if (formCheckoutEl) {
    formCheckoutEl.addEventListener('submit', function (e) {
      e.preventDefault();
      var fotoOutput = document.getElementById('fotoOutInput');
      fotoOutput.value = (fotoDiperlukan() && video && video.srcObject) ? ambilFoto() : '';
      formCheckoutEl.submit();
    });
  }
}); 
</script>
SCRIPT;
?>