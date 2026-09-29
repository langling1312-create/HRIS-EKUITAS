-- ============================================================
-- Migrasi: Rincian Tunjangan pada Penggajian
-- Jalankan file ini di phpMyAdmin (tab SQL) pada database
-- yang SUDAH ada (hris_mvc) agar HR bisa mengatur tunjangan
-- per komponen (Jabatan, Transport, BPJS, Sakit, Lainnya) dan
-- mengedit gaji karyawan yang sudah diproses.
--
-- Aman dijalankan walau kolom belum ada karena memakai
-- IF NOT EXISTS-style guard via prosedur di bawah (MySQL 8+/MariaDB 10.x).
-- Jika error "Duplicate column", berarti kolom sudah pernah
-- ditambahkan sebelumnya dan boleh diabaikan.
-- ============================================================

ALTER TABLE payroll
  ADD COLUMN tunjangan_jabatan DECIMAL(15,2) DEFAULT 0.00 AFTER tunjangan,
  ADD COLUMN tunjangan_transport DECIMAL(15,2) DEFAULT 0.00 AFTER tunjangan_jabatan,
  ADD COLUMN tunjangan_bpjs DECIMAL(15,2) DEFAULT 0.00 AFTER tunjangan_transport,
  ADD COLUMN tunjangan_sakit DECIMAL(15,2) DEFAULT 0.00 AFTER tunjangan_bpjs,
  ADD COLUMN tunjangan_lainnya DECIMAL(15,2) DEFAULT 0.00 AFTER tunjangan_sakit;
