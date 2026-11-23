<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Every table that holds data belonging to one merchant. */
    private const TENANT_TABLES = [
        'users', 'roles', 'permissions', 'role_permissions',
        'employees', 'customers', 'customer_groups', 'suppliers',
        'categories', 'brands', 'units', 'products', 'product_variants',
        'product_batches', 'serial_numbers', 'warranties',
        'selling_price_groups', 'product_selling_prices',
        'warehouses', 'stock_balances', 'stock_movements', 'stock_adjustments',
        'warehouse_transfers', 'warehouse_transfer_items',
        'purchases', 'purchase_items', 'goods_received_notes',
        'purchase_returns', 'purchase_return_items',
        'quotations', 'quotation_items',
        'sales', 'sale_items', 'sales_returns', 'sales_return_items',
        'cash_registers', 'cash_shifts',
        'payments', 'payment_allocations',
        'accounts', 'journal_entries', 'journal_lines',
        'customer_ledger', 'supplier_ledger',
        'expenses', 'expense_categories',
        'notifications', 'audit_logs', 'settings',
    ];

    /**
     * Previously global unique keys that must become unique per tenant.
     * users.email deliberately stays globally unique so login is unambiguous.
     *
     * @var array<string, list<list<string>>>
     */
    private const UNIQUE_INDEXES = [
        'roles' => [['name'], ['slug']],
        'permissions' => [['name'], ['slug']],
        'role_permissions' => [['role_id', 'permission_id']],
        'employees' => [['employee_number']],
        'quotations' => [['reference']],
        'sales_returns' => [['reference']],
        'purchase_returns' => [['reference']],
        'brands' => [['name']],
        'categories' => [['slug']],
        'products' => [['sku'], ['barcode']],
        'product_variants' => [['sku'], ['barcode']],
        'units' => [['short_name']],
        'warehouses' => [['code']],
        'stock_balances' => [['product_id', 'warehouse_id']],
        'stock_adjustments' => [['reference']],
        'goods_received_notes' => [['reference']],
        'purchases' => [['reference']],
        'payments' => [['reference']],
        'expense_categories' => [['name']],
        'expenses' => [['reference']],
        'accounts' => [['code']],
        'journal_entries' => [['reference']],
        'cash_registers' => [['code']],
        'warehouse_transfers' => [['reference']],
        'sales' => [['reference']],
        'settings' => [['key']],
        'product_selling_prices' => [['product_id', 'selling_price_group_id']],
        'serial_numbers' => [['serial']],
    ];

    /**
     * Columns in a unique index that are also foreign keys. MySQL will not
     * drop an index that is still needed by a foreign key constraint.
     *
     * @var array<string, array<string, array{0: string, 1: string}>>
     */
    private const INDEX_FOREIGN_KEYS = [
        'role_permissions' => [
            'role_id' => ['roles', 'id'],
            'permission_id' => ['permissions', 'id'],
        ],
        'stock_balances' => [
            'product_id' => ['products', 'id'],
            'warehouse_id' => ['warehouses', 'id'],
        ],
        'product_selling_prices' => [
            'product_id' => ['products', 'id'],
            'selling_price_group_id' => ['selling_price_groups', 'id'],
        ],
    ];

    public function up(): void
    {
        $defaultTenantId = $this->defaultTenantId();

        foreach (self::TENANT_TABLES as $tableName) {
            if (! Schema::hasColumn($tableName, 'tenant_id')) {
                Schema::table($tableName, function (Blueprint $table): void {
                    $table->unsignedBigInteger('tenant_id')->nullable()->index();
                });
            }

            if (DB::table($tableName)->whereNull('tenant_id')->exists()) {
                DB::table($tableName)->update(['tenant_id' => $defaultTenantId]);
            }
        }

        foreach (self::TENANT_TABLES as $tableName) {
            if ($tableName !== 'audit_logs') {
                Schema::table($tableName, function (Blueprint $table): void {
                    $table->unsignedBigInteger('tenant_id')->nullable(false)->change();
                });
            }

            foreach (self::UNIQUE_INDEXES[$tableName] ?? [] as $columns) {
                $this->convertUniqueIndex($tableName, $columns);
            }
        }
    }

    public function down(): void
    {
        foreach (self::TENANT_TABLES as $tableName) {
            foreach (self::UNIQUE_INDEXES[$tableName] ?? [] as $columns) {
                $newColumns = array_merge(['tenant_id'], $columns);

                if (Schema::hasIndex($tableName, $newColumns, 'unique')) {
                    $newIndexName = $this->tenantUniqueIndexName($tableName, $columns);

                    Schema::table($tableName, function (Blueprint $table) use ($columns, $newIndexName): void {
                        $table->dropUnique($newIndexName);
                        $table->unique($columns);
                    });
                }
            }

            if (Schema::hasColumn($tableName, 'tenant_id')) {
                Schema::table($tableName, function (Blueprint $table): void {
                    $table->dropColumn('tenant_id');
                });
            }
        }

        DB::table('tenants')->where('slug', 'default')->delete();
    }

    /**
     * @param  list<string>  $columns
     */
    private function convertUniqueIndex(string $tableName, array $columns): void
    {
        $newColumns = array_merge(['tenant_id'], $columns);
        $newIndexName = $this->tenantUniqueIndexName($tableName, $columns);
        $oldIndexName = $tableName.'_'.implode('_', $columns).'_unique';

        $newExists = Schema::hasIndex($tableName, $newColumns, 'unique');
        $oldExists = Schema::hasIndex($tableName, $oldIndexName, 'unique');

        if ($newExists) {
            return;
        }

        $foreignKeys = $this->foreignKeysFor($tableName, $columns);

        if ($foreignKeys === []) {
            Schema::table($tableName, function (Blueprint $table) use ($oldExists, $columns, $newColumns, $newIndexName): void {
                if ($oldExists) {
                    $table->dropUnique($columns);
                }

                $table->unique($newColumns, $newIndexName);
            });

            return;
        }

        $existingForeignKeys = $this->existingForeignKeys($tableName, $foreignKeys);

        if ($existingForeignKeys !== []) {
            Schema::table($tableName, function (Blueprint $table) use ($existingForeignKeys): void {
                foreach (array_keys($existingForeignKeys) as $column) {
                    $table->dropForeign([$column]);
                }
            });
        }

        $singleColumnIndexes = array_values(array_filter(
            $columns,
            fn (string $column): bool => ! Schema::hasIndex($tableName, [$column])
        ));

        Schema::table($tableName, function (Blueprint $table) use ($oldExists, $columns, $newColumns, $newIndexName, $singleColumnIndexes): void {
            if ($oldExists) {
                $table->dropUnique($columns);
            }

            foreach ($singleColumnIndexes as $column) {
                $table->index($column);
            }

            $table->unique($newColumns, $newIndexName);
        });

        Schema::table($tableName, function (Blueprint $table) use ($foreignKeys): void {
            foreach ($foreignKeys as $column => [$referencedTable, $referencedColumn]) {
                $table->foreign($column)->references($referencedColumn)->on($referencedTable);
            }
        });
    }

    /**
     * @param  list<string>  $columns
     */
    private function tenantUniqueIndexName(string $tableName, array $columns): string
    {
        return 'tnt_'.substr(md5($tableName.'_'.implode('_', $columns)), 0, 16).'_uniq';
    }

    /**
     * @param  array<string, array{0: string, 1: string}>  $foreignKeys
     * @return array<string, array{0: string, 1: string}>
     */
    private function existingForeignKeys(string $tableName, array $foreignKeys): array
    {
        if (DB::getDriverName() !== 'mysql') {
            return $foreignKeys;
        }

        $existing = [];

        foreach ($foreignKeys as $column => $definition) {
            $name = $tableName.'_'.$column.'_foreign';
            $result = DB::selectOne(
                'select count(*) as total from information_schema.TABLE_CONSTRAINTS where TABLE_SCHEMA = database() and TABLE_NAME = ? and CONSTRAINT_NAME = ? and CONSTRAINT_TYPE = ?',
                [$tableName, $name, 'FOREIGN KEY']
            );

            if ((int) ($result->total ?? 0) > 0) {
                $existing[$column] = $definition;
            }
        }

        return $existing;
    }

    /**
     * @param  list<string>  $columns
     * @return array<string, array{0: string, 1: string}>
     */
    private function foreignKeysFor(string $tableName, array $columns): array
    {
        return array_intersect_key(self::INDEX_FOREIGN_KEYS[$tableName] ?? [], array_flip($columns));
    }

    private function defaultTenantId(): int
    {
        $tenant = DB::table('tenants')->where('slug', 'default')->first();

        if ($tenant !== null) {
            return (int) $tenant->id;
        }

        return (int) DB::table('tenants')->insertGetId([
            'name' => 'Default Organization',
            'slug' => 'default',
            'status' => 'active',
            'subscription_status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
};
