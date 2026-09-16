<?php

namespace App\Support;

/**
 * Detect MTN vs Orange from a Cameroon mobile number (prefix), with optional
 * override from Campay holder_info.operator.
 */
class CameroonMomoNetwork
{
    public static function localDigits($phone)
    {
        $digits = preg_replace('/\D/', '', (string) $phone);
        if (strpos($digits, '237') === 0) {
            $digits = substr($digits, 3);
        }
        if (strlen($digits) === 10 && $digits[0] === '0') {
            $digits = substr($digits, 1);
        }

        return substr($digits, 0, 9);
    }

    /**
     * @return string|null mtn|orange
     */
    public static function detect($phone)
    {
        $local = self::localDigits($phone);
        if (strlen($local) < 3) {
            return null;
        }

        $p2 = substr($local, 0, 2);
        $p3 = substr($local, 0, 3);

        if ($p2 === '69' || in_array($p3, ['655', '656', '657', '658', '659'], true)) {
            return 'orange';
        }
        if ($p2 === '67' || in_array($p3, ['650', '651', '652', '653', '654', '680', '681', '682', '683', '684'], true)) {
            return 'mtn';
        }

        return null;
    }

    /**
     * @return string|null mtn|orange
     */
    public static function fromApiValue($raw)
    {
        $n = strtoupper(trim(preg_replace('/\s+/', ' ', (string) $raw)));
        if ($n === '') {
            return null;
        }
        if (strpos($n, 'ORANGE') !== false || $n === 'OM' || $n === 'ORANGE_CMR') {
            return 'orange';
        }
        if (strpos($n, 'MTN') !== false || $n === 'MOMO' || $n === 'MTN_MOMO_CMR') {
            return 'mtn';
        }

        return null;
    }

    public static function label($operator)
    {
        if ($operator === 'orange') {
            return 'Orange Money';
        }
        if ($operator === 'mtn') {
            return 'MTN MoMo';
        }

        return null;
    }

    public static function campayOption($operator)
    {
        if ($operator === 'orange') {
            return 'OM';
        }
        if ($operator === 'mtn') {
            return 'MOMO';
        }

        return 'MOMO,OM';
    }
}
