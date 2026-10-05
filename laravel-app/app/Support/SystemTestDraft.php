<?php

namespace App\Support;

class SystemTestDraft
{
    public static function find($phone)
    {
        $path = self::path($phone);
        if (! is_file($path)) {
            return null;
        }
        $data = json_decode((string) file_get_contents($path), true);

        return is_array($data) ? $data : null;
    }

    public static function put($phone, array $draft)
    {
        $dir = storage_path('app/system-tests/drafts');
        if (! is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        $draft['tester_phone'] = $phone;
        $draft['updated_at'] = date('Y-m-d H:i');
        file_put_contents(self::path($phone), json_encode($draft, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return $draft;
    }

    public static function forget($phone)
    {
        $path = self::path($phone);
        if (is_file($path)) {
            @unlink($path);
        }
    }

    public static function blank($name, $phone)
    {
        return [
            'tester_name' => $name,
            'tester_phone' => $phone,
            'summary' => '',
            'checks' => [],
            'notes' => [],
            'page' => 1,
            'otp_hash' => null,
            'otp_expires' => null,
            'otp_attempts' => 0,
            'updated_at' => date('Y-m-d H:i'),
        ];
    }

    public static function path($phone)
    {
        return storage_path('app/system-tests/drafts/'.hash('sha256', $phone).'.json');
    }
}
