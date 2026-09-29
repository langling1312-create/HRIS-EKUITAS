<?php

class AuthHelper
{
    public static function check(): bool
    {
        return SessionHelper::has('user_id');
    }

    public static function id()
    {
        return SessionHelper::get('user_id');
    }

    public static function name()
    {
        return SessionHelper::get('name');
    }

    public static function role()
    {
        return SessionHelper::get('role');
    }

    public static function foto()
    {
        return SessionHelper::get('foto');
    }

    public static function isAdmin(): bool
    {
        return self::role() === 'admin';
    }

    public static function isHr(): bool
    {
        return self::role() === 'hr';
    }

    public static function isHrOrAdmin(): bool
    {
        return in_array(self::role(), ['admin', 'hr'], true);
    }

    public static function isKaryawan(): bool
    {
        return self::role() === 'karyawan';
    }

    public static function isKepalaUnit(): bool
    {
        return self::role() === 'kepala_unit';
    }

    public static function isPimpinanUnit(): bool
    {
        return self::role() === 'pimpinan_unit';
    }

    /**
     * Role "staf" yang punya menu self-service sendiri (absensi, cuti, payroll,
     * shift): karyawan biasa, Kepala Unit, dan Pimpinan Unit. Kepala Unit &
     * Pimpinan Unit tetaplah pegawai yang absen dan mengajukan cuti sendiri,
     * hanya saja mereka juga bertugas menyetujui cuti bawahannya.
     */
    public static function isStaff(): bool
    {
        return in_array(self::role(), ['karyawan', 'kepala_unit', 'pimpinan_unit'], true);
    }

    public static function login(array $user): void
    {
        SessionHelper::set('user_id', $user['id']);
        SessionHelper::set('name', $user['name']);
        SessionHelper::set('role', $user['role']);
        SessionHelper::set('foto', $user['foto'] ?? null);
    }

    public static function logout(): void
    {
        SessionHelper::destroy();
    }
}
