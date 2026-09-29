<?php

class LogController extends Controller
{
    public function index()
    {
        AuthMiddleware::role(['admin']);

        $logModel = $this->model('LogAktivitas');
        $daftarLog = $logModel->allWithUser(300);

        $totalLogHariIni = 0;
        $userAktif = [];
        foreach ($daftarLog as $l) {
            if (date('Y-m-d', strtotime($l['created_at'])) === date('Y-m-d')) {
                $totalLogHariIni++;
                if (!empty($l['user_id'])) {
                    $userAktif[$l['user_id']] = true;
                }
            }
        }

        $this->view('log/index', [
            'title'            => 'Log Aktivitas',
            'daftarLog'        => $daftarLog,
            'totalLogHariIni'  => $totalLogHariIni,
            'userAktifHariIni' => count($userAktif),
        ]);
    }
}
