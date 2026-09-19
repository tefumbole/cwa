<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class MembershipHandoffService
{
    const TTL = 1800;

    public function create($purpose, array $extra = [])
    {
        $token = Str::random(40);
        $payload = array_merge([
            'purpose' => $purpose,
            'done' => false,
        ], $extra);
        Cache::put($this->key($token), $payload, self::TTL);

        return $token;
    }

    public function get($token)
    {
        $data = Cache::get($this->key($token));

        return is_array($data) ? $data : null;
    }

    public function put($token, array $data)
    {
        Cache::put($this->key($token), $data, self::TTL);
    }

    public function merge($token, array $patch)
    {
        $data = $this->get($token);
        if (! $data) {
            return null;
        }
        $data = array_merge($data, $patch);
        $this->put($token, $data);

        return $data;
    }

    protected function key($token)
    {
        return 'mship_h_'.$token;
    }
}
