# HRIS App — PHP Native (MVC) untuk XAMPP

Sistem Informasi Manajemen SDM (HRIS) dibangun dengan **PHP native** (tanpa framework),
menggunakan pola arsitektur **MVC manual**, sesuai dokumen analisis kebutuhan.
Tampilan (UI) mengadopsi gaya **SB Admin** (sidebar + topnav + card dashboard) menggunakan
Bootstrap 5 + Font Awesome.

## Fitur / Halaman

### Modul Inti (13 halaman)
1. Landing Page
2. Register
3. Login
4. Dashboard (statistik + grafik sebaran departemen + notifikasi)
5. Profil Saya (edit data, upload foto, ganti password)
6. Absensi (check-in/out kamera web & lokasi GPS) — Karyawan
7. Rekap Absensi (harian & bulanan seluruh karyawan) — HR/Admin
8. Pengajuan Cuti (karyawan) & Persetujuan Cuti (HR/Admin)
9. Slip Gaji (karyawan) & Penggajian/proses gaji (HR/Admin) — otomatis hitung BPJS, PPh21, cicilan kasbon
10. Manajemen Karyawan (CRUD) — HR/Admin
11. Laporan & Analitik (grafik + export Excel/PDF) — HR/Admin
12. Pengaturan Role & Sistem (termasuk persentase BPJS/PPh21) — Admin
13. Log Aktivitas (audit trail) — Admin

### Modul Tambahan (Fase 2)
14. **Reimbursement & Klaim Keuangan** — karyawan ajukan klaim + upload bukti; HR/Finance approve/reject
15. **Shift & Roster Management** — HR atur jadwal shift mingguan per karyawan; karyawan lihat jadwal sendiri
16. **Loan & Cash Advance (Kasbon)** — karyawan ajukan kasbon; HR/Finance approve; cicilan otomatis terpotong tiap proses payroll
17. **Asset Management** — HR kelola inventaris (pinjam/kembalikan/rusak); karyawan lihat aset yang dipinjam
18. **Exit Interview & Offboarding** — karyawan ajukan resign + isi exit interview; HR proses hingga nonaktifkan akun
19. **Performance Management (KPI)** — HR/karyawan tetapkan target; HR beri nilai & catatan
20. **Recruitment & ATS** — HR kelola lowongan & pelamar; pelamar diterima otomatis jadi akun karyawan baru
21. **Training & Development (LMS)** — HR buat pelatihan + materi; karyawan daftar & lihat progres
22. **PPh 21 & BPJS Terintegrasi** — otomatis terhitung di setiap proses payroll berdasarkan persentase di Pengaturan
23. **Company Announcement & Polls** — HR buat pengumuman & polling; karyawan baca & vote


## Struktur Folder

```
hris-php-mvc/
├─ public/              -> Document root (arahkan XAMPP/virtual host ke sini)
│  ├─ index.php          -> Front controller
│  ├─ .htaccess          -> URL routing
│  ├─ assets/            -> css, js
│  └─ uploads/           -> foto profil, foto absensi, dsb
├─ app/
│  ├─ config/            -> database.php, config.php
│  ├─ core/              -> Router, Controller, Model, Database
│  ├─ helpers/           -> SessionHelper, AuthHelper, ValidationHelper, functions.php
│  ├─ middleware/        -> AuthMiddleware
│  ├─ controllers/       -> seluruh controller
│  ├─ models/            -> seluruh model
│  └─ views/              -> seluruh tampilan (layouts, per modul)
└─ database/
   └─ hris_mvc.sql       -> struktur + data awal database
```

## Cara Instalasi di XAMPP

1. Copy folder `hris-php-mvc` ke dalam `C:\xampp\htdocs\` (Windows) atau `/Applications/XAMPP/htdocs/` (Mac).
2. Jalankan **Apache** dan **MySQL** melalui XAMPP Control Panel.
3. Buka `http://localhost/phpmyadmin`, buat database baru bernama **hris_mvc**
   (atau langsung import, database akan otomatis dibuat oleh script SQL).
4. Import file `database/hris_mvc.sql` melalui tab **Import** di phpMyAdmin.
5. Jika perlu, sesuaikan kredensial database di `app/config/database.php`
   (default: host `127.0.0.1`, user `root`, password kosong — sudah sesuai default XAMPP).
6. Akses aplikasi melalui browser:
   ```
   http://localhost/hris-php-mvc/public/
   ```

> Catatan: Aplikasi mendeteksi Base URL secara otomatis, jadi tidak perlu diubah manual
> selama folder diakses melalui path di atas.

## Akun Demo

| Role     | Email             | Password |
|----------|-------------------|----------|
| Admin    | admin@hris.com    | 123456   |
| HR       | hr@hris.com       | 123456   |
| Karyawan | karyawan@hris.com | 123456   |
| Kepala Unit (unit IT) | kepalaunit@hris.com | 123456 |
| Pimpinan Unit (unit IT) | pimpinanunit@hris.com | 123456 |

## Catatan Penting - Update Database

Jika Anda meng-update dari versi sebelumnya, **wajib import ulang** `database/hris_mvc.sql`
(drop database `hris_mvc` lama di phpMyAdmin lalu import file baru), karena ada penambahan
14 tabel baru dan kolom baru di tabel `payroll` (BPJS, PPh21, potongan kasbon).

## Catatan Teknis

- **100% PHP native**, tidak menggunakan framework (Laravel/CodeIgniter, dll) maupun Composer.
- Untuk fitur **cetak Slip Gaji** dan **cetak Laporan**, sistem menyediakan halaman
  siap-cetak (tombol "Cetak / Simpan sebagai PDF") yang memanfaatkan fitur *Print to PDF*
  bawaan browser — sehingga tidak memerlukan library eksternal (Dompdf) yang butuh instalasi
  via Composer/internet.
