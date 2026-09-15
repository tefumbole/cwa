<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class CampayService
{
    public function isConfigured()
    {
        return $this->token() !== '';
    }

    public function token()
    {
        $static = trim((string) (
            config('services.campay.token')
            ?: getenv('CAMPAY_TOKEN')
            ?: getenv('MOMO_TOKEN')
            ?: ''
        ));
        if ($static !== '') {
            return $static;
        }

        return $this->tokenFromPassword();
    }

    public function createPaymentLink($amount, $phone, $redirectUrl, $externalReference, $description = 'CWACAM donation', $paymentOptions = 'MOMO')
    {
        $token = $this->token();
        if ($token === '') {
            return null;
        }

        $payload = json_encode(array(
            'amount' => (string) max(1, (int) $amount),
            'from' => $this->normalizePhone($phone),
            'currency' => 'XAF',
            'description' => $description,
            'external_reference' => (string) $externalReference,
            'redirect_url' => $redirectUrl,
            'failure_redirect_url' => $redirectUrl,
            'payment_options' => $paymentOptions,
        ));

        $raw = $this->http('POST', $this->baseUrl().'/get_payment_link/', $payload, array(
            'Authorization: Token '.$token,
            'Content-Type: application/json',
            'Accept: application/json',
        ));
        $decoded = json_decode((string) $raw, true);
        if (is_array($decoded) && ! empty($decoded['link'])) {
            return $decoded['link'];
        }

        Log::warning('Campay payment link failed', array('body' => substr((string) $raw, 0, 300)));

        return null;
    }

    public function normalizePhone($phone)
    {
        $digits = preg_replace('/\D/', '', (string) $phone);
        if ($digits === '') {
            return '';
        }
        if (strpos($digits, '237') === 0) {
            return $digits;
        }
        if (strlen($digits) === 9) {
            return '237'.$digits;
        }
        if (strlen($digits) === 10 && substr($digits, 0, 1) === '0') {
            return '237'.substr($digits, 1);
        }

        return $digits;
    }

    protected function tokenFromPassword()
    {
        $username = trim((string) (config('services.campay.username') ?: getenv('CAMPAY_USERNAME') ?: ''));
        $password = (string) (config('services.campay.password') ?: getenv('CAMPAY_PASSWORD') ?: '');
        if ($username === '' || $password === '') {
            return '';
        }

        $raw = $this->http('POST', $this->baseUrl().'/token/', json_encode(array(
            'username' => $username,
            'password' => $password,
        )), array(
            'Content-Type: application/json',
            'Accept: application/json',
        ));
        $decoded = json_decode((string) $raw, true);
        if (is_array($decoded) && ! empty($decoded['token'])) {
            return (string) $decoded['token'];
        }

        Log::warning('Campay token request failed');

        return '';
    }

    protected function baseUrl()
    {
        return rtrim((string) (
            config('services.campay.base_url')
            ?: getenv('CAMPAY_BASE_URL')
            ?: 'https://www.campay.net/api'
        ), '/');
    }

    protected function http($method, $url, $body, array $headers)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 25,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_HTTPHEADER => $headers,
        ));
        $raw = curl_exec($curl);
        curl_close($curl);

        return $raw;
    }
}
