<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Employee;
use App\Models\Permission;
use App\Models\PlanLimits;
use App\Models\Role;
use App\Models\Setting;
use App\Models\TenantContext;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SettingController extends Controller
{
    public function index()
    {
        return view('settings.index', ['settings' => Setting::whereNotIn('group', ['currencies_list', 'receipt'])->get()]);
    }

    public function receipt()
    {
        $defaults = [
            'business_name' => 'Airwaves Wholesalers',
            'business_phone' => '',
            'business_email' => '',
            'business_address' => '',
            'business_tax_pin' => '',
            'receipt_header_title' => 'OFFICIAL SALES RECEIPT',
            'receipt_paper_size' => '80mm',
            'receipt_show_warehouse' => '1',
            'receipt_show_customer' => '1',
            'receipt_show_cashier' => '1',
            'receipt_show_sku' => '0',
            'receipt_show_tax' => '1',
            'receipt_show_payments' => '1',
            'receipt_show_barcode' => '1',
            'receipt_auto_print' => '1',
            'receipt_footer_note' => 'Goods once sold are subject to company return policy.',
            'receipt_thank_you' => 'Thank you for your business!',
        ];
        $saved = Setting::pluck('value', 'key')->toArray();
        $receiptSettings = array_merge($defaults, $saved);

        return view('settings.receipt', compact('receiptSettings'));
    }

    public function updateReceipt(Request $r)
    {
        $textKeys = [
            'business_name',
            'business_phone',
            'business_email',
            'business_address',
            'business_tax_pin',
            'receipt_header_title',
            'receipt_paper_size',
            'receipt_footer_note',
            'receipt_thank_you',
        ];
        foreach ($textKeys as $key) {
            if ($r->has($key)) {
                Setting::updateOrCreate(
                    ['key' => $key],
                    ['value' => $r->input($key), 'group' => str_starts_with($key, 'business_') ? 'business' : 'receipt']
                );
            }
        }

        $toggleKeys = [
            'receipt_show_warehouse',
            'receipt_show_customer',
            'receipt_show_cashier',
            'receipt_show_sku',
            'receipt_show_tax',
            'receipt_show_payments',
            'receipt_show_barcode',
            'receipt_auto_print',
        ];
        foreach ($toggleKeys as $key) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $r->boolean($key) ? '1' : '0', 'group' => 'receipt']
            );
        }

        return back()->with('success', 'Receipt settings updated.');
    }

    public function update(Request $r)
    {
        $oldValues = [];
        $newValues = [];

        foreach ($r->except('_token') as $key => $value) {
            $oldValues[$key] = Setting::where('key', $key)->value('value');
            Setting::updateOrCreate(['key' => $key], ['value' => $value, 'group' => 'general']);
            $newValues[$key] = $value;
        }

        if ($newValues !== []) {
            AuditLog::record('settings.updated', newValues: $newValues, oldValues: $oldValues);
        }

        return back()->with('success', 'System settings updated.');
    }

    public function uploadLogo(Request $r)
    {
        $r->validate(['logo' => 'required|image|max:2048']);
        $path = $r->file('logo')->store('logos', 'public');
        Setting::updateOrCreate(['key' => 'business_logo'], ['value' => $path, 'group' => 'business']);
        AuditLog::record('settings.logo_uploaded', newValues: ['path' => $path]);

        return back()->with('success', 'Logo uploaded.');
    }

    public function removeLogo()
    {
        $path = Setting::where('key', 'business_logo')->value('value');
        if ($path) {
            Storage::disk('public')->delete($path);
            Setting::where('key', 'business_logo')->delete();
            AuditLog::record('settings.logo_removed', oldValues: ['path' => $path]);
        }

        return back()->with('success', 'Logo removed.');
    }

    public function staff()
    {
        $users = User::with('role')
            ->where('tenant_id', app(TenantContext::class)->id())
            ->latest()
            ->get();
        $roles = Role::orderBy('name')->get();
        $employees = Employee::latest()->get()->keyBy('user_id');

        return view('settings.staff', compact('users', 'roles', 'employees'));
    }

    public function storeStaff(Request $r, PlanLimits $limits)
    {
        if (! $limits->allows('users')) {
            return back()->withErrors([
                'plan_limit' => 'Your plan allows '.$limits->limitFor('users').' users. Upgrade your plan to add more staff.',
            ])->withInput();
        }

        $data = $r->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role_id' => ['required', Rule::exists('roles', 'id')->where('tenant_id', app(TenantContext::class)->id())],
            'phone' => 'nullable|string|max:50',
            'department' => 'nullable|string|max:100',
            'position' => 'nullable|string|max:100',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role_id' => $data['role_id'],
            'is_active' => true,
        ]);

        $parts = explode(' ', $data['name'], 2);
        Employee::create([
            'user_id' => $user->id,
            'employee_number' => 'EMP-'.str_pad((string) $user->id, 4, '0', STR_PAD_LEFT),
            'first_name' => $parts[0],
            'last_name' => $parts[1] ?? '-',
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'department' => $data['department'] ?? null,
            'position' => $data['position'] ?? null,
            'hire_date' => now()->toDateString(),
            'is_active' => true,
        ]);

        AuditLog::record('staff.created', $user, [
            'email' => $user->email,
            'role_id' => $user->role_id,
        ]);

        return back()->with('success', 'Staff member added.');
    }

    public function toggleStaff(User $user)
    {
        $previousState = $user->is_active;
        $user->is_active = ! $user->is_active;
        $user->save();

        AuditLog::record(
            'staff.toggle',
            $user,
            ['is_active' => $user->is_active],
            ['is_active' => $previousState],
        );

        return back()->with('success', 'Staff status updated.');
    }

    public function currencies()
    {
        $defaultCurrency = Setting::where('key', 'currency')->value('value') ?? 'KES';
        $rawList = Setting::where('key', 'currencies_json')->value('value');
        $currencies = $rawList ? json_decode($rawList, true) : [
            ['code' => 'KES', 'name' => 'Kenyan Shilling', 'symbol' => 'KSh', 'rate' => 1.00],
            ['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$', 'rate' => 0.0077],
            ['code' => 'EUR', 'name' => 'Euro', 'symbol' => '€', 'rate' => 0.0071],
            ['code' => 'GBP', 'name' => 'British Pound', 'symbol' => '£', 'rate' => 0.0061],
        ];

        return view('settings.currencies', compact('currencies', 'defaultCurrency'));
    }

    public function storeCurrency(Request $r)
    {
        $data = $r->validate([
            'code' => 'required|string|max:10',
            'name' => 'required|string|max:100',
            'symbol' => 'required|string|max:10',
            'rate' => 'required|numeric|min:0.000001',
        ]);

        $rawList = Setting::where('key', 'currencies_json')->value('value');
        $currencies = $rawList ? json_decode($rawList, true) : [
            ['code' => 'KES', 'name' => 'Kenyan Shilling', 'symbol' => 'KSh', 'rate' => 1.00],
            ['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$', 'rate' => 0.0077],
            ['code' => 'EUR', 'name' => 'Euro', 'symbol' => '€', 'rate' => 0.0071],
            ['code' => 'GBP', 'name' => 'British Pound', 'symbol' => '£', 'rate' => 0.0061],
        ];

        $code = strtoupper($data['code']);
        $updated = false;
        foreach ($currencies as &$c) {
            if ($c['code'] === $code) {
                $c['name'] = $data['name'];
                $c['symbol'] = $data['symbol'];
                $c['rate'] = (float) $data['rate'];
                $updated = true;
                break;
            }
        }
        if (! $updated) {
            $currencies[] = [
                'code' => $code,
                'name' => $data['name'],
                'symbol' => $data['symbol'],
                'rate' => (float) $data['rate'],
            ];
        }

        Setting::updateOrCreate(
            ['key' => 'currencies_json'],
            ['value' => json_encode($currencies), 'group' => 'currencies_list']
        );

        return back()->with('success', 'Currency saved.');
    }

    public function setDefaultCurrency(Request $r)
    {
        $data = $r->validate(['currency' => 'required|string|max:10']);
        Setting::updateOrCreate(
            ['key' => 'currency'],
            ['value' => strtoupper($data['currency']), 'group' => 'business']
        );

        return back()->with('success', 'Default currency updated.');
    }

    public function warehouses()
    {
        return view('settings.warehouses', ['warehouses' => Warehouse::orderBy('name')->get()]);
    }

    public function storeWarehouse(Request $r, PlanLimits $limits)
    {
        if (! $limits->allows('warehouses')) {
            return back()->withErrors([
                'plan_limit' => 'Your plan allows '.$limits->limitFor('warehouses').' warehouses. Upgrade your plan to add more.',
            ])->withInput();
        }

        $data = $r->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
            'is_default' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);
        if ($r->boolean('is_default')) {
            Warehouse::query()->update(['is_default' => false]);
        }
        $code = $data['code'] ?: strtoupper(substr($data['name'], 0, 4));
        $base = $code;
        $i = 1;
        while (Warehouse::where('code', $code)->exists()) {
            $code = $base.$i++;
        }
        Warehouse::create([
            'name' => $data['name'],
            'code' => $code,
            'address' => $data['address'] ?? null,
            'is_default' => $r->boolean('is_default'),
            'is_active' => $r->boolean('is_active', true),
        ]);

        return back()->with('success', 'Warehouse added.');
    }

    public function updateWarehouse(Request $r, Warehouse $warehouse)
    {
        $data = $r->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
            'is_default' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);
        if ($r->boolean('is_default')) {
            Warehouse::query()->update(['is_default' => false]);
        }
        $code = $data['code'] ?: $warehouse->code;
        $base = $code;
        $i = 1;
        while (Warehouse::where('code', $code)->where('id', '!=', $warehouse->id)->exists()) {
            $code = $base.$i++;
        }
        $warehouse->update([
            'name' => $data['name'],
            'code' => $code,
            'address' => $data['address'] ?? null,
            'is_default' => $r->boolean('is_default'),
            'is_active' => $r->boolean('is_active', true),
        ]);

        return back()->with('success', 'Warehouse updated.');
    }

    public function access()
    {
        $roles = Role::with('permissions')->withCount('users')->orderBy('name')->get();
        $permissions = Permission::orderBy('module')->orderBy('name')->get();

        return view('settings.access', compact('roles', 'permissions'));
    }

    public function storeRole(Request $r)
    {
        $data = $r->validate([
            'name' => 'required|string|max:100|unique:roles,name',
            'description' => 'nullable|string|max:255',
        ]);

        $role = Role::create([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'description' => $data['description'] ?? null,
            'is_system' => false,
        ]);

        AuditLog::record('role.created', $role, ['name' => $role->name, 'slug' => $role->slug]);

        return back()->with('success', 'Role created.');
    }

    public function storePermission(Request $r)
    {
        $data = $r->validate([
            'name' => 'required|string|max:100|unique:permissions,name',
            'module' => 'nullable|string|max:100',
        ]);

        $permission = Permission::create([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'module' => $data['module'] ?? 'General',
        ]);

        AuditLog::record('permission.created', $permission, ['name' => $permission->name, 'slug' => $permission->slug]);

        return back()->with('success', 'Permission created.');
    }

    public function syncRolePermissions(Request $r, Role $role)
    {
        $data = $r->validate([
            'permissions' => 'nullable|array',
            'permissions.*' => [Rule::exists('permissions', 'id')->where('tenant_id', app(TenantContext::class)->id())],
        ]);

        $tenantId = app(TenantContext::class)->id();
        $permissions = collect($data['permissions'] ?? [])
            ->mapWithKeys(fn (int $permissionId): array => [
                $permissionId => ['tenant_id' => $tenantId],
            ])
            ->all();

        $oldPermissions = $role->permissions()->pluck('permissions.id')->all();
        $role->permissions()->sync($permissions);
        $newPermissions = $role->permissions()->pluck('permissions.id')->all();

        AuditLog::record(
            'role.permissions_synced',
            $role,
            ['permissions' => $newPermissions],
            ['permissions' => $oldPermissions],
        );

        return back()->with('success', "Permissions updated for {$role->name}.");
    }
}
