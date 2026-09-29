<?php
/**
 * Konfigurasi umum aplikasi HRIS
 */

// Deteksi otomatis base URL agar tidak perlu diubah manual di kebanyakan kasus
// Catatan: saat diakses lewat tunnel HTTPS seperti ngrok/Cloudflare Tunnel,
// koneksi ke Apache di lokal tetap berupa HTTP biasa (HTTPS "dilepas" di sisi
// tunnel), sehingga $_SERVER['HTTPS'] tidak akan otomatis terisi. Header
// X-Forwarded-Proto dikirim oleh tunnel/reverse-proxy tersebut untuk
// memberi tahu protokol asli yang dipakai pengunjung, jadi ikut dicek juga
// di sini supaya BASE_URL tetap benar (https://) dan kamera/lokasi tidak
// diblokir browser karena salah kira halamannya masih HTTP biasa.
$httpsAktif = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
$protocol = $httpsAktif ? 'https://' : 'http://';
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
$scriptDir = rtrim($scriptDir, '/');
define('BASE_URL', $protocol . $_SERVER['HTTP_HOST'] . $scriptDir . '/');

define('APP_NAME', 'HRIS - EKUITAS HRIS Indonesia');
define('UPLOAD_PATH', APP_ROOT . '/../public/uploads/');
define('UPLOAD_URL', BASE_URL . 'uploads/');

// Timezone
date_default_timezone_set('Asia/Jakarta');
