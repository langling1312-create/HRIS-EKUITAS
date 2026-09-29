<?php

class PengaturanController extends Controller
{
    private array $daftarMenu = [
        'dashboard', 'karyawan', 'absensi', 'cuti', 'payroll', 'laporan', 'pengaturan', 'log',
    ];

    public function index()
    {
        AuthMiddleware::role(['admin']);

        $rolePermissionModel = $this->model('RolePermission');
        $pengaturanModel = $this->model('PengaturanSistem');

        $roles = ['admin', 'hr', 'karyawan', 'kepala_unit', 'pimpinan_unit'];
        $permissions = [];
        foreach ($roles as $role) {
            $rows = $rolePermissionModel->byRole($role);
            $map = [];
            foreach ($rows as $r) {
                $map[$r['menu']] = (bool) $r['can_access'];
            }
            $permissions[$role] = $map;
        }

        $pengaturanSistem = $pengaturanModel->allAsMap();

        $this->view('pengaturan/index', [
            'title'       => 'Pengaturan',
            'roles'       => $roles,
            'daftarMenu'  => $this->daftarMenu,
            'permissions' => $permissions,
            'pengaturan'  => $pengaturanSistem,
        ]);
    }

    public function simpanRole()
    {
        AuthMiddleware::role(['admin']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('pengaturan');
            return;
        }

        $role = $this->input('role', '');
        $rolePermissionModel = $this->model('RolePermission');
        $logModel = $this->model('LogAktivitas');

        $checkedMenus = $_POST['menu'] ?? [];

        foreach ($this->daftarMenu as $menu) {
            $rolePermissionModel->setAccess($role, $menu, in_array($menu, $checkedMenus, true));
        }

        $logModel->catat(AuthHelper::id(), "Memperbarui hak akses role: {$role}");

        SessionHelper::flash('success', "Hak akses untuk role {$role} berhasil diperbarui.");
        $this->redirect('pengaturan');
    }

    public function simpanSistem()
    {
        AuthMiddleware::role(['admin']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('pengaturan');
            return;
        }

        $pengaturanModel = $this->model('PengaturanSistem');
        $logModel = $this->model('LogAktivitas');

        $pengaturanModel->set('nama_perusahaan', ValidationHelper::clean($this->input('nama_perusahaan', '')));
        $pengaturanModel->set('tahun_anggaran', ValidationHelper::clean($this->input('tahun_anggaran', '')));
        $pengaturanModel->set('persen_bpjs_kesehatan', ValidationHelper::clean($this->input('persen_bpjs_kesehatan', '1')));
        $pengaturanModel->set('persen_bpjs_jht', ValidationHelper::clean($this->input('persen_bpjs_jht', '2')));
        $pengaturanModel->set('persen_pph21', ValidationHelper::clean($this->input('persen_pph21', '5')));
        $pengaturanModel->set('uang_makan_harian', ValidationHelper::clean($this->input('uang_makan_harian', '40000')));

        $logModel->catat(AuthHelper::id(), 'Memperbarui pengaturan sistem');

        SessionHelper::flash('success', 'Pengaturan sistem berhasil disimpan.');
        $this->redirect('pengaturan');
    }
}
