-- ================================================
-- HRIS - Human Resource Information System
-- Database: hris_mvc
-- Sesuai rancangan pada dokumen Analisis HRIS
-- ================================================

CREATE DATABASE IF NOT EXISTS hris_mvc CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE hris_mvc;

-- ================================================
-- TABLE STRUCTURES
-- ================================================

-- 1. Tabel users (Autentikasi & Role)
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'hr', 'karyawan', 'kepala_unit', 'pimpinan_unit') DEFAULT 'karyawan',
    foto VARCHAR(255) NULL,
    no_hp VARCHAR(20) NULL,
    alamat TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. Tabel departemen
CREATE TABLE departemen (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama VARCHAR(100) NOT NULL,
    kepala_dept VARCHAR(100) NULL,
    kepala_unit_id INT NULL,
    pimpinan_unit_id INT NULL,
    FOREIGN KEY (kepala_unit_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (pimpinan_unit_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- 3. Tabel karyawan (Data Tambahan Karyawan)
CREATE TABLE karyawan (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT UNIQUE NOT NULL,
    nip VARCHAR(50) UNIQUE NOT NULL,
    jabatan VARCHAR(100) NOT NULL,
    departemen_id INT NULL,
    tgl_bergabung DATE NULL,
    kontrak_file VARCHAR(255) NULL,
    status_aktif BOOLEAN DEFAULT TRUE,
    gaji_pokok DECIMAL(15,2) DEFAULT 0.00,
    jenis_kelamin ENUM('L', 'P') NULL,
    tempat_lahir VARCHAR(100) NULL,
    tanggal_lahir DATE NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (departemen_id) REFERENCES departemen(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- 4. Tabel absensi
CREATE TABLE absensi (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    tanggal DATE NOT NULL,
    shift VARCHAR(50) NULL,
    check_in TIME NULL,
    check_out TIME NULL,
    foto_in VARCHAR(255) NULL,
    foto_out VARCHAR(255) NULL,
    lokasi VARCHAR(255) NULL,
    status ENUM('hadir', 'terlambat', 'izin', 'sakit', 'alfa') DEFAULT 'hadir',
    potongan_gaji DECIMAL(15,2) DEFAULT 0.00,
    potongan_uang_makan DECIMAL(15,2) DEFAULT 0.00,
    keterangan TEXT NULL,
    bukti_sakit VARCHAR(255) NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 5. Tabel cuti
CREATE TABLE cuti (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    jenis_cuti ENUM('tahunan', 'sakit', 'penting', 'spesial', 'khusus') DEFAULT 'tahunan',
    tanggal_mulai DATE NOT NULL,
    tanggal_selesai DATE NOT NULL,
    alasan TEXT NULL,
    -- Nama file surat keterangan sakit (PDF/JPG/PNG) yang diunggah karyawan,
    -- wajib diisi untuk jenis_cuti = 'sakit'. Disimpan di uploads/surat_sakit/.
    surat_sakit VARCHAR(255) NULL,
    status ENUM('pending', 'disetujui', 'ditolak') DEFAULT 'pending',
    -- Alur berjenjang: Kepala Unit -> Pimpinan Unit -> HRD
    -- kepala_unit_id / pimpinan_unit_id adalah "snapshot" pejabat unit karyawan
    -- pada saat pengajuan dibuat (diambil dari tabel departemen).
    kepala_unit_id INT NULL,
    pimpinan_unit_id INT NULL,
    status_kepala_unit ENUM('dilewati', 'pending', 'disetujui', 'ditolak') DEFAULT 'pending',
    status_pimpinan_unit ENUM('dilewati', 'pending', 'disetujui', 'ditolak') DEFAULT 'pending',
    status_hrd ENUM('pending', 'disetujui', 'ditolak') DEFAULT 'pending',
    catatan_kepala_unit TEXT NULL,
    catatan_pimpinan_unit TEXT NULL,
    catatan_hrd TEXT NULL,
    tanggal_kepala_unit DATETIME NULL,
    tanggal_pimpinan_unit DATETIME NULL,
    tanggal_hrd DATETIME NULL,
    -- tahap_sekarang menandai giliran approval saat ini
    tahap_sekarang ENUM('kepala_unit', 'pimpinan_unit', 'hrd', 'selesai') DEFAULT 'kepala_unit',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (kepala_unit_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (pimpinan_unit_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- 6. Tabel sisa_cuti (Tahunan)
CREATE TABLE sisa_cuti (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    tahun INT NOT NULL,
    sisa_hari INT DEFAULT 12,
    UNIQUE KEY unique_user_tahun (user_id, tahun),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 7. Tabel payroll
CREATE TABLE payroll (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    bulan INT NOT NULL,
    tahun INT NOT NULL,
    gaji_pokok DECIMAL(15,2) DEFAULT 0.00,
    tunjangan DECIMAL(15,2) DEFAULT 0.00,
    tunjangan_jabatan DECIMAL(15,2) DEFAULT 0.00,
    tunjangan_transport DECIMAL(15,2) DEFAULT 0.00,
    tunjangan_bpjs DECIMAL(15,2) DEFAULT 0.00,
    tunjangan_sakit DECIMAL(15,2) DEFAULT 0.00,
    tunjangan_lainnya DECIMAL(15,2) DEFAULT 0.00,
    potongan DECIMAL(15,2) DEFAULT 0.00,
    bpjs_kesehatan DECIMAL(15,2) DEFAULT 0.00,
    bpjs_ketenagakerjaan DECIMAL(15,2) DEFAULT 0.00,
    pph21 DECIMAL(15,2) DEFAULT 0.00,
    potongan_kasbon DECIMAL(15,2) DEFAULT 0.00,
    total DECIMAL(15,2) DEFAULT 0.00,
    slip_pdf VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 8. Tabel notifikasi
CREATE TABLE notifikasi (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    pesan TEXT NOT NULL,
    dibaca BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 9. Tabel log_aktivitas (Audit Trail)
CREATE TABLE log_aktivitas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NULL,
    aktivitas TEXT NOT NULL,
    ip_address VARCHAR(45) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- 10. Tabel pengaturan_sistem (Konfigurasi Aplikasi)
CREATE TABLE pengaturan_sistem (
    id INT PRIMARY KEY AUTO_INCREMENT,
    key_setting VARCHAR(100) UNIQUE NOT NULL,
    value_setting TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 11. Tabel role_permission (Manajemen Akses Role)
CREATE TABLE role_permission (
    id INT PRIMARY KEY AUTO_INCREMENT,
    role ENUM('admin', 'hr', 'karyawan', 'kepala_unit', 'pimpinan_unit') NOT NULL,
    menu VARCHAR(100) NOT NULL,
    can_access BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_role_menu (role, menu)
) ENGINE=InnoDB;

-- ================================================
-- MODUL TAMBAHAN (FASE 2)
-- ================================================

-- 12. Reimbursement & Klaim Keuangan
CREATE TABLE reimbursement (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    jenis VARCHAR(100) NOT NULL,
    jumlah DECIMAL(15,2) NOT NULL,
    deskripsi TEXT NULL,
    bukti_file VARCHAR(255) NULL,
    status ENUM('pending','disetujui','ditolak') DEFAULT 'pending',
    catatan_approval TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 13. Shift & Roster Management
CREATE TABLE shift (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama_shift VARCHAR(50) NOT NULL,
    jam_mulai TIME NOT NULL,
    jam_selesai TIME NOT NULL
) ENGINE=InnoDB;

CREATE TABLE jadwal_shift (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    shift_id INT NOT NULL,
    tanggal DATE NOT NULL,
    UNIQUE KEY unique_user_tanggal (user_id, tanggal),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (shift_id) REFERENCES shift(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 14. Loan & Cash Advance (Kasbon)
CREATE TABLE kasbon (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    jumlah DECIMAL(15,2) NOT NULL,
    alasan TEXT NULL,
    tenor_bulan INT DEFAULT 1,
    cicilan_per_bulan DECIMAL(15,2) DEFAULT 0,
    sisa_cicilan INT DEFAULT 0,
    status ENUM('pending','disetujui','ditolak','lunas') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 15. Asset Management (Inventaris Kantor)
CREATE TABLE aset (
    id INT PRIMARY KEY AUTO_INCREMENT,
    kode_aset VARCHAR(50) UNIQUE NOT NULL,
    nama_aset VARCHAR(150) NOT NULL,
    kategori VARCHAR(100) NULL,
    status ENUM('tersedia','dipinjam','rusak','perbaikan') DEFAULT 'tersedia',
    user_id INT NULL,
    tanggal_pinjam DATE NULL,
    tanggal_kembali DATE NULL,
    keterangan TEXT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- 16. Exit Interview & Offboarding
CREATE TABLE resign (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    tanggal_pengajuan DATE NOT NULL,
    tanggal_efektif DATE NOT NULL,
    alasan TEXT NULL,
    exit_interview TEXT NULL,
    status ENUM('pending','diproses','selesai','ditolak') DEFAULT 'pending',
    catatan_hrd TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 17. Performance Management (KPI)
CREATE TABLE kpi (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    periode VARCHAR(20) NOT NULL,
    deskripsi_target TEXT NOT NULL,
    target_value VARCHAR(100) NULL,
    nilai INT NULL,
    status ENUM('berjalan','dinilai') DEFAULT 'berjalan',
    catatan_atasan TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 18. Recruitment & Applicant Tracking System (ATS)
CREATE TABLE lowongan (
    id INT PRIMARY KEY AUTO_INCREMENT,
    judul VARCHAR(150) NOT NULL,
    departemen_id INT NULL,
    deskripsi TEXT NULL,
    status ENUM('buka','tutup') DEFAULT 'buka',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (departemen_id) REFERENCES departemen(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE pelamar (
    id INT PRIMARY KEY AUTO_INCREMENT,
    lowongan_id INT NOT NULL,
    nama VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL,
    no_hp VARCHAR(20) NULL,
    cv_file VARCHAR(255) NULL,
    status ENUM('baru','interview','diterima','ditolak') DEFAULT 'baru',
    catatan TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (lowongan_id) REFERENCES lowongan(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 19. Training & Development (LMS)
CREATE TABLE pelatihan (
    id INT PRIMARY KEY AUTO_INCREMENT,
    judul VARCHAR(150) NOT NULL,
    deskripsi TEXT NULL,
    tanggal_mulai DATE NULL,
    tanggal_selesai DATE NULL,
    materi_file VARCHAR(255) NULL,
    dibuat_oleh INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (dibuat_oleh) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE peserta_pelatihan (
    id INT PRIMARY KEY AUTO_INCREMENT,
    pelatihan_id INT NOT NULL,
    user_id INT NOT NULL,
    status ENUM('terdaftar','selesai') DEFAULT 'terdaftar',
    keterangan TEXT NULL,
    UNIQUE KEY unique_pelatihan_user (pelatihan_id, user_id),
    FOREIGN KEY (pelatihan_id) REFERENCES pelatihan(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 20. Company Announcement & Polls
CREATE TABLE pengumuman (
    id INT PRIMARY KEY AUTO_INCREMENT,
    judul VARCHAR(150) NOT NULL,
    isi TEXT NOT NULL,
    dibuat_oleh INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (dibuat_oleh) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE polling (
    id INT PRIMARY KEY AUTO_INCREMENT,
    pertanyaan VARCHAR(255) NOT NULL,
    status ENUM('aktif','ditutup') DEFAULT 'aktif',
    dibuat_oleh INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (dibuat_oleh) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE polling_opsi (
    id INT PRIMARY KEY AUTO_INCREMENT,
    polling_id INT NOT NULL,
    opsi_text VARCHAR(150) NOT NULL,
    FOREIGN KEY (polling_id) REFERENCES polling(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE polling_vote (
    id INT PRIMARY KEY AUTO_INCREMENT,
    polling_id INT NOT NULL,
    opsi_id INT NOT NULL,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_poll_user (polling_id, user_id),
    FOREIGN KEY (polling_id) REFERENCES polling(id) ON DELETE CASCADE,
    FOREIGN KEY (opsi_id) REFERENCES polling_opsi(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ================================================
-- INITIAL SEED DATA
-- ================================================

-- Insert Data Default: Pengaturan Sistem
INSERT INTO pengaturan_sistem (key_setting, value_setting) VALUES
('nama_perusahaan', 'PT HRIS Indonesia'),
('logo', 'logo.png'),
('tahun_anggaran', '2026');

-- Insert Data Default: Role Permission
INSERT INTO role_permission (role, menu, can_access) VALUES
('admin', 'dashboard', TRUE),
('admin', 'karyawan', TRUE),
('admin', 'absensi', TRUE),
('admin', 'cuti', TRUE),
('admin', 'payroll', TRUE),
('admin', 'laporan', TRUE),
('admin', 'pengaturan', TRUE),
('admin', 'log', TRUE),
('hr', 'dashboard', TRUE),
('hr', 'karyawan', TRUE),
('hr', 'absensi', TRUE),
('hr', 'cuti', TRUE),
('hr', 'payroll', TRUE),
('hr', 'laporan', TRUE),
('hr', 'pengaturan', FALSE),
('hr', 'log', TRUE),
('karyawan', 'dashboard', TRUE),
('karyawan', 'karyawan', FALSE),
('karyawan', 'absensi', TRUE),
('karyawan', 'cuti', TRUE),
('karyawan', 'payroll', TRUE),
('karyawan', 'laporan', FALSE),
('karyawan', 'pengaturan', FALSE),
('karyawan', 'log', FALSE);

-- Hak akses default untuk role Kepala Unit & Pimpinan Unit (sama seperti karyawan,
-- ditambah akses approval cuti bawahannya yang ditangani otomatis lewat penunjukan
-- di menu Unit Kerja, bukan lewat tabel ini).
INSERT INTO role_permission (role, menu, can_access) VALUES
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

-- Insert Data Default Departemen
INSERT INTO departemen (nama, kepala_dept) VALUES
('Human Resources', 'HR Manager'),
('Information Technology', '-'),
('Finance', '-'),
('Marketing', '-');

-- Insert Data Default: User (Password bawaan: 123456)
INSERT INTO users (name, email, password_hash, role) VALUES
('Super Admin', 'admin@hris.com', '$2b$10$JmUVkMLsNTtm7IhP4tnqoOSOZQWPwQWw9mheo/jBvJpjmLXQWMV7.', 'admin'),
('HR Manager', 'hr@hris.com', '$2b$10$JmUVkMLsNTtm7IhP4tnqoOSOZQWPwQWw9mheo/jBvJpjmLXQWMV7.', 'hr'),
('Karyawan 1', 'karyawan@hris.com', '$2b$10$JmUVkMLsNTtm7IhP4tnqoOSOZQWPwQWw9mheo/jBvJpjmLXQWMV7.', 'karyawan'),
('Kepala Unit IT', 'kepalaunit@hris.com', '$2b$10$JmUVkMLsNTtm7IhP4tnqoOSOZQWPwQWw9mheo/jBvJpjmLXQWMV7.', 'kepala_unit'),
('Pimpinan Unit IT', 'pimpinanunit@hris.com', '$2b$10$JmUVkMLsNTtm7IhP4tnqoOSOZQWPwQWw9mheo/jBvJpjmLXQWMV7.', 'pimpinan_unit');

-- Data tambahan karyawan untuk user 'Karyawan 1', 'Kepala Unit IT', 'Pimpinan Unit IT'
INSERT INTO karyawan (user_id, nip, jabatan, departemen_id, tgl_bergabung, status_aktif, gaji_pokok, jenis_kelamin) VALUES
(3, 'EMP-0001', 'Staff IT', 2, CURDATE(), TRUE, 5000000.00, 'L'),
(4, 'EMP-0002', 'Kepala Unit IT', 2, CURDATE(), TRUE, 8000000.00, 'L'),
(5, 'EMP-0003', 'Pimpinan Unit IT', 2, CURDATE(), TRUE, 10000000.00, 'P');

-- Langsung tunjuk kedua akun di atas sebagai pejabat approval unit "Information Technology"
-- (id=2) supaya alur cuti berjenjang bisa langsung dicoba tanpa setting manual dulu.
UPDATE departemen SET kepala_unit_id = 4, pimpinan_unit_id = 5 WHERE id = 2;

-- Inisialisasi sisa cuti tahun berjalan untuk karyawan
INSERT INTO sisa_cuti (user_id, tahun, sisa_hari) VALUES
(3, YEAR(CURDATE()), 12),
(4, YEAR(CURDATE()), 12),
(5, YEAR(CURDATE()), 12);

-- Pengaturan default persentase BPJS & PPh21 (untuk modul payroll terintegrasi)
INSERT INTO pengaturan_sistem (key_setting, value_setting) VALUES
('persen_bpjs_kesehatan', '1'),
('persen_bpjs_jht', '2'),
('persen_pph21', '5');

-- Data awal Shift
INSERT INTO shift (nama_shift, jam_mulai, jam_selesai) VALUES
('Shift Pagi', '08:00:00', '16:00:00'),
('Shift Siang', '13:00:00', '21:00:00'),
('Shift Malam', '21:00:00', '05:00:00');

-- Catatan: password default untuk ketiga akun di atas adalah 123456
