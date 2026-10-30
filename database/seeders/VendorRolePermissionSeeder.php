<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VendorRolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Explicit allowlists. Unlisted permissions are denied.
        $rolePermissions = [
            'OWNER' => [
                'dashboard.view',
                'products.view', 'products.create',
                'products.update', 'products.delete',
                'inventory.view', 'inventory.adjust',
                'inventory.transfer',
                'sales.view', 'sales.create', 'sales.refund',
                'purchases.view', 'purchases.create',
                'purchases.update', 'purchases.approve',
                'customers.view', 'customers.create',
                'customers.update', 'customers.delete',
                'suppliers.view', 'suppliers.create',
                'suppliers.update', 'suppliers.delete',
                'branches.view', 'branches.create', 'branches.update',
                'warehouses.view', 'warehouses.create',
                'warehouses.update',
                'users.view', 'users.create', 'users.update',
                'users.deactivate',
                'roles.view', 'roles.create', 'roles.update',
                'roles.assign',
                'reports.view', 'reports.export',
                'settings.view', 'settings.update',
            ],

            'ADMIN' => [
                'dashboard.view',
                'products.view', 'products.create',
                'products.update', 'products.delete',
                'inventory.view', 'inventory.adjust',
                'inventory.transfer',
                'sales.view', 'sales.create', 'sales.refund',
                'purchases.view', 'purchases.create',
                'purchases.update', 'purchases.approve',
                'customers.view', 'customers.create',
                'customers.update', 'customers.delete',
                'suppliers.view', 'suppliers.create',
                'suppliers.update', 'suppliers.delete',
                'branches.view',
                'warehouses.view', 'warehouses.create',
                'warehouses.update',
                'users.view', 'users.create', 'users.update',
                'users.deactivate',
                'roles.view',
                'reports.view', 'reports.export',
                'settings.view', 'settings.update',
            ],

            'GENERAL_MANAGER' => [
                'dashboard.view',
                'products.view',
                'inventory.view', 'inventory.adjust',
                'inventory.transfer',
                'sales.view', 'sales.create',
                'purchases.view', 'purchases.create',
                'purchases.update', 'purchases.approve',
                'customers.view', 'customers.create',
                'customers.update',
                'suppliers.view', 'suppliers.create',
                'suppliers.update',
                'branches.view', 'warehouses.view',
                'reports.view', 'reports.export',
            ],

            'BRANCH_MANAGER' => [
                'dashboard.view',
                'products.view',
                'inventory.view', 'inventory.adjust',
                'sales.view', 'sales.create',
                'purchases.view', 'purchases.create',
                'customers.view', 'customers.create',
                'customers.update',
                'suppliers.view',
                'warehouses.view',
                'reports.view',
            ],

            'CASHIER' => [
                'dashboard.view',
                'products.view',
                'sales.view', 'sales.create',
                'customers.view', 'customers.create',
            ],

            'ACCOUNTANT' => [
                'dashboard.view',
                'sales.view',
                'purchases.view',
                'reports.view', 'reports.export',
            ],

            'INVENTORY_MANAGER' => [
                'dashboard.view',
                'products.view', 'products.create',
                'products.update',
                'inventory.view', 'inventory.adjust',
                'inventory.transfer',
                'warehouses.view',
                'reports.view',
            ],

            'WAREHOUSE_MANAGER' => [
                'dashboard.view',
                'products.view',
                'inventory.view', 'inventory.adjust',
                'inventory.transfer',
                'warehouses.view',
                'reports.view',
            ],

            'PURCHASE_MANAGER' => [
                'dashboard.view',
                'products.view',
                'purchases.view', 'purchases.create',
                'purchases.update', 'purchases.approve',
                'suppliers.view', 'suppliers.create',
                'suppliers.update',
                'inventory.view',
                'reports.view',
            ],

            'SALES_MANAGER' => [
                'dashboard.view',
                'products.view',
                'sales.view', 'sales.create',
                'customers.view', 'customers.create',
                'customers.update',
                'reports.view', 'reports.export',
            ],

            'HR_MANAGER' => [
                'dashboard.view',
                'users.view', 'users.create', 'users.update',
            ],
        ];

        $companies = DB::table('companies')
            ->whereNull('deleted_at')
            ->pluck('id');

        foreach ($companies as $companyId) {
            foreach ($rolePermissions as $roleCode => $permissions) {
                $roleId = DB::table('vendor_roles')
                    ->where('company_id', $companyId)
                    ->where('role_code', $roleCode)
                    ->where('is_active', true)
                    ->value('id');

                if (! $roleId) {
                    continue;
                }

                foreach ($permissions as $permissionCode) {
                    $permissionId = DB::table('vendor_permissions')
                        ->where('permission_code', $permissionCode)
                        ->where('is_active', true)
                        ->value('id');

                    if (! $permissionId) {
                        throw new \RuntimeException(
                            "Missing active permission: {$permissionCode}"
                        );
                    }

                    DB::table('vendor_role_permissions')->updateOrInsert(
                        [
                            'role_id' => $roleId,
                            'permission_id' => $permissionId,
                        ],
                        [
                            'updated_at' => now(),
                            'created_at' => now(),
                        ]
                    );
                }
            }
        }
    }
}
