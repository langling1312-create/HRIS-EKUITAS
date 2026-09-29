<?php

class ShiftController extends Controller
{
    public function index()
    {
        AuthMiddleware::handle();

        $shiftModel = $this->model('Shift');
        $jadwalModel = $this->model('JadwalShift');

        $daftarShift = $shiftModel->all('jam_mulai ASC');

        $mulai = $this->input('mulai', date('Y-m-d', strtotime('monday this week')));
        $selesai = date('Y-m-d', strtotime($mulai . ' +6 days'));

        if (AuthHelper::isHrOrAdmin()) {
            $keyword = trim($this->input('q', ''));
            $karyawanModel = $this->model('Karyawan');
            $daftarKaryawan = $karyawanModel->allAktif();
            $jadwal = $jadwalModel->mingguan($mulai, $selesai, $keyword);

            // Susun jadwal dalam bentuk grid: [user_id][tanggal] = data shift
            $grid = [];
            foreach ($jadwal as $j) {
                $grid[$j['user_id']][$j['tanggal']] = $j;
            }

            $daftarKaryawanDenganUser = $this->model('Karyawan')->allWithUser();

            $this->view('shift/index', [
                'title'          => 'Shift & Roster',
                'daftarShift'    => $daftarShift,
                'daftarKaryawan' => $daftarKaryawanDenganUser,
                'grid'           => $grid,
                'mulai'          => $mulai,
                'selesai'        => $selesai,
                'keyword'        => $keyword,
            ]);
            return;
        }

        $jadwalSaya = $jadwalModel->byUserMingguan(AuthHelper::id(), $mulai, $selesai);
        $this->view('shift/index', [
            'title'       => 'Jadwal Shift Saya',
            'daftarShift' => $daftarShift,
            'jadwalSaya'  => $jadwalSaya,
            'mulai'       => $mulai,
            'selesai'     => $selesai,
        ]);
    }

    public function simpanJadwal()
    {
        AuthMiddleware::role(['admin', 'hr']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('shift');
            return;
        }

        $jadwalModel = $this->model('JadwalShift');
        $logModel = $this->model('LogAktivitas');

        $userId = (int) $this->input('user_id');
        $tanggal = $this->input('tanggal');
        $shiftId = (int) $this->input('shift_id');

        if ($userId && $tanggal && $shiftId) {
            $jadwalModel->setJadwal($userId, $tanggal, $shiftId);
            $logModel->catat(AuthHelper::id(), "Mengatur jadwal shift karyawan #{$userId} pada {$tanggal}");
        }

        $this->json(['status' => 'ok']);
    }

    public function tambahShift()
    {
        AuthMiddleware::role(['admin', 'hr']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('shift');
            return;
        }

        $shiftModel = $this->model('Shift');
        $nama = ValidationHelper::clean($this->input('nama_shift', ''));
        $jamMulai = $this->input('jam_mulai');
        $jamSelesai = $this->input('jam_selesai');

        if ($nama && $jamMulai && $jamSelesai) {
            $shiftModel->insert(['nama_shift' => $nama, 'jam_mulai' => $jamMulai, 'jam_selesai' => $jamSelesai]);
            SessionHelper::flash('success', 'Jenis shift baru berhasil ditambahkan.');
        }

        $this->redirect('shift');
    }
}
