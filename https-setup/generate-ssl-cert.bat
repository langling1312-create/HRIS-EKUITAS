@echo off
REM ===========================================================
REM  Generator sertifikat SSL self-signed untuk XAMPP (Windows)
REM  Tujuan: supaya HRIS ini bisa diakses lewat https:// baik
REM  dari laptop/PC maupun dari HP (Android & iOS) di jaringan
REM  yang sama, sehingga kamera & lokasi tidak lagi diblokir
REM  browser.
REM
REM  CARA PAKAI:
REM  1. Edit file openssl-san.cnf di folder ini dulu:
REM     ganti "10.31.101.80" (baris CN dan IP.2) dengan alamat
REM     IP lokal laptop/PC kamu saat ini (cek lewat "ipconfig").
REM  2. Jalankan file ini dengan cara: klik kanan -> Run as
REM     administrator (atau cukup double-click).
REM  3. Sertifikat (hris-selfsigned.crt) dan private key
REM     (hris-selfsigned.key) akan dibuat di folder ini.
REM  4. Ikuti langkah selanjutnya di README.md untuk memasang
REM     sertifikat ini ke Apache XAMPP.
REM ===========================================================

set OPENSSL_EXE=C:\xampp\apache\bin\openssl.exe

if not exist "%OPENSSL_EXE%" (
    echo [ERROR] Tidak menemukan openssl.exe di %OPENSSL_EXE%
    echo Sesuaikan path XAMPP kamu di baris "set OPENSSL_EXE=" pada file ini
    echo jika lokasi instalasi XAMPP kamu berbeda.
    pause
    exit /b 1
)

echo Membuat sertifikat self-signed berlaku 825 hari...
"%OPENSSL_EXE%" req -x509 -nodes -days 825 -newkey rsa:2048 ^
  -keyout hris-selfsigned.key ^
  -out hris-selfsigned.crt ^
  -config openssl-san.cnf ^
  -extensions v3_req

if errorlevel 1 (
    echo [ERROR] Gagal membuat sertifikat. Cek pesan error di atas.
    pause
    exit /b 1
)

echo.
echo ============================================================
echo  BERHASIL! File yang dibuat di folder ini:
echo    - hris-selfsigned.crt  (sertifikat)
echo    - hris-selfsigned.key  (private key)
echo.
echo  Lanjutkan ke README.md untuk memasangnya ke Apache XAMPP.
echo ============================================================
pause
