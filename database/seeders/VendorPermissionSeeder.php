<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VendorPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $modules = [
            'dashboard' => ['view'],
            'products' => ['view', 'create', 'update', 'delete'],
            'inventory' => ['view', 'adjust', 'transfer'],
            'sales' => ['view', 'create', 'refund'],
            'purchases' => ['view', 'create', 'update', 'approve'],
            'customers' => ['view', 'create', 'update', 'delete'],
            'suppliers' => ['view', 'create', 'update', 'delete'],
            'branches' => ['view', 'create', 'update'],
            'warehouses' => ['view', 'create', 'update'],
            'users' => ['view', 'create', 'update', 'deactivate'],
            'roles' => ['view', 'create', 'update', 'assign'],
            'reports' => ['view', 'export'],
            'settings' => ['view', 'update'],
        ];

        foreach ($modules as $module => $actions) {
            foreach ($actions as $action) {
                $code = "{$module}.{$action}";

                DB::table('vendor_permissions')->updateOrInsert(
                    ['permission_code' => $code],
                    [
                        'permission_name' => ucwords(
                            str_replace('.', ' ', $code)
                        ),
                        'module' => $module,
                        'action' => $action,
                        'description' => null,
                        'is_active' => true,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }
        }
    }
}
