-- ================================================
-- MIGRASI: Perbaikan kolom Absensi yang hilang
-- (penyebab error "Unknown column 'a.potongan_gaji'"
--  saat membuka Dashboard / Penggajian)
--
-- Kode aplikasi (AbsensiController, model Absensi, model Payroll)
-- sudah lama memakai kolom shift, potongan_gaji, potongan_uang_makan,
-- dan status 'terlambat' pada tabel absensi, tapi kolom-kolom ini
-- belum pernah dibuat di database. Jalankan skrip ini SATU KALI di
-- phpMyAdmin (tab SQL) pada database hris_mvc yang sudah ada.
--
-- Untuk instalasi baru, cukup import database/hris_mvc.sql karena
-- semua kolom ini sudah termasuk di sana.
-- ================================================

USE hris_mvc;

ALTER TABLE absensi
    ADD COLUMN shift VARCHAR(50) NULL AFTER tanggal,
    ADD COLUMN potongan_gaji DECIMAL(15,2) DEFAULT 0.00 AFTER status,
    ADD COLUMN potongan_uang_makan DECIMAL(15,2) DEFAULT 0.00 AFTER potongan_gaji;

-- Tambahkan status 'terlambat' ke daftar status yang diizinkan.
ALTER TABLE absensi
    MODIFY COLUMN status ENUM('hadir', 'terlambat', 'izin', 'sakit', 'alfa') DEFAULT 'hadir';
