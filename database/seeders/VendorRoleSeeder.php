<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VendorRoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['OWNER', 'Owner'],
            ['ADMIN', 'Administrator'],
            ['GENERAL_MANAGER', 'General Manager'],
            ['BRANCH_MANAGER', 'Branch Manager'],
            ['CASHIER', 'Cashier'],
            ['ACCOUNTANT', 'Accountant'],
            ['INVENTORY_MANAGER', 'Inventory Manager'],
            ['WAREHOUSE_MANAGER', 'Warehouse Manager'],
            ['PURCHASE_MANAGER', 'Purchase Manager'],
            ['SALES_MANAGER', 'Sales Manager'],
            ['HR_MANAGER', 'HR Manager'],
        ];

        $companyIds = DB::table('companies')
            ->whereNull('deleted_at')
            ->pluck('id');

        foreach ($companyIds as $companyId) {
            foreach ($roles as [$code, $name]) {
                DB::table('vendor_roles')->updateOrInsert(
                    [
                        'company_id' => $companyId,
                        'role_code' => $code,
                    ],
                    [
                        'role_name' => $name,
                        'description' => "Company role template: {$name}",
                        'is_system' => true,
                        'is_active' => true,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }
        }
    }
}
