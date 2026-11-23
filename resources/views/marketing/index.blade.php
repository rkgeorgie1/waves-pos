<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="A simple POS, inventory, and reporting platform for retail shops, supermarkets, and growing businesses.">
        <meta name="theme-color" content="#4f46e5">

        <title>{{ config('app.name', 'Point of Sale') }} — POS, Inventory & Reports</title>

        <link rel="manifest" href="/manifest.json">
        <link rel="icon" type="image/png" href="/icons/icon-192.png">
        <link rel="apple-touch-icon" href="/icons/apple-touch-icon.png">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-slate-50 font-sans text-slate-900 antialiased">
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

            $highlights = [
                ['title' => 'Sell without slowing down', 'description' => 'A fast checkout flow for counter sales, invoices, returns, and held transactions.'],
                ['title' => 'Know your stock in real time', 'description' => 'Track products, batches, serial numbers, and stock levels across every warehouse.'],
                ['title' => 'Make decisions with clear reports', 'description' => 'See sales, inventory, profit, and ledger activity in one connected workspace.'],
                ['title' => 'Built for more than one store', 'description' => 'Manage multiple warehouses, suppliers, and teams with role-based access.'],
                ['title' => 'Purchases that stay organized', 'description' => 'Handle purchase orders, goods received notes, returns, and supplier ledgers.'],
                ['title' => 'Ready when your team is', 'description' => 'Create your organization, invite staff, and start your 14-day trial in minutes.'],
            ];
        @endphp

        <div class="relative overflow-hidden">
            <div class="pointer-events-none absolute inset-x-0 top-0 h-[680px] bg-gradient-to-b from-indigo-50 via-sky-50/70 to-slate-50"></div>
            <div class="pointer-events-none absolute -left-40 top-24 h-96 w-96 rounded-full bg-indigo-200/40 blur-3xl"></div>
            <div class="pointer-events-none absolute -right-40 top-10 h-96 w-96 rounded-full bg-cyan-200/40 blur-3xl"></div>

            <header class="relative z-20 mx-auto flex max-w-7xl items-center justify-between gap-6 px-6 py-6 lg:px-8">
                <a href="{{ route('home') }}" class="flex items-center gap-3" aria-label="{{ config('app.name', 'Point of Sale') }} home">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-600 text-white shadow-lg shadow-indigo-600/20">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l2.4 11.2a2 2 0 0 0 2 1.6h7.7a2 2 0 0 0 2-1.6L21 7H6" />
                            <circle cx="10" cy="20" r="1.5" />
                            <circle cx="18" cy="20" r="1.5" />
                        </svg>
                    </span>
                    <span class="text-lg font-extrabold tracking-tight text-slate-950">{{ config('app.name', 'Point of Sale') }}</span>
                </a>

                <nav class="hidden items-center gap-8 text-sm font-semibold text-slate-600 md:flex" aria-label="Main navigation">
                    <a href="#features" class="transition hover:text-indigo-600">Features</a>
                    <a href="#platform" class="transition hover:text-indigo-600">Why us</a>
                    <a href="#pricing" class="transition hover:text-indigo-600">Pricing</a>
                    <a href="#faq" class="transition hover:text-indigo-600">FAQ</a>
                </nav>

                <div class="flex items-center gap-2 sm:gap-3">
                    @auth
                        <a href="{{ route('billing.index') }}" class="hidden rounded-lg px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-white sm:inline-block">Billing</a>
                        <a href="{{ route('dashboard') }}" class="rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800">Open dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="rounded-lg px-3 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-white sm:px-4">Log in</a>
                        <a href="{{ route('register') }}" class="rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-600/20 transition hover:bg-indigo-500">Start free trial</a>
                    @endauth
                </div>
            </header>

            <main>
                <section class="relative mx-auto grid max-w-7xl items-center gap-14 px-6 pb-20 pt-10 lg:grid-cols-[minmax(0,1fr)_minmax(0,1.05fr)] lg:px-8 lg:pb-28 lg:pt-16">
                    <div class="max-w-2xl">
                        <span class="inline-flex items-center gap-2 rounded-full border border-indigo-200 bg-white/80 px-4 py-2 text-xs font-bold uppercase tracking-wider text-indigo-700 shadow-sm backdrop-blur">
                            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                            14-day free trial
                        </span>

                        <h1 class="mt-7 text-4xl font-extrabold leading-[1.1] tracking-tight text-slate-950 sm:text-5xl lg:text-6xl">
                            Run your whole business from
                            <span class="bg-gradient-to-r from-indigo-600 to-cyan-500 bg-clip-text text-transparent">one simple platform.</span>
                        </h1>

                        <p class="mt-6 max-w-xl text-lg leading-8 text-slate-600">
                            Take payments, manage inventory, track suppliers, and understand your numbers — without juggling spreadsheets or disconnected tools.
                        </p>

                        <div class="mt-9 flex flex-col gap-3 sm:flex-row">
                            @auth
                                <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-6 py-4 text-base font-bold text-white shadow-xl shadow-indigo-600/20 transition hover:-translate-y-0.5 hover:bg-indigo-500">
                                    Open your dashboard
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" /></svg>
                                </a>
                                <a href="{{ route('billing.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-6 py-4 text-base font-bold text-slate-800 shadow-sm transition hover:border-indigo-300 hover:text-indigo-700">View plans & billing</a>
                            @else
                                <a href="{{ route('register') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-6 py-4 text-base font-bold text-white shadow-xl shadow-indigo-600/20 transition hover:-translate-y-0.5 hover:bg-indigo-500">
                                    Create your account
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" /></svg>
                                </a>
                                <a href="{{ route('login') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-6 py-4 text-base font-bold text-slate-800 shadow-sm transition hover:border-indigo-300 hover:text-indigo-700">Sign in</a>
                            @endauth
                        </div>

                        <p class="mt-4 text-sm text-slate-500">No credit card required to start. Choose a plan when you're ready.</p>

                        <ul class="mt-9 grid gap-3 text-sm font-semibold text-slate-700 sm:grid-cols-3">
                            <li class="flex items-center gap-2"><span class="flex h-5 w-5 items-center justify-center rounded-full bg-emerald-100 text-emerald-700"><svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7" /></svg></span> Easy setup</li>
                            <li class="flex items-center gap-2"><span class="flex h-5 w-5 items-center justify-center rounded-full bg-emerald-100 text-emerald-700"><svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7" /></svg></span> Secure access</li>
                            <li class="flex items-center gap-2"><span class="flex h-5 w-5 items-center justify-center rounded-full bg-emerald-100 text-emerald-700"><svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7" /></svg></span> Grow anytime</li>
                        </ul>
                    </div>

                    <div class="relative mx-auto w-full max-w-2xl lg:mx-0">
                        <div class="absolute -inset-5 rounded-[2.5rem] bg-indigo-500/10 blur-2xl"></div>
                        <div class="relative overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl shadow-slate-900/10">
                            <div class="flex items-center gap-2 border-b border-slate-200 bg-slate-50 px-5 py-4">
                                <span class="h-3 w-3 rounded-full bg-rose-300"></span>
                                <span class="h-3 w-3 rounded-full bg-amber-300"></span>
                                <span class="h-3 w-3 rounded-full bg-emerald-300"></span>
                                <span class="ml-4 text-xs font-semibold text-slate-400">Dashboard overview</span>
                            </div>

                            <div class="grid sm:grid-cols-[132px_1fr]">
                                <aside class="hidden flex-col gap-2 border-r border-slate-200 bg-slate-50 p-4 text-xs font-semibold text-slate-500 sm:flex">
                                    <span class="rounded-lg bg-indigo-600 px-3 py-2 text-white">Overview</span>
                                    <span class="rounded-lg px-3 py-2">Point of sale</span>
                                    <span class="rounded-lg px-3 py-2">Products</span>
                                    <span class="rounded-lg px-3 py-2">Inventory</span>
                                    <span class="rounded-lg px-3 py-2">Sales</span>
                                    <span class="rounded-lg px-3 py-2">Purchases</span>
                                    <span class="rounded-lg px-3 py-2">Reports</span>
                                </aside>

                                <div class="p-5 sm:p-6">
                                    <div class="flex items-center justify-between gap-4">
                                        <div>
                                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Today</p>
                                            <p class="mt-1 text-xl font-extrabold text-slate-900">Business snapshot</p>
                                        </div>
                                        <span class="rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700">All systems good</span>
                                    </div>

                                    <div class="mt-6 grid grid-cols-3 gap-3">
                                        <div class="rounded-xl border border-slate-200 p-3">
                                            <p class="text-[11px] font-semibold text-slate-400">Sales</p>
                                            <p class="mt-2 text-lg font-extrabold text-slate-900">{{ $currency }} 4,820</p>
                                            <p class="mt-1 text-[11px] font-bold text-emerald-600">+12.4%</p>
                                        </div>
                                        <div class="rounded-xl border border-slate-200 p-3">
                                            <p class="text-[11px] font-semibold text-slate-400">Products</p>
                                            <p class="mt-2 text-lg font-extrabold text-slate-900">1,248</p>
                                            <p class="mt-1 text-[11px] font-semibold text-slate-400">In stock</p>
                                        </div>
                                        <div class="rounded-xl border border-slate-200 p-3">
                                            <p class="text-[11px] font-semibold text-slate-400">Profit</p>
                                            <p class="mt-2 text-lg font-extrabold text-slate-900">{{ $currency }} 1,390</p>
                                            <p class="mt-1 text-[11px] font-bold text-emerald-600">+8.1%</p>
                                        </div>
                                    </div>

                                    <div class="mt-5 grid gap-5 lg:grid-cols-[1.3fr_1fr]">
                                        <div class="rounded-xl border border-slate-200 p-4">
                                            <div class="flex items-center justify-between">
                                                <p class="text-sm font-bold text-slate-800">Recent sales</p>
                                                <span class="text-[11px] font-semibold text-indigo-600">View all</span>
                                            </div>
                                            <div class="mt-4 space-y-3">
                                                <div class="flex items-center justify-between border-b border-slate-100 pb-2 text-xs"><span class="font-semibold text-slate-600">#INV-2048</span><span class="font-bold text-slate-900">{{ $currency }} 245.00</span></div>
                                                <div class="flex items-center justify-between border-b border-slate-100 pb-2 text-xs"><span class="font-semibold text-slate-600">#INV-2047</span><span class="font-bold text-slate-900">{{ $currency }} 118.50</span></div>
                                                <div class="flex items-center justify-between border-b border-slate-100 pb-2 text-xs"><span class="font-semibold text-slate-600">#INV-2046</span><span class="font-bold text-slate-900">{{ $currency }} 620.00</span></div>
                                                <div class="flex items-center justify-between text-xs"><span class="font-semibold text-slate-600">#INV-2045</span><span class="font-bold text-slate-900">{{ $currency }} 89.90</span></div>
                                            </div>
                                        </div>

                                        <div class="rounded-xl border border-slate-200 p-4">
                                            <p class="text-sm font-bold text-slate-800">Sales trend</p>
                                            <div class="mt-5 flex h-28 items-end justify-between gap-2">
                                                <div class="h-8 flex-1 rounded-t bg-indigo-200"></div>
                                                <div class="h-12 flex-1 rounded-t bg-indigo-200"></div>
                                                <div class="h-9 flex-1 rounded-t bg-indigo-200"></div>
                                                <div class="h-16 flex-1 rounded-t bg-indigo-300"></div>
                                                <div class="h-11 flex-1 rounded-t bg-indigo-200"></div>
                                                <div class="h-20 flex-1 rounded-t bg-indigo-500"></div>
                                                <div class="h-14 flex-1 rounded-t bg-indigo-300"></div>
                                            </div>
                                            <p class="mt-3 text-center text-[11px] font-semibold text-slate-400">Last 7 days</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section id="features" class="relative mx-auto max-w-7xl px-6 py-20 lg:px-8">
                    <div class="mx-auto max-w-2xl text-center">
                        <p class="text-sm font-bold uppercase tracking-wider text-indigo-600">Everything connected</p>
                        <h2 class="mt-3 text-3xl font-extrabold tracking-tight text-slate-950 sm:text-4xl">The tools your business needs, in one place</h2>
                        <p class="mt-4 text-lg leading-8 text-slate-600">From the first sale to the final report, every part of your operation works together.</p>
                    </div>

                    <div class="mt-14 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                        @foreach ($highlights as $highlight)
                            <article class="group rounded-2xl border border-slate-200 bg-white p-7 shadow-sm transition hover:-translate-y-1 hover:border-indigo-200 hover:shadow-xl hover:shadow-slate-900/5">
                                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-50 text-sm font-extrabold text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white">{{ $loop->iteration }}</span>
                                <h3 class="mt-5 text-lg font-bold text-slate-950">{{ $highlight['title'] }}</h3>
                                <p class="mt-3 text-sm leading-7 text-slate-600">{{ $highlight['description'] }}</p>
                            </article>
                        @endforeach
                    </div>
                </section>

                <section id="platform" class="border-y border-slate-200 bg-white">
                    <div class="mx-auto grid max-w-7xl items-center gap-12 px-6 py-20 lg:grid-cols-2 lg:px-8">
                        <div>
                            <p class="text-sm font-bold uppercase tracking-wider text-indigo-600">Built for growing teams</p>
                            <h2 class="mt-3 text-3xl font-extrabold tracking-tight text-slate-950 sm:text-4xl">Simple enough for a small shop. Strong enough for a growing chain.</h2>
                            <p class="mt-5 text-lg leading-8 text-slate-600">Start with one location and add warehouses, products, and staff as you grow. Your data stays organized under one organization.</p>

                            <ul class="mt-8 space-y-4">
                                <li class="flex gap-3"><span class="mt-1 flex h-6 w-6 flex-none items-center justify-center rounded-full bg-indigo-100 text-indigo-700"><svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7" /></svg></span><span class="text-slate-700"><strong class="text-slate-950">Separate organizations.</strong> Each merchant gets its own workspace and data.</span></li>
                                <li class="flex gap-3"><span class="mt-1 flex h-6 w-6 flex-none items-center justify-center rounded-full bg-indigo-100 text-indigo-700"><svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7" /></svg></span><span class="text-slate-700"><strong class="text-slate-950">Role-based access.</strong> Give every team member the right level of access.</span></li>
                                <li class="flex gap-3"><span class="mt-1 flex h-6 w-6 flex-none items-center justify-center rounded-full bg-indigo-100 text-indigo-700"><svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7" /></svg></span><span class="text-slate-700"><strong class="text-slate-950">Plans that scale.</strong> Move to a higher plan when your limits need room.</span></li>
                            </ul>
                        </div>

                        <div class="rounded-3xl bg-gradient-to-br from-slate-900 via-slate-800 to-indigo-900 p-8 text-white shadow-2xl sm:p-10">
                            <p class="text-sm font-bold uppercase tracking-wider text-indigo-300">One connected workflow</p>
                            <div class="mt-8 space-y-4">
                                <div class="flex items-center gap-4 rounded-2xl border border-white/10 bg-white/5 p-4"><span class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-500/20 text-indigo-200">01</span><div><p class="font-bold">Create your organization</p><p class="text-sm text-slate-300">Your workspace is ready in minutes.</p></div></div>
                                <div class="flex items-center gap-4 rounded-2xl border border-white/10 bg-white/5 p-4"><span class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-500/20 text-indigo-200">02</span><div><p class="font-bold">Add products and warehouses</p><p class="text-sm text-slate-300">Set prices, stock levels, and access.</p></div></div>
                                <div class="flex items-center gap-4 rounded-2xl border border-white/10 bg-white/5 p-4"><span class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-500/20 text-indigo-200">03</span><div><p class="font-bold">Start selling and reporting</p><p class="text-sm text-slate-300">Every transaction updates your numbers.</p></div></div>
                            </div>
                        </div>
                    </div>
                </section>

                <section id="pricing" class="mx-auto max-w-7xl px-6 py-20 lg:px-8">
                    <div class="mx-auto max-w-2xl text-center">
                        <p class="text-sm font-bold uppercase tracking-wider text-indigo-600">Simple pricing</p>
                        <h2 class="mt-3 text-3xl font-extrabold tracking-tight text-slate-950 sm:text-4xl">Choose the plan that fits today</h2>
                        <p class="mt-4 text-lg leading-8 text-slate-600">Every new account starts with a 14-day trial. Upgrade, downgrade, or manage your plan later.</p>
                    </div>

                    <div class="mt-14 grid gap-6 md:grid-cols-2 xl:grid-cols-4">
                        @forelse ($plans as $plan)
                            <article @class([
                                'relative flex flex-col rounded-3xl border bg-white p-7 shadow-sm',
                                'border-indigo-500 ring-2 ring-indigo-500/20 shadow-xl shadow-indigo-600/10' => $plan->slug === 'growth',
                                'border-slate-200' => $plan->slug !== 'growth',
                            ])>
                                @if ($plan->slug === 'growth')
                                    <span class="absolute -top-3 left-7 rounded-full bg-indigo-600 px-3 py-1 text-xs font-bold text-white">Most popular</span>
                                @endif

                                <h3 class="text-xl font-extrabold text-slate-950">{{ $plan->name }}</h3>
                                <p class="mt-2 min-h-[48px] text-sm leading-6 text-slate-600">{{ $plan->description }}</p>

                                <div class="mt-6">
                                    @if ((float) $plan->monthly_price === 0.0)
                                        <p class="text-4xl font-extrabold tracking-tight text-slate-950">Free</p>
                                        <p class="mt-1 text-sm text-slate-500">No monthly charge</p>
                                    @else
                                        <p class="text-4xl font-extrabold tracking-tight text-slate-950">{{ $currency }} {{ $formatMoney($plan->monthly_price) }}<span class="text-base font-semibold text-slate-500">/mo</span></p>
                                        <p class="mt-1 text-sm text-slate-500">{{ $currency }} {{ $formatMoney($plan->yearly_price) }} billed yearly</p>
                                    @endif
                                </div>

                                <dl class="mt-6 space-y-2.5 border-y border-slate-100 py-5 text-sm">
                                    <div class="flex items-center justify-between gap-3"><dt class="text-slate-500">Users</dt><dd class="font-bold text-slate-900">{{ $formatLimit($plan->max_users) }}</dd></div>
                                    <div class="flex items-center justify-between gap-3"><dt class="text-slate-500">Products</dt><dd class="font-bold text-slate-900">{{ $formatLimit($plan->max_products) }}</dd></div>
                                    <div class="flex items-center justify-between gap-3"><dt class="text-slate-500">Warehouses</dt><dd class="font-bold text-slate-900">{{ $formatLimit($plan->max_warehouses) }}</dd></div>
                                    <div class="flex items-center justify-between gap-3"><dt class="text-slate-500">Sales / month</dt><dd class="font-bold text-slate-900">{{ $formatLimit($plan->max_sales_per_month) }}</dd></div>
                                </dl>

                                <ul class="mt-5 flex-1 space-y-3 text-sm text-slate-700">
                                    @forelse ($plan->features ?? [] as $feature)
                                        <li class="flex items-start gap-2.5">
                                            <span class="mt-0.5 flex h-5 w-5 flex-none items-center justify-center rounded-full bg-emerald-100 text-emerald-700"><svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7" /></svg></span>
                                            <span>{{ $featureLabel($feature) }}</span>
                                        </li>
                                    @empty
                                        <li class="text-slate-500">Core POS and inventory tools.</li>
                                    @endforelse
                                </ul>

                                <div class="mt-7 space-y-2">
                                    @auth
                                        <a href="{{ route('billing.index') }}" class="block rounded-xl border border-slate-300 bg-white px-4 py-3 text-center text-sm font-bold text-slate-800 transition hover:border-indigo-400 hover:text-indigo-700">Manage billing</a>
                                    @else
                                        <a href="{{ route('register') }}" class="block rounded-xl bg-slate-900 px-4 py-3 text-center text-sm font-bold text-white transition hover:bg-indigo-600">Start with {{ $plan->name }}</a>
                                    @endauth

                                    @if ($whatsappConfigured && isset($whatsappLinks[$plan->id]))
                                        <a href="{{ $whatsappLinks[$plan->id]['monthly'] }}" target="_blank" rel="noopener noreferrer" class="flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-3 text-center text-sm font-bold text-white transition hover:bg-emerald-700">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12.04 2C6.51 2 2.07 6.43 2.07 11.96c0 1.76.46 3.48 1.34 5L2 22l5.17-1.35a9.92 9.92 0 0 0 4.87 1.24h.01c5.53 0 10.47-4.43 10.47-9.96C22.52 6.43 18.09 2 12.04 2Zm0 18.15a8.18 8.18 0 0 1-4.17-1.14l-.3-.18-3.07.8.82-2.99-.2-.31a8.16 8.16 0 0 1-1.26-4.37c0-4.51 3.68-8.18 8.19-8.18 2.19 0 4.25.85 5.8 2.39a8.11 8.11 0 0 1 2.4 5.8c0 4.51-3.68 8.18-8.21 8.18Zm4.49-6.13c-.24-.12-1.44-.71-1.66-.79-.22-.09-.38-.13-.54.13-.16.25-.62.79-.76.96-.14.16-.28.18-.52.06-.24-.13-1.05-.39-2-1.24-.74-.74-1.21-1.66-1.35-1.9-.14-.25-.15-.52-.15-.75 0-.22.06-.52.25-.8.09-.09.13-.21.19-.36.06-.14.03-.27-.02-.4-.04-.13-.54-1.3-.74-1.78-.19-.46-.39-.4-.54-.4h-.46c-.16 0-.42.06-.64.3-.22.24-.85.83-.85 2.02s.87 2.35.99 2.51c.13.16 1.7 2.6 4.13 3.64.58.25 1.03.4 1.38.51.58.19 1.11.16 1.53.1.47-.07 1.45-.59 1.66-1.16.2-.57.2-1.06.14-1.16-.06-.1-.22-.16-.47-.29Z" /></svg>
                                            Chat about monthly
                                        </a>
                                        <a href="{{ $whatsappLinks[$plan->id]['yearly'] }}" target="_blank" rel="noopener noreferrer" class="block rounded-xl border border-emerald-300 bg-white px-4 py-3 text-center text-sm font-bold text-emerald-700 transition hover:border-emerald-500 hover:bg-emerald-50">Ask about yearly</a>
                                    @endif
                                </div>
                            </article>
                        @empty
                            <div class="col-span-full rounded-3xl border border-dashed border-slate-300 bg-white p-10 text-center text-slate-600">
                                Plans are being prepared. Please check back shortly.
                            </div>
                        @endforelse
                    </div>
                </section>

                <section id="faq" class="border-t border-slate-200 bg-white">
                    <div class="mx-auto grid max-w-7xl gap-12 px-6 py-20 lg:grid-cols-[0.8fr_1.2fr] lg:px-8">
                        <div>
                            <p class="text-sm font-bold uppercase tracking-wider text-indigo-600">Questions</p>
                            <h2 class="mt-3 text-3xl font-extrabold tracking-tight text-slate-950 sm:text-4xl">A few things you might want to know</h2>
                            <p class="mt-4 text-slate-600">Still unsure where to begin? Start the trial and explore the platform with your own data.</p>
                        </div>

                        <div class="divide-y divide-slate-200 rounded-3xl border border-slate-200 bg-white px-6">
                            <details class="group py-6" open>
                                <summary class="flex cursor-pointer items-center justify-between gap-4 font-bold text-slate-900">How does the free trial work?<span class="text-indigo-600 transition group-open:rotate-45">+</span></summary>
                                <p class="mt-3 text-sm leading-7 text-slate-600">Your new organization starts with 14 days of access. You can set everything up and invite your team before choosing a paid plan.</p>
                            </details>
                            <details class="group py-6">
                                <summary class="flex cursor-pointer items-center justify-between gap-4 font-bold text-slate-900">Can I change plans later?<span class="text-indigo-600 transition group-open:rotate-45">+</span></summary>
                                <p class="mt-3 text-sm leading-7 text-slate-600">Yes. The billing page shows your current usage and available plans so you can move to a plan that fits as your business grows.</p>
                            </details>
                            <details class="group py-6">
                                <summary class="flex cursor-pointer items-center justify-between gap-4 font-bold text-slate-900">Is my business data separate from other merchants?<span class="text-indigo-600 transition group-open:rotate-45">+</span></summary>
                                <p class="mt-3 text-sm leading-7 text-slate-600">Yes. Each merchant gets its own organization and workspace, with access controls for the people you invite.</p>
                            </details>
                        </div>
                    </div>
                </section>

                <section class="mx-auto max-w-7xl px-6 py-20 lg:px-8">
                    <div class="relative overflow-hidden rounded-[2rem] bg-gradient-to-br from-indigo-700 via-indigo-600 to-cyan-600 px-6 py-14 text-center text-white shadow-2xl shadow-indigo-600/20 sm:px-12">
                        <div class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full border border-white/20"></div>
                        <div class="pointer-events-none absolute -bottom-24 -left-10 h-72 w-72 rounded-full border border-white/10"></div>
                        <h2 class="relative text-3xl font-extrabold tracking-tight sm:text-4xl">Ready to bring your business together?</h2>
                        <p class="relative mx-auto mt-4 max-w-xl text-lg text-indigo-50">Create your account today and see how much easier daily operations can be.</p>
                        <div class="relative mt-8 flex flex-col justify-center gap-3 sm:flex-row">
                            @auth
                                <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center rounded-xl bg-white px-6 py-4 font-bold text-indigo-700 transition hover:bg-indigo-50">Open dashboard</a>
                            @else
                                <a href="{{ route('register') }}" class="inline-flex items-center justify-center rounded-xl bg-white px-6 py-4 font-bold text-indigo-700 transition hover:bg-indigo-50">Create your account</a>
                                <a href="{{ route('login') }}" class="inline-flex items-center justify-center rounded-xl border border-white/40 px-6 py-4 font-bold text-white transition hover:bg-white/10">I already have an account</a>
                            @endauth
                        </div>
                    </div>
                </section>
            </main>

            <footer class="border-t border-slate-200 bg-white">
                <div class="mx-auto flex max-w-7xl flex-col gap-6 px-6 py-10 sm:flex-row sm:items-center sm:justify-between lg:px-8">
                    <div>
                        <p class="font-extrabold text-slate-950">{{ config('app.name', 'Point of Sale') }}</p>
                        <p class="mt-1 text-sm text-slate-500">POS, inventory, and reporting for growing businesses.</p>
                    </div>
                    <nav class="flex flex-wrap gap-6 text-sm font-semibold text-slate-600" aria-label="Footer navigation">
                        <a href="#features" class="hover:text-indigo-600">Features</a>
                        <a href="#pricing" class="hover:text-indigo-600">Pricing</a>
                        <a href="#faq" class="hover:text-indigo-600">FAQ</a>
                        @guest
                            <a href="{{ route('login') }}" class="hover:text-indigo-600">Log in</a>
                            <a href="{{ route('register') }}" class="hover:text-indigo-600">Sign up</a>
                        @endguest
                    </nav>
                    <p class="text-sm text-slate-400">© {{ date('Y') }} {{ config('app.name', 'Point of Sale') }}</p>
                </div>
            </footer>
        </div>
        <x-whatsapp-float />
    </body>
</html>
