<?php

class ValidationHelper
{
    public static function isEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function minLength(string $value, int $len): bool
    {
        return mb_strlen($value) >= $len;
    }

    public static function required(array $fields, array $data): array
    {
        $errors = [];
        foreach ($fields as $field) {
            if (empty($data[$field]) && $data[$field] !== '0') {
                $errors[] = "Field {$field} wajib diisi.";
            }
        }
        return $errors;
    }

    public static function clean(string $value): string
    {
        return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
    }
}
