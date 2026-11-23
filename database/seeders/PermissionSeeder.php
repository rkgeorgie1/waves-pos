<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\TenantContext;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Permission slug => [display name, module].
     *
     * @var array<string, array{0: string, 1: string}>
     */
    private const PERMISSIONS = [
        'dashboard.view' => ['View dashboard', 'Dashboard'],
        'products.view' => ['View products', 'Products'],
        'products.manage' => ['Manage products', 'Products'],
        'sales.view' => ['View sales', 'Sales'],
        'sales.manage' => ['Manage sales', 'Sales'],
        'pos.access' => ['Use point of sale', 'Sales'],
        'quotations.manage' => ['Manage quotations', 'Sales'],
        'sales_returns.manage' => ['Manage sales returns', 'Sales'],
        'purchases.view' => ['View purchases', 'Purchases'],
        'purchases.manage' => ['Manage purchases', 'Purchases'],
        'purchase_returns.manage' => ['Manage purchase returns', 'Purchases'],
        'inventory.view' => ['View inventory', 'Inventory'],
        'inventory.manage' => ['Manage inventory', 'Inventory'],
        'warehouse_transfers.manage' => ['Manage warehouse transfers', 'Inventory'],
        'customers.view' => ['View customers', 'Customers'],
        'customers.manage' => ['Manage customers', 'Customers'],
        'ledgers.view' => ['View ledgers', 'Customers'],
        'suppliers.view' => ['View suppliers', 'Suppliers'],
        'suppliers.manage' => ['Manage suppliers', 'Suppliers'],
        'expenses.view' => ['View expenses', 'Expenses'],
        'expenses.manage' => ['Manage expenses', 'Expenses'],
        'reports.view' => ['View reports', 'Reports'],
        'reports.export' => ['Export reports', 'Reports'],
        'cash_shifts.manage' => ['Manage cash shifts', 'Cash'],
        'settings.view' => ['View settings', 'Settings'],
        'settings.manage' => ['Manage settings', 'Settings'],
        'notifications.view' => ['View notifications', 'Settings'],
    ];

    /**
     * Role slug => permission slugs granted to that role.
     * Super administrators bypass this list in User::hasPermission().
     *
     * @var array<string, list<string>>
     */
    private const ROLE_PERMISSIONS = [
        'manager' => [
            'dashboard.view', 'products.view', 'products.manage', 'sales.view', 'sales.manage',
            'pos.access', 'quotations.manage', 'sales_returns.manage', 'purchases.view',
            'purchases.manage', 'purchase_returns.manage', 'inventory.view', 'inventory.manage',
            'warehouse_transfers.manage', 'customers.view', 'customers.manage', 'ledgers.view',
            'suppliers.view', 'suppliers.manage', 'expenses.view', 'expenses.manage',
            'reports.view', 'reports.export', 'cash_shifts.manage', 'settings.view',
            'settings.manage', 'notifications.view',
        ],
        'accountant' => [
            'dashboard.view', 'sales.view', 'purchases.view', 'expenses.view', 'ledgers.view',
            'reports.view', 'reports.export', 'settings.view', 'notifications.view',
        ],
        'storekeeper' => [
            'dashboard.view', 'products.view', 'products.manage', 'inventory.view',
            'inventory.manage', 'warehouse_transfers.manage', 'purchases.view',
            'reports.view', 'notifications.view',
        ],
        'procurement' => [
            'dashboard.view', 'products.view', 'purchases.view', 'purchases.manage',
            'purchase_returns.manage', 'suppliers.view', 'suppliers.manage',
            'inventory.view', 'reports.view', 'notifications.view',
        ],
        'salesperson' => [
            'dashboard.view', 'pos.access', 'sales.view', 'sales.manage', 'quotations.manage',
            'customers.view', 'customers.manage', 'products.view', 'inventory.view',
            'reports.view', 'notifications.view',
        ],
        'cashier' => [
            'dashboard.view', 'pos.access', 'sales.view', 'customers.view', 'products.view',
            'inventory.view', 'cash_shifts.manage', 'notifications.view',
        ],
        'hr' => [
            'dashboard.view', 'settings.view', 'settings.manage', 'notifications.view',
        ],
        'auditor' => [
            'dashboard.view', 'sales.view', 'purchases.view', 'ledgers.view',
            'reports.view', 'reports.export', 'settings.view', 'notifications.view',
        ],
    ];

    public function run(): void
    {
        foreach (self::PERMISSIONS as $slug => [$name, $module]) {
            Permission::firstOrCreate(
                ['slug' => $slug],
                ['name' => $name, 'module' => $module],
            );
        }

        $permissionsBySlug = Permission::all()->keyBy('slug');

        foreach (self::ROLE_PERMISSIONS as $roleSlug => $permissionSlugs) {
            $role = Role::where('slug', $roleSlug)->first();

            if ($role === null) {
                continue;
            }

            $tenantId = app(TenantContext::class)->id();

            $role->permissions()->sync(
                collect($permissionSlugs)
                    ->map(fn (string $slug): ?int => $permissionsBySlug->get($slug)?->id)
                    ->filter()
                    ->mapWithKeys(fn (int $permissionId): array => [
                        $permissionId => ['tenant_id' => $tenantId],
                    ])
                    ->all()
            );
        }
    }
}
