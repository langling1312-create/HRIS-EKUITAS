<?php

class NotifikasiController extends Controller
{
    public function tandaiDibaca()
    {
        AuthMiddleware::handle();

        $notifikasiModel = $this->model('Notifikasi');
        $notifikasiModel->tandaiDibaca(AuthHelper::id());

        $this->json(['status' => 'ok']);
    }
}
