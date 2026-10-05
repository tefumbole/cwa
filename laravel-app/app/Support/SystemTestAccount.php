<?php

namespace App\Support;

use App\Biller;
use App\User;
use App\Warehouse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SystemTestAccount
{
    const ROLE = 'System Tester';

    public static function excludedPermissions()
    {
        return [
            'send_notification',
            'warehouse',
            'customer_group',
            'brand',
            'unit',
            'currency',
            'tax',
            'general_setting',
            'backup_database',
            'mail_setting',
            'sms_setting',
            'pos_setting',
            'reward_point_setting',
            'empty_database',
            'env_setting',
            'role_permission',
        ];
    }

    /**
     * @return array{username:string,password:?string,created:bool}
     */
    public static function ensure($name, $phone)
    {
        $roleId = self::roleId();
        $existing = self::findByPhone($phone);
        if ($existing) {
            $roleName = DB::table('roles')->where('id', $existing->role_id)->value('name');
            if ($roleName !== self::ROLE) {
                return [
                    'username' => (string) $existing->username,
                    'password' => null,
                    'created' => false,
                ];
            }
            if (trim((string) $existing->username) === '') {
                $password = 'Cwa-'.random_int(100000, 999999);
                $existing->username = self::uniqueUsername($name);
                $existing->password = bcrypt($password);
                $existing->name = $name;
                $existing->is_active = 1;
                $existing->save();

                return [
                    'username' => (string) $existing->username,
                    'password' => $password,
                    'created' => true,
                ];
            }

            return [
                'username' => (string) $existing->username,
                'password' => null,
                'created' => false,
            ];
        }

        $username = self::uniqueUsername($name);
        $password = 'Cwa-'.random_int(100000, 999999);
        $email = self::uniqueEmail($username);
        $warehouseId = optional(Warehouse::where('is_active', true)->first())->id;
        $billerId = optional(Biller::where('is_active', true)->first())->id;

        User::create([
            'name' => $name,
            'username' => $username,
            'email' => $email,
            'phone' => $phone,
            'password' => bcrypt($password),
            'role_id' => $roleId,
            'warehouse_id' => $warehouseId,
            'biller_id' => $billerId,
            'is_active' => 1,
            'is_deleted' => 0,
        ]);

        return [
            'username' => $username,
            'password' => $password,
            'created' => true,
        ];
    }

    public static function roleId()
    {
        $role = DB::table('roles')->where('name', self::ROLE)->first();
        if (! $role) {
            $row = [
                'name' => self::ROLE,
                'description' => 'Temporary account for the public system test. No Settings menu.',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
            if (Schema::hasColumn('roles', 'guard_name')) {
                $row['guard_name'] = 'web';
            }
            $id = DB::table('roles')->insertGetId($row);
        } else {
            $id = $role->id;
        }

        self::syncPermissions($id);

        return (int) $id;
    }

    protected static function syncPermissions($roleId)
    {
        $admin = DB::table('roles')->where('name', 'Admin')->first();
        if (! $admin) {
            return;
        }

        $exclude = self::excludedPermissions();
        $ids = DB::table('role_has_permissions')
            ->join('permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
            ->where('role_has_permissions.role_id', $admin->id)
            ->whereNotIn('permissions.name', $exclude)
            ->pluck('permissions.id');

        $have = array_map('intval', DB::table('role_has_permissions')->where('role_id', $roleId)->pluck('permission_id')->all());
        foreach ($ids as $permissionId) {
            if (! in_array((int) $permissionId, $have, true)) {
                DB::table('role_has_permissions')->insert([
                    'permission_id' => $permissionId,
                    'role_id' => $roleId,
                ]);
            }
        }

        $drop = DB::table('permissions')->whereIn('name', $exclude)->pluck('id');
        if ($drop->count()) {
            DB::table('role_has_permissions')->where('role_id', $roleId)->whereIn('permission_id', $drop)->delete();
        }
    }

    protected static function findByPhone($phone)
    {
        $digits = preg_replace('/\D/', '', (string) $phone);
        $tail = substr($digits, -9);

        return User::query()
            ->where(function ($q) {
                $q->where('is_deleted', 0)->orWhere('is_deleted', false)->orWhereNull('is_deleted');
            })
            ->where(function ($q) use ($phone, $digits, $tail) {
                $q->where('phone', $phone)
                    ->orWhere('phone', $digits)
                    ->orWhere('phone', '+'.$digits);
                if (strlen($tail) >= 8) {
                    $q->orWhere('phone', 'like', '%'.$tail);
                }
            })
            ->orderByDesc('id')
            ->first();
    }

    protected static function uniqueUsername($name)
    {
        $parts = preg_split('/\s+/', trim((string) $name));
        $base = strtolower((string) preg_replace('/[^a-z]/i', '', (string) ($parts[0] ?? '')));
        if (strlen($base) < 3) {
            $base = 'tester';
        }
        $username = $base;
        $i = 2;
        while (User::query()->whereRaw('LOWER(username) = ?', [strtolower($username)])->exists()) {
            $username = $base.$i;
            $i++;
        }

        return $username;
    }

    protected static function uniqueEmail($username)
    {
        $email = strtolower($username).'@testers.cwacam.org';
        $i = 2;
        while (User::query()->whereRaw('LOWER(email) = ?', [strtolower($email)])->exists()) {
            $email = strtolower($username).$i.'@testers.cwacam.org';
            $i++;
        }

        return $email;
    }
}
