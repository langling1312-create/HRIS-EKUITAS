-- ================================================
-- MIGRASI: Kepala Unit & Pimpinan Unit sebagai Role Akun
--          + Upload Surat Sakit pada Absensi
--
-- Jalankan skrip ini SATU KALI pada database hris_mvc yang sudah ada
-- (yang dibuat sebelum update ini, TERMASUK yang sudah menjalankan
-- update_approval_cuti.sql sebelumnya). Untuk instalasi baru, cukup
-- import database/hris_mvc.sql karena semuanya sudah termasuk di sana.
-- ================================================

USE hris_mvc;

-- 1. Tambah role 'kepala_unit' dan 'pimpinan_unit' pada tabel users.
--    Kedua role ini adalah AKUN LOGIN TERSENDIRI (bukan sekadar penunjukan),
--    dipakai khusus untuk memproses persetujuan cuti bawahannya sesuai unit
--    yang ditentukan di menu "Unit Kerja".
ALTER TABLE users
    MODIFY COLUMN role ENUM('admin', 'hr', 'karyawan', 'kepala_unit', 'pimpinan_unit') DEFAULT 'karyawan';

-- 2. Perluas ENUM role pada tabel role_permission (kalau tabel ini sudah ada).
ALTER TABLE role_permission
    MODIFY COLUMN role ENUM('admin', 'hr', 'karyawan', 'kepala_unit', 'pimpinan_unit') NOT NULL;

INSERT IGNORE INTO role_permission (role, menu, can_access) VALUES
('kepala_unit', 'dashboard', TRUE),
('kepala_unit', 'karyawan', FALSE),
('kepala_unit', 'absensi', TRUE),
('kepala_unit', 'cuti', TRUE),
('kepala_unit', 'payroll', TRUE),
('kepala_unit', 'laporan', FALSE),
('kepala_unit', 'pengaturan', FALSE),
('kepala_unit', 'log', FALSE),
('pimpinan_unit', 'dashboard', TRUE),
('pimpinan_unit', 'karyawan', FALSE),
('pimpinan_unit', 'absensi', TRUE),
('pimpinan_unit', 'cuti', TRUE),
('pimpinan_unit', 'payroll', TRUE),
('pimpinan_unit', 'laporan', FALSE),
('pimpinan_unit', 'pengaturan', FALSE),
('pimpinan_unit', 'log', FALSE);

-- 2b. (Opsional) Buat 2 akun contoh Kepala Unit & Pimpinan Unit supaya bisa langsung
--     dicoba, sekaligus otomatis ditunjuk sebagai pejabat approval untuk departemen
--     pertama yang ada di database Anda. Password bawaan: 123456
--     Silakan lewati/hapus blok ini kalau Anda ingin membuat akunnya sendiri secara
--     manual lewat menu Karyawan.
INSERT INTO users (name, email, password_hash, role) VALUES
('Kepala Unit Contoh', 'kepalaunit@hris.com', '$2b$10$JmUVkMLsNTtm7IhP4tnqoOSOZQWPwQWw9mheo/jBvJpjmLXQWMV7.', 'kepala_unit'),
('Pimpinan Unit Contoh', 'pimpinanunit@hris.com', '$2b$10$JmUVkMLsNTtm7IhP4tnqoOSOZQWPwQWw9mheo/jBvJpjmLXQWMV7.', 'pimpinan_unit');

-- Tunjuk otomatis ke departemen pertama (ganti id departemen di WHERE sesuai kebutuhan Anda).
UPDATE departemen d
JOIN (SELECT id FROM departemen ORDER BY id ASC LIMIT 1) target ON d.id = target.id
JOIN (SELECT id FROM users WHERE email = 'kepalaunit@hris.com') ku ON 1=1
JOIN (SELECT id FROM users WHERE email = 'pimpinanunit@hris.com') pu ON 1=1
SET d.kepala_unit_id = ku.id, d.pimpinan_unit_id = pu.id;

-- 3. Tambah kolom untuk fitur "Izin/Sakit" pada Absensi: keterangan alasan
--    dan foto/scan surat keterangan sakit dari dokter.
ALTER TABLE absensi
    ADD COLUMN keterangan TEXT NULL AFTER status,
    ADD COLUMN bukti_sakit VARCHAR(255) NULL AFTER keterangan;

-- Catatan: setelah migrasi ini, gunakan menu "Unit Kerja" untuk menunjuk
-- akun ber-role Kepala Unit / Pimpinan Unit sebagai penanggung jawab
-- approval cuti di masing-masing unit. Akun dengan role tersebut dibuat
-- lewat menu "Karyawan" seperti biasa, cukup pilih Role = Kepala Unit /
-- Pimpinan Unit saat menambah data karyawan baru.
