# Kenapa Kamera & Lokasi Diblokir di Android/iOS?

Ini **bukan bug di kode HRIS**. Chrome (Android) dan Safari (iOS) memang
sengaja memblokir akses kamera (`getUserMedia`) dan lokasi
(`geolocation`) untuk halaman yang diakses lewat `http://` biasa,
**kecuali** diakses dari `http://localhost` di perangkat yang sama
dengan server. Kalau HRIS ini dibuka dari HP lewat alamat IP jaringan
lokal seperti `http://10.31.101.80/...`, browser akan selalu
memblokirnya — tidak peduli seperti apa pun kode PHP/JavaScript-nya.

Solusinya cuma satu: **akses lewat `https://`**. Begitu protokolnya
`https://` — walaupun sertifikatnya self-signed (bukan dari
CA resmi) — browser tetap menganggapnya "secure context" dan kamera
langsung berfungsi normal, cukup klik "Lanjutkan" sekali di layar
peringatan sertifikat.

Folder `https-setup/` ini berisi semua yang kamu butuhkan untuk
mengaktifkan HTTPS di XAMPP kamu.

## Langkah-langkah

### 1. Cek alamat IP laptop/PC kamu saat ini
Buka Command Prompt, ketik:
```
ipconfig
```
Cari baris **IPv4 Address** (contoh: `10.31.101.80`). Catat angka ini.

### 2. Edit `openssl-san.cnf`
Buka file `openssl-san.cnf` di folder ini, ganti SEMUA kemunculan
`10.31.101.80` (ada di baris `CN =` dan `IP.2 =`) dengan IP hasil
langkah 1 tadi. Kalau IP laptop kamu sering berubah-ubah (DHCP),
pertimbangkan set IP statis di pengaturan jaringan Windows supaya
tidak perlu generate ulang sertifikat setiap kali.

### 3. Jalankan `generate-ssl-cert.bat`
Double-click file ini (atau klik kanan → Run as administrator).
Akan muncul 2 file baru di folder ini:
- `hris-selfsigned.crt`
- `hris-selfsigned.key`

Kalau muncul error "tidak menemukan openssl.exe", buka file
`generate-ssl-cert.bat` dengan Notepad dan sesuaikan baris
`set OPENSSL_EXE=...` dengan lokasi instalasi XAMPP kamu.

### 4. Pasang sertifikat ke Apache
1. Copy `hris-selfsigned.crt` ke `C:\xampp\apache\conf\ssl.crt\`
2. Copy `hris-selfsigned.key` ke `C:\xampp\apache\conf\ssl.key\`
   (buat folder `ssl.crt` dan `ssl.key` kalau belum ada)
3. Buka `C:\xampp\apache\conf\httpd.conf`, cari baris berikut dan
   hapus tanda `#` di depannya kalau masih ada tanda pagar
   (mengaktifkan modul SSL & vhost SSL):
   ```
   LoadModule ssl_module modules/mod_ssl.so
   Include conf/extra/httpd-ssl.conf
   ```
4. Buka `C:\xampp\apache\conf\extra\httpd-ssl.conf`, tempelkan isi
   file `httpd-vhosts-ssl-example.conf` (dari folder ini) di bagian
   paling bawah. Sesuaikan `ServerAlias` dengan IP kamu dari
   langkah 1.
5. Restart Apache lewat XAMPP Control Panel (Stop lalu Start lagi).

### 5. Akses dari laptop & HP
- Dari laptop: `https://localhost/`
- Dari HP (Android/iOS, satu WiFi yang sama dengan laptop):
  `https://10.31.101.80/` (ganti dengan IP kamu)

Pertama kali dibuka akan muncul peringatan "Your connection is not
private" / "Not Secure" — ini **normal** karena sertifikatnya
self-signed (buatan sendiri, bukan dari otoritas sertifikat
resmi seperti Let's Encrypt). Klik **Advanced/Lanjutan** →
**Proceed/Lanjutkan ke situs**. Setelah itu kamera & lokasi akan
langsung berfungsi normal di Android maupun iOS, sama seperti di
laptop.

## Kalau tidak mau ribet setup sendiri

Alternatif paling cepat tanpa install apa-apa di server: pakai
**ngrok**.
```
ngrok http 80
```
Nanti dapat URL seperti `https://xxxx.ngrok-free.app` yang otomatis
sudah HTTPS asli (tanpa peringatan sertifikat sama sekali) dan bisa
langsung dibuka dari HP mana pun, tanpa perlu setting Apache/XAMPP
sama sekali. Cocok untuk testing cepat; kalau untuk dipakai
sehari-hari oleh banyak karyawan, setup HTTPS permanen di atas (atau
deploy ke hosting dengan HTTPS asli) tetap yang paling disarankan.
