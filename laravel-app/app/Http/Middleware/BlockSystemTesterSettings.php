<?php

namespace App\Http\Middleware;

use App\Support\SystemTestAccount;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BlockSystemTesterSettings
{
    public function handle($request, Closure $next)
    {
        $user = Auth::user();
        if ($user) {
            $role = DB::table('roles')->where('id', $user->role_id)->value('name');
            if ($role === SystemTestAccount::ROLE && $this->isSettingsPath($request->path())) {
                abort(403, 'Settings are not available on a test account.');
            }
        }

        return $next($request);
    }

    protected function isSettingsPath($path)
    {
        $path = trim((string) $path, '/');
        $prefixes = [
            'setting',
            'backup',
            'role',
            'warehouse',
            'customer_group',
            'brand',
            'unit',
            'currency',
            'tax',
        ];
        foreach ($prefixes as $prefix) {
            if ($path === $prefix || strpos($path, $prefix.'/') === 0) {
                return true;
            }
        }

        return false;
    }
}
