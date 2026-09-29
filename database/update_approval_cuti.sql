-- ================================================
-- MIGRASI: Alur Persetujuan Cuti Berjenjang
-- (Kepala Unit -> Pimpinan Unit -> HRD)
--
-- Jalankan skrip ini SATU KALI pada database hris_mvc
-- yang sudah ada (yang dibuat sebelum update ini).
-- Untuk instalasi baru, cukup import database/hris_mvc.sql
-- karena kolom-kolom ini sudah termasuk di dalamnya.
-- ================================================

USE hris_mvc;

-- 1. Tambah kolom penunjukan Kepala Unit & Pimpinan Unit per departemen.
--    Boleh diisi salah satu, keduanya, atau dikosongkan (NULL = tahap dilewati).
ALTER TABLE departemen
    ADD COLUMN kepala_unit_id INT NULL AFTER kepala_dept,
    ADD COLUMN pimpinan_unit_id INT NULL AFTER kepala_unit_id,
    ADD CONSTRAINT fk_departemen_kepala_unit FOREIGN KEY (kepala_unit_id) REFERENCES users(id) ON DELETE SET NULL,
    ADD CONSTRAINT fk_departemen_pimpinan_unit FOREIGN KEY (pimpinan_unit_id) REFERENCES users(id) ON DELETE SET NULL;

-- 2. Tambah kolom alur berjenjang pada tabel cuti.
ALTER TABLE cuti
    ADD COLUMN kepala_unit_id INT NULL AFTER status,
    ADD COLUMN pimpinan_unit_id INT NULL AFTER kepala_unit_id,
    ADD COLUMN status_kepala_unit ENUM('dilewati', 'pending', 'disetujui', 'ditolak') DEFAULT 'pending' AFTER pimpinan_unit_id,
    ADD COLUMN status_pimpinan_unit ENUM('dilewati', 'pending', 'disetujui', 'ditolak') DEFAULT 'pending' AFTER status_kepala_unit,
    ADD COLUMN status_hrd ENUM('pending', 'disetujui', 'ditolak') DEFAULT 'pending' AFTER status_pimpinan_unit,
    ADD COLUMN catatan_kepala_unit TEXT NULL AFTER status_hrd,
    ADD COLUMN catatan_pimpinan_unit TEXT NULL AFTER catatan_kepala_unit,
    ADD COLUMN catatan_hrd TEXT NULL AFTER catatan_pimpinan_unit,
    ADD COLUMN tanggal_kepala_unit DATETIME NULL AFTER catatan_hrd,
    ADD COLUMN tanggal_pimpinan_unit DATETIME NULL AFTER tanggal_kepala_unit,
    ADD COLUMN tanggal_hrd DATETIME NULL AFTER tanggal_pimpinan_unit,
    ADD COLUMN tahap_sekarang ENUM('kepala_unit', 'pimpinan_unit', 'hrd', 'selesai') DEFAULT 'kepala_unit' AFTER tanggal_hrd,
    ADD CONSTRAINT fk_cuti_kepala_unit FOREIGN KEY (kepala_unit_id) REFERENCES users(id) ON DELETE SET NULL,
    ADD CONSTRAINT fk_cuti_pimpinan_unit FOREIGN KEY (pimpinan_unit_id) REFERENCES users(id) ON DELETE SET NULL;

-- 3. Sinkronkan data cuti lama (yang sudah ada sebelum migrasi) agar tetap konsisten:
--    - yang sudah disetujui/ditolak -> anggap sudah selesai semua tahap.
--    - yang masih pending -> langsung diarahkan ke tahap HRD (karena saat itu
--      pengajuan belum melalui Kepala Unit / Pimpinan Unit).
UPDATE cuti SET
    status_kepala_unit = 'dilewati',
    status_pimpinan_unit = 'dilewati',
    status_hrd = CASE WHEN status = 'pending' THEN 'pending' ELSE status END,
    tahap_sekarang = CASE WHEN status = 'pending' THEN 'hrd' ELSE 'selesai' END
WHERE tahap_sekarang IS NULL OR tahap_sekarang = 'kepala_unit';
