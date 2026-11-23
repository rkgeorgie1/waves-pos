<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\WhatsApp;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class MarketingController extends Controller
{
    /**
     * Show the public landing page with available subscription plans.
     */
    public function index(): View
    {
        $plans = Schema::hasTable('plans')
            ? Plan::query()
                ->where('is_active', true)
                ->orderBy('monthly_price')
                ->get()
            : collect();

        $currency = $plans->first()?->currency ?? config('cashier.currency', 'KES');
        $whatsappConfigured = WhatsApp::configured();

        $whatsappLinks = $whatsappConfigured
            ? $plans
                ->filter(fn (Plan $plan): bool => (float) $plan->monthly_price > 0)
                ->mapWithKeys(fn (Plan $plan): array => [
                    $plan->id => [
                        'monthly' => WhatsApp::chatUrl(WhatsApp::paymentMessage($plan, 'monthly')),
                        'yearly' => WhatsApp::chatUrl(WhatsApp::paymentMessage($plan, 'yearly')),
                    ],
                ])
            : collect();

        return view('marketing.index', [
            'plans' => $plans,
            'currency' => strtoupper((string) $currency),
            'whatsappConfigured' => $whatsappConfigured,
            'whatsappLinks' => $whatsappLinks,
            'trialDays' => (int) config('app.trial_days'),
        ]);
    }
}
