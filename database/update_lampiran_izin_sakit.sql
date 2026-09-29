-- ================================================
-- MIGRASI: Lampiran Surat Sakit pada Pengajuan Cuti/Izin
--
-- Perbaikan untuk bug: form "Ajukan Cuti" sudah punya input upload
-- surat sakit dan CutiController sudah mencoba menyimpannya, tapi
-- kolom surat_sakit belum pernah dibuat di tabel cuti sehingga setiap
-- pengajuan cuti sakit GAGAL disimpan (SQL error kolom tidak ditemukan).
-- ENUM jenis_cuti juga belum mendukung nilai 'spesial' dan 'khusus'
-- yang sudah ada di dropdown form.
--
-- Jalankan skrip ini SATU KALI pada database hris_mvc yang sudah ada.
-- Untuk instalasi baru, cukup import database/hris_mvc.sql karena
-- perbaikan ini sudah termasuk di sana.
-- ================================================

USE hris_mvc;

-- 1. Tambah kolom surat_sakit (nama file bukti sakit/izin: PDF/JPG/PNG).
ALTER TABLE cuti
    ADD COLUMN surat_sakit VARCHAR(255) NULL AFTER alasan;

-- 2. Lengkapi ENUM jenis_cuti agar 'spesial' dan 'khusus' (sudah ada di
--    form pengajuan) tidak gagal disimpan.
ALTER TABLE cuti
    MODIFY COLUMN jenis_cuti ENUM('tahunan', 'sakit', 'penting', 'spesial', 'khusus') DEFAULT 'tahunan';