- Untuk **Export Excel**, sistem menghasilkan file `.xls` berbasis tabel HTML yang otomatis
  terbuka di Microsoft Excel — juga tanpa perlu library eksternal (PhpSpreadsheet).
- Fitur **Absensi** menggunakan `navigator.mediaDevices.getUserMedia` (webcam) dan
  `navigator.geolocation` (lokasi GPS) bawaan browser. Browser akan meminta izin akses
  kamera & lokasi saat halaman Absensi dibuka — mohon diizinkan.
- Koneksi database menggunakan PDO dengan prepared statements (aman dari SQL Injection).
- Password disimpan ter-enkripsi menggunakan `password_hash()` (bcrypt).

## Alur Hak Akses (Role)

- **Admin**: akses penuh ke seluruh modul termasuk Pengaturan Role & Log Aktivitas.
- **HR**: kelola karyawan, approve cuti (tahap akhir), proses payroll, lihat laporan.
- **Kepala Unit**: role akun tersendiri. Absen & ajukan cuti sendiri seperti karyawan, plus
  menyetujui/menolak cuti bawahan di unit yang dipimpinnya (tahap pertama).
- **Pimpinan Unit**: role akun tersendiri. Sama seperti Kepala Unit, tapi memproses cuti
  setelah disetujui Kepala Unit (tahap kedua).
- **Karyawan**: absensi, ajukan cuti/izin/sakit, lihat slip gaji, edit profil sendiri.

## Update: Alur Persetujuan Cuti Berjenjang (Kepala Unit → Pimpinan Unit → HRD)

Pengajuan cuti karyawan melalui alur persetujuan berjenjang, dan **Kepala Unit / Pimpinan
Unit adalah akun login tersendiri** (role khusus di tabel `users`, bukan sekadar penunjukan):

1. **Kepala Unit** (login dengan akunnya sendiri) menyetujui/menolak terlebih dahulu.
2. Jika disetujui, lanjut ke **Pimpinan Unit** (login dengan akunnya sendiri).
3. Jika disetujui, lanjut ke **HRD/Admin** sebagai tahap akhir.

### Cara membuat & menunjuk Kepala Unit / Pimpinan Unit

1. Buka menu **Karyawan** (Admin/HR) → Tambah Karyawan (atau Edit karyawan yang sudah ada) →
   pilih **Role Akun = Kepala Unit** atau **Pimpinan Unit**. Akun ini punya email & password
   sendiri untuk login, sama seperti karyawan biasa.
2. Buka menu **Unit Kerja** (Admin/HR) → klik **Atur Pejabat** pada unit/departemen terkait →
   pilih akun ber-role Kepala Unit / Pimpinan Unit yang bertugas di unit tersebut.
3. Selesai. Saat karyawan di unit itu mengajukan cuti, sistem otomatis mengarahkan
   persetujuan ke akun yang ditunjuk, sesuai urutan Kepala Unit → Pimpinan Unit → HRD.

Poin penting:

- Kedua posisi **tetap boleh dikosongkan** di menu Unit Kerja. Jika kosong, tahap tersebut
  otomatis dilewati (unit tanpa Pimpinan Unit langsung dari Kepala Unit ke HRD; unit tanpa
  keduanya langsung ke HRD).
- Kepala Unit / Pimpinan Unit login dengan akun masing-masing dan akan melihat kartu
  **"Menunggu Persetujuan Anda"** di halaman Cuti saat ada bawahan yang mengajukan cuti dan
  gilirannya untuk memproses. Mereka tetap punya menu self-service sendiri (Absensi, Cuti,
  Slip Gaji, Jadwal Shift) karena tetap berstatus pegawai.
- Admin selalu bisa mengambil alih (override) tahap manapun sebagai jaring pengaman, misalnya
  saat akun Kepala Unit/Pimpinan Unit berhalangan.
- Halaman Cuti menampilkan progres tiap tahap (Kepala Unit → Pimpinan Unit → HRD) untuk
  setiap pengajuan.

## Update: Absensi Izin/Sakit dengan Upload Surat Dokter

Di halaman **Absensi**, karyawan (termasuk Kepala Unit/Pimpinan Unit) sekarang bisa menekan
tombol **"Ajukan Izin/Sakit"** untuk hari itu:

- Pilih jenis **Izin** atau **Sakit**, isi keterangan/alasan.
- Jika memilih **Sakit**, wajib melampirkan foto/scan surat keterangan dokter (format
  JPG/PNG/WEBP/PDF, maksimal 3MB) — bisa langsung foto dari kamera HP lewat input file.
- Bukti surat sakit tersimpan dan bisa dilihat kembali di riwayat absensi maupun oleh HR/Admin.

### Migrasi Database

- **Instalasi baru**: cukup import `database/hris_mvc.sql` seperti biasa, semua kolom &
  role baru sudah termasuk di dalamnya.
- **Database yang sudah pernah menjalankan update alur cuti sebelumnya** (sudah punya kolom
  `kepala_unit_id` dkk di tabel `cuti`/`departemen`): jalankan
  `database/update_role_dan_sakit.sql` satu kali untuk menambahkan role Kepala Unit/Pimpinan
  Unit dan kolom bukti sakit.
- **Database lama yang belum pernah dimigrasi sama sekali**: jalankan
  `database/update_approval_cuti.sql` terlebih dahulu, baru kemudian
  `database/update_role_dan_sakit.sql`.

Selamat menggunakan HRIS App!
