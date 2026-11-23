<?php

namespace App;

use App\Models\Plan;
use App\Models\Setting;
use Illuminate\Support\Facades\Schema;

class WhatsApp
{
    public const GLOBAL_TENANT_ID = 0;

    private const NUMBER_SETTING_KEY = 'whatsapp_number';

    private const COUNTRY_CODE_SETTING_KEY = 'whatsapp_country_code';

    public static function configured(): bool
    {
        return filled(self::setting(self::NUMBER_SETTING_KEY));
    }

    public static function setting(string $key, ?string $default = null): ?string
    {
        if (! Schema::hasTable('settings')) {
            return $default;
        }

        $value = Setting::withoutGlobalScopes()
            ->where('tenant_id', self::GLOBAL_TENANT_ID)
            ->where('key', $key)
            ->value('value');

        return filled($value) ? (string) $value : $default;
    }

    public static function number(): string
    {
        $number = preg_replace('/\D+/', '', self::setting(self::NUMBER_SETTING_KEY) ?? '') ?? '';
        $countryCode = preg_replace('/\D+/', '', self::countryCode()) ?? '';

        if (str_starts_with($number, '0') && $countryCode !== '') {
            return $countryCode.substr($number, 1);
        }

        return $number;
    }

    public static function countryCode(): string
    {
        return self::setting(self::COUNTRY_CODE_SETTING_KEY, '254') ?? '254';
    }

    /**
     * @param  array{whatsapp_number?: mixed, whatsapp_country_code?: mixed}  $values
     */
    public static function saveSettings(array $values): void
    {
        foreach ([
            self::NUMBER_SETTING_KEY => $values['whatsapp_number'] ?? null,
            self::COUNTRY_CODE_SETTING_KEY => $values['whatsapp_country_code'] ?? null,
        ] as $key => $value) {
            $storedValue = filled($value) ? trim((string) $value) : null;

            Setting::withoutGlobalScopes()->updateOrCreate(
                ['key' => $key, 'tenant_id' => self::GLOBAL_TENANT_ID],
                ['value' => $storedValue, 'group' => 'platform']
            );
        }
    }

    public static function chatUrl(?string $message = null): string
    {
        $url = 'https://wa.me/'.self::number();

        return filled($message) ? $url.'?text='.rawurlencode($message) : $url;
    }

    public static function paymentMessage(Plan $plan, string $interval, ?string $tenantName = null): string
    {
        $price = $interval === 'yearly' ? $plan->yearly_price : $plan->monthly_price;
        $currency = $plan->currency ?: 'KES';

        $message = "Hi, I'd like to pay for the {$plan->name} plan ({$interval}) - {$currency} ".number_format((float) $price, 2).'.';

        if (filled($tenantName)) {
            $message .= " Organization: {$tenantName}.";
        }

        return $message;
    }
}
