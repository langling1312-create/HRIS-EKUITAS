<?php

class DashboardController extends Controller
{
    public function index()
    {
        AuthMiddleware::handle();

        $karyawanModel = $this->model('Karyawan');
        $absensiModel = $this->model('Absensi');
        $cutiModel = $this->model('Cuti');
        $payrollModel = $this->model('Payroll');
        $departemenModel = $this->model('Departemen');
        $notifikasiModel = $this->model('Notifikasi');

        $totalKaryawan = $karyawanModel->countAktif();
        $hadirHariIni = $absensiModel->hadirHariIni();
        $cutiPending = $cutiModel->countPending();
        $gajiBulanIni = $payrollModel->totalBulanIni((int) date('n'), (int) date('Y'));

        $sebaranDept = $departemenModel->jumlahKaryawanPerDept();
        $notifikasi = $notifikasiModel->terbaru(AuthHelper::id(), 5);

        $this->view('dashboard/index', [
            'title'          => 'Dashboard',
            'totalKaryawan'  => $totalKaryawan,
            'hadirHariIni'   => $hadirHariIni,
            'cutiPending'    => $cutiPending,
            'gajiBulanIni'   => $gajiBulanIni,
            'sebaranDept'    => $sebaranDept,
            'notifikasi'     => $notifikasi,
        ]);
    }

    public function bacaSemua()
    {
        AuthMiddleware::handle();
        $notifikasiModel = $this->model('Notifikasi');
        $notifikasiModel->tandaiDibaca(AuthHelper::id());
        exit;
    }
}