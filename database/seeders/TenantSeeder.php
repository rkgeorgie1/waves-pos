<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Brand;
use App\Models\Category;
use App\Models\ExpenseCategory;
use App\Models\Role;
use App\Models\Setting;
use App\Models\Unit;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Seeds the default operating data for the tenant in the current context.
 */
class TenantSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'Super Administrator', 'slug' => 'super-admin', 'is_system' => true],
            ['name' => 'Manager', 'slug' => 'manager', 'is_system' => true],
            ['name' => 'Accountant', 'slug' => 'accountant', 'is_system' => true],
            ['name' => 'Storekeeper', 'slug' => 'storekeeper', 'is_system' => true],
            ['name' => 'Procurement Officer', 'slug' => 'procurement', 'is_system' => true],
            ['name' => 'Salesperson', 'slug' => 'salesperson', 'is_system' => true],
            ['name' => 'Cashier', 'slug' => 'cashier', 'is_system' => true],
            ['name' => 'HR Officer', 'slug' => 'hr', 'is_system' => true],
            ['name' => 'Auditor', 'slug' => 'auditor', 'is_system' => true],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['slug' => $role['slug']], $role);
        }

        foreach (['pcs' => 'Pieces', 'm' => 'Metre', 'mtr' => 'Metre', 'box' => 'Box', 'roll' => 'Roll', 'kg' => 'Kilogram'] as $short => $name) {
            Unit::firstOrCreate(['short_name' => $short], ['name' => $name]);
        }

        Warehouse::firstOrCreate(['code' => 'MAIN'], ['name' => 'Main Warehouse', 'is_default' => true]);
        Warehouse::firstOrCreate(['code' => 'SHOP'], ['name' => 'Retail Shop']);

        foreach (['Electrical Cables', 'Circuit Breakers', 'Switches & Sockets', 'LED Lighting', 'Electrical Conduits', 'Distribution Boards', 'Solar Equipment', 'Batteries', 'Electrical Accessories', 'Electronic Equipment'] as $name) {
            Category::firstOrCreate(['name' => $name], ['slug' => Str::slug($name)]);
        }

        foreach (['Schneider', 'ABB', 'Siemens', 'Philips', 'Havells', 'Legrand', 'Generic'] as $name) {
            Brand::firstOrCreate(['name' => $name]);
        }

        $accounts = [
            ['code' => '1000', 'name' => 'Cash', 'type' => 'asset'],
            ['code' => '1010', 'name' => 'Bank', 'type' => 'asset'],
            ['code' => '1100', 'name' => 'Accounts Receivable', 'type' => 'asset'],
            ['code' => '1200', 'name' => 'Inventory', 'type' => 'asset'],
            ['code' => '2000', 'name' => 'Accounts Payable', 'type' => 'liability'],
            ['code' => '3000', 'name' => 'Capital', 'type' => 'equity'],
            ['code' => '4000', 'name' => 'Sales Revenue', 'type' => 'revenue'],
            ['code' => '5000', 'name' => 'Cost of Goods Sold', 'type' => 'expense'],
            ['code' => '6000', 'name' => 'Operating Expenses', 'type' => 'expense'],
        ];

        foreach ($accounts as $account) {
            Account::firstOrCreate(['code' => $account['code']], $account);
        }

        foreach (['Rent', 'Electricity', 'Internet', 'Salaries', 'Transport', 'Repairs', 'Maintenance', 'Stationery', 'Other Operating Expenses'] as $name) {
            ExpenseCategory::firstOrCreate(['name' => $name]);
        }

        $settings = [
            ['key' => 'business_name', 'value' => 'My Business', 'group' => 'business'],
            ['key' => 'currency', 'value' => 'KES', 'group' => 'business'],
            ['key' => 'vat_rate', 'value' => '16', 'group' => 'tax'],
            ['key' => 'tax_enabled', 'value' => '1', 'group' => 'tax'],
        ];

        foreach ($settings as $setting) {
            Setting::firstOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
