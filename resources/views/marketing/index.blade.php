<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="POS, inventory, purchases, and reporting for retail shops and wholesalers.">
        <meta name="theme-color" content="#0f172a">

        <title>{{ config('app.name', 'Point of Sale') }} — POS, Inventory & Reports</title>

        <link rel="manifest" href="/manifest.json">
        <link rel="icon" type="image/png" href="/icons/icon-192.png">
        <link rel="apple-touch-icon" href="/icons/icon-192.png">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-white font-sans text-slate-900 antialiased">
        @php
            $formatMoney = static fn (mixed $amount): string => number_format((float) $amount, 2);
            $formatLimit = static fn (mixed $value): string => $value === null ? 'Unlimited' : number_format((float) $value);
            $featureLabel = static function (string $feature): string {
                return match ($feature) {
                    'pos' => 'Point of sale',
                    'basic_reports' => 'Basic reports',
                    'multi_warehouse' => 'Multi-warehouse',
                    'priority_support' => 'Priority support',
                    default => \Illuminate\Support\Str::headline(str_replace('_', ' ', $feature)),
                };
            };

            $features = [
                ['name' => 'Point of sale', 'description' => 'Counter sales, invoices, returns, held transactions, and cash shifts.'],
                ['name' => 'Products and stock', 'description' => 'Products, variants, batches, serial numbers, and stock balances by warehouse.'],
                ['name' => 'Purchases', 'description' => 'Purchase orders, goods received notes, supplier returns, and supplier ledgers.'],
                ['name' => 'Reports', 'description' => 'Sales, stock, profit, and ledger reports. Export to PDF or Excel.'],
                ['name' => 'Users and permissions', 'description' => 'Roles and permissions for cashiers, managers, and owners.'],
                ['name' => 'Separate organizations', 'description' => 'Each business has its own workspace, users, products, and records.'],
            ];
        @endphp

        <div class="min-h-screen">
            <header class="border-b border-slate-200">
                <div class="mx-auto flex max-w-6xl items-center justify-between gap-6 px-5 py-4 sm:px-8">
                    <a href="{{ route('home') }}" class="flex items-center gap-2.5" aria-label="{{ config('app.name', 'Point of Sale') }} home">
                        <span class="flex h-8 w-8 items-center justify-center rounded bg-slate-900 text-white">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 4h2l2.2 10.5a2 2 0 0 0 2 1.6h8.1a2 2 0 0 0 2-1.6L21 8H6" />
                                <circle cx="10" cy="20" r="1.3" />
                                <circle cx="18" cy="20" r="1.3" />
                            </svg>
                        </span>
                        <span class="text-base font-bold">{{ config('app.name', 'Point of Sale') }}</span>
                    </a>

                    <nav class="hidden items-center gap-7 text-sm text-slate-600 md:flex" aria-label="Main navigation">
                        <a href="#features" class="hover:text-slate-900">Features</a>
                        <a href="#pricing" class="hover:text-slate-900">Pricing</a>
                        <a href="#faq" class="hover:text-slate-900">FAQ</a>
                    </nav>

                    <div class="flex items-center gap-4">
                        @auth
                            <a href="{{ route('billing.index') }}" class="hidden text-sm text-slate-600 hover:text-slate-900 sm:inline">Billing</a>
                            <a href="{{ route('dashboard') }}" class="rounded bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">Open dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="text-sm text-slate-600 hover:text-slate-900">Log in</a>
                            <a href="{{ route('register') }}" class="rounded bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">Start free trial</a>
                        @endauth
                    </div>
                </div>
            </header>

            <main>
                <section class="mx-auto grid max-w-6xl items-center gap-12 px-5 py-16 sm:px-8 lg:grid-cols-2 lg:py-20">
                    <div>
                        <h1 class="text-3xl font-bold leading-tight tracking-tight sm:text-4xl">POS, inventory, and reporting for retail shops and wholesalers</h1>

                        <p class="mt-5 text-base leading-7 text-slate-600">
                            Record sales, track stock across warehouses, manage suppliers and purchases, and run reports from one account. Every new account gets 14 days free.
                        </p>

                        <div class="mt-7 flex flex-wrap items-center gap-4">
                            @auth
                                <a href="{{ route('dashboard') }}" class="rounded bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white hover:bg-slate-800">Open dashboard</a>
                                <a href="{{ route('billing.index') }}" class="text-sm font-medium text-slate-700 underline underline-offset-4">View plans and billing</a>
                            @else
                                <a href="{{ route('register') }}" class="rounded bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white hover:bg-slate-800">Create your account</a>
                                <a href="{{ route('login') }}" class="text-sm font-medium text-slate-700 underline underline-offset-4">Sign in</a>
                            @endauth
                        </div>

                        <p class="mt-4 text-sm text-slate-500">No credit card required.</p>
                    </div>

                    <div class="border border-slate-200">
                        <div class="flex items-center justify-between border-b border-slate-200 bg-slate-50 px-4 py-3">
                            <span class="text-sm font-semibold">Dashboard</span>
                            <span class="text-xs text-slate-500">Today</span>
                        </div>

                        <div class="grid grid-cols-3 divide-x divide-slate-200 border-b border-slate-200">
                            <div class="p-4">
                                <p class="text-xs text-slate-500">Sales</p>
                                <p class="mt-1 text-lg font-semibold tabular-nums">{{ $currency }} 4,820</p>
                            </div>
                            <div class="p-4">
                                <p class="text-xs text-slate-500">Products</p>
                                <p class="mt-1 text-lg font-semibold tabular-nums">1,248</p>
                            </div>
                            <div class="p-4">
                                <p class="text-xs text-slate-500">Warehouses</p>
                                <p class="mt-1 text-lg font-semibold tabular-nums">3</p>
                            </div>
                        </div>

                        <div class="p-4">
                            <p class="text-sm font-semibold">Recent sales</p>
                            <div class="mt-3 divide-y divide-slate-100 text-sm">
                                <div class="flex justify-between py-2"><span class="text-slate-600">#INV-2048</span><span class="font-medium tabular-nums">{{ $currency }} 245.00</span></div>
                                <div class="flex justify-between py-2"><span class="text-slate-600">#INV-2047</span><span class="font-medium tabular-nums">{{ $currency }} 118.50</span></div>
                                <div class="flex justify-between py-2"><span class="text-slate-600">#INV-2046</span><span class="font-medium tabular-nums">{{ $currency }} 620.00</span></div>
                                <div class="flex justify-between py-2"><span class="text-slate-600">#INV-2045</span><span class="font-medium tabular-nums">{{ $currency }} 89.90</span></div>
                            </div>
                        </div>
                    </div>
                </section>

                <section id="features" class="border-y border-slate-200 bg-slate-50">
                    <div class="mx-auto max-w-6xl px-5 py-16 sm:px-8">
                        <h2 class="text-2xl font-bold tracking-tight">Features</h2>
                        <p class="mt-2 text-sm text-slate-600">The modules included in every account.</p>

                        <div class="mt-8 grid gap-x-12 md:grid-cols-2">
                            @foreach ($features as $feature)
                                <div class="border-t border-slate-200 py-5">
                                    <h3 class="text-sm font-semibold">{{ $feature['name'] }}</h3>
                                    <p class="mt-1.5 text-sm leading-6 text-slate-600">{{ $feature['description'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </section>

                <section id="pricing" class="mx-auto max-w-6xl px-5 py-16 sm:px-8">
                    <h2 class="text-2xl font-bold tracking-tight">Plans</h2>
                    <p class="mt-2 text-sm text-slate-600">Prices in {{ $currency }}. Every account starts with a 14-day trial.</p>

                    <div class="mt-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                        @forelse ($plans as $plan)
                            <div class="flex flex-col border border-slate-200 p-5">
                                <h3 class="font-semibold">{{ $plan->name }}</h3>
                                <p class="mt-1 min-h-[40px] text-sm text-slate-600">{{ $plan->description }}</p>

                                <div class="mt-4">
                                    @if ((float) $plan->monthly_price === 0.0)
                                        <p class="text-2xl font-bold">Free</p>
                                        <p class="mt-1 text-xs text-slate-500">No monthly charge</p>
                                    @else
                                        <p class="text-2xl font-bold">{{ $currency }} {{ $formatMoney($plan->monthly_price) }}<span class="text-sm font-normal text-slate-500">/mo</span></p>
                                        <p class="mt-1 text-xs text-slate-500">{{ $currency }} {{ $formatMoney($plan->yearly_price) }} billed yearly</p>
                                    @endif
                                </div>

                                <dl class="mt-4 space-y-1.5 border-y border-slate-200 py-4 text-xs">
                                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Users</dt><dd class="font-medium tabular-nums">{{ $formatLimit($plan->max_users) }}</dd></div>
                                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Products</dt><dd class="font-medium tabular-nums">{{ $formatLimit($plan->max_products) }}</dd></div>
                                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Warehouses</dt><dd class="font-medium tabular-nums">{{ $formatLimit($plan->max_warehouses) }}</dd></div>
                                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Sales / month</dt><dd class="font-medium tabular-nums">{{ $formatLimit($plan->max_sales_per_month) }}</dd></div>
                                </dl>

                                <ul class="mt-4 flex-1 space-y-1.5 text-xs text-slate-600">
                                    @forelse ($plan->features ?? [] as $feature)
                                        <li>{{ $featureLabel($feature) }}</li>
                                    @empty
                                        <li class="text-slate-500">Core POS and inventory tools.</li>
                                    @endforelse
                                </ul>

                                <div class="mt-5 space-y-2">
                                    @auth
                                        <a href="{{ route('billing.index') }}" class="block border border-slate-900 px-3 py-2 text-center text-xs font-semibold hover:bg-slate-50">Manage billing</a>
                                    @else
                                        <a href="{{ route('register') }}" class="block bg-slate-900 px-3 py-2 text-center text-xs font-semibold text-white hover:bg-slate-800">Start with {{ $plan->name }}</a>
                                    @endauth

                                    @if ($whatsappConfigured && isset($whatsappLinks[$plan->id]))
                                        <a href="{{ $whatsappLinks[$plan->id]['monthly'] }}" target="_blank" rel="noopener noreferrer" class="flex items-center justify-center gap-2 bg-emerald-600 px-3 py-2 text-center text-xs font-semibold text-white hover:bg-emerald-700">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12.04 2C6.51 2 2.07 6.43 2.07 11.96c0 1.76.46 3.48 1.34 5L2 22l5.17-1.35a9.92 9.92 0 0 0 4.87 1.24h.01c5.53 0 10.47-4.43 10.47-9.96C22.52 6.43 18.09 2 12.04 2Zm0 18.15a8.18 8.18 0 0 1-4.17-1.14l-.3-.18-3.07.8.82-2.99-.2-.31a8.16 8.16 0 0 1-1.26-4.37c0-4.51 3.68-8.18 8.19-8.18 2.19 0 4.25.85 5.8 2.39a8.11 8.11 0 0 1 2.4 5.8c0 4.51-3.68 8.18-8.21 8.18Zm4.49-6.13c-.24-.12-1.44-.71-1.66-.79-.22-.09-.38-.13-.54.13-.16.25-.62.79-.76.96-.14.16-.28.18-.52.06-.24-.13-1.05-.39-2-1.24-.74-.74-1.21-1.66-1.35-1.9-.14-.25-.15-.52-.15-.75 0-.22.06-.52.25-.8.09-.09.13-.21.19-.36.06-.14.03-.27-.02-.4-.04-.13-.54-1.3-.74-1.78-.19-.46-.39-.4-.54-.4h-.46c-.16 0-.42.06-.64.3-.22.24-.85.83-.85 2.02s.87 2.35.99 2.51c.13.16 1.7 2.6 4.13 3.64.58.25 1.03.4 1.38.51.58.19 1.11.16 1.53.1.47-.07 1.45-.59 1.66-1.16.2-.57.2-1.06.14-1.16-.06-.1-.22-.16-.47-.29Z" /></svg>
                                            Chat about monthly
                                        </a>
                                        <a href="{{ $whatsappLinks[$plan->id]['yearly'] }}" target="_blank" rel="noopener noreferrer" class="block border border-emerald-600 px-3 py-2 text-center text-xs font-semibold text-emerald-700 hover:bg-emerald-50">Ask about yearly</a>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="col-span-full border border-dashed border-slate-300 p-8 text-center text-sm text-slate-500">
                                Plans are being prepared. Please check back shortly.
                            </div>
                        @endforelse
                    </div>
                </section>

                <section id="faq" class="border-t border-slate-200 bg-slate-50">
                    <div class="mx-auto grid max-w-6xl gap-10 px-5 py-16 sm:px-8 lg:grid-cols-[0.7fr_1.3fr]">
                        <div>
                            <h2 class="text-2xl font-bold tracking-tight">Frequently asked questions</h2>
                        </div>

                        <div class="divide-y divide-slate-200 border-y border-slate-200">
                            <details class="group py-4" open>
                                <summary class="flex cursor-pointer list-none items-center justify-between gap-4 text-sm font-semibold">How does the free trial work?<span class="text-slate-500 transition group-open:rotate-45" aria-hidden="true">+</span></summary>
                                <p class="mt-2 text-sm leading-6 text-slate-600">Your organization has full access for 14 days. You can add products, set up warehouses, and invite staff before choosing a plan.</p>
                            </details>
                            <details class="group py-4">
                                <summary class="flex cursor-pointer list-none items-center justify-between gap-4 text-sm font-semibold">How do I pay for a plan?<span class="text-slate-500 transition group-open:rotate-45" aria-hidden="true">+</span></summary>
                                <p class="mt-2 text-sm leading-6 text-slate-600">Use the WhatsApp button on the plan you want. You chat with the admin, who arranges payment and activates the plan on your account.</p>
                            </details>
                            <details class="group py-4">
                                <summary class="flex cursor-pointer list-none items-center justify-between gap-4 text-sm font-semibold">Can I change plans later?<span class="text-slate-500 transition group-open:rotate-45" aria-hidden="true">+</span></summary>
                                <p class="mt-2 text-sm leading-6 text-slate-600">Yes. The billing page shows your current plan, usage, and the other plans you can move to.</p>
                            </details>
                            <details class="group py-4">
                                <summary class="flex cursor-pointer list-none items-center justify-between gap-4 text-sm font-semibold">Is data separated between businesses?<span class="text-slate-500 transition group-open:rotate-45" aria-hidden="true">+</span></summary>
                                <p class="mt-2 text-sm leading-6 text-slate-600">Yes. Each organization has its own users, products, customers, and records.</p>
                            </details>
                        </div>
                    </div>
                </section>

                <section class="border-t border-slate-200">
                    <div class="mx-auto flex max-w-6xl flex-col gap-5 px-5 py-12 sm:flex-row sm:items-center sm:justify-between sm:px-8">
                        <div>
                            <h2 class="text-xl font-bold tracking-tight">Get started</h2>
                            <p class="mt-1 text-sm text-slate-600">Create an account and start your 14-day trial.</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-4">
                            @auth
                                <a href="{{ route('dashboard') }}" class="rounded bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white hover:bg-slate-800">Open dashboard</a>
                            @else
                                <a href="{{ route('register') }}" class="rounded bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white hover:bg-slate-800">Create your account</a>
                                <a href="{{ route('login') }}" class="text-sm font-medium text-slate-700 underline underline-offset-4">I already have an account</a>
                            @endauth
                        </div>
                    </div>
                </section>
            </main>

            <footer class="border-t border-slate-200">
                <div class="mx-auto flex max-w-6xl flex-col gap-5 px-5 py-8 text-sm sm:flex-row sm:items-center sm:justify-between sm:px-8">
                    <p class="font-semibold">{{ config('app.name', 'Point of Sale') }}</p>
                    <nav class="flex flex-wrap gap-5 text-slate-600" aria-label="Footer navigation">
                        <a href="#features" class="hover:text-slate-900">Features</a>
                        <a href="#pricing" class="hover:text-slate-900">Pricing</a>
                        <a href="#faq" class="hover:text-slate-900">FAQ</a>
                        @guest
                            <a href="{{ route('login') }}" class="hover:text-slate-900">Log in</a>
                            <a href="{{ route('register') }}" class="hover:text-slate-900">Sign up</a>
                        @endguest
                    </nav>
                    <p class="text-slate-500">© {{ date('Y') }} {{ config('app.name', 'Point of Sale') }}</p>
                </div>
            </footer>
        </div>
        <x-whatsapp-float />
    </body>
</html>
