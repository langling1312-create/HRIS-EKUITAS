<?php

class HomeController extends Controller
{
    public function index()
    {
        if (AuthHelper::check()) {
            $this->redirect('dashboard');
        }
        $this->view('home/index', [
            'title' => 'Beranda',
        ], false);
    }
}
