<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Point of sale, stock, purchases, and reports for retail shops and wholesalers in Kenya.">
        <meta name="theme-color" content="#145c48">

        <title>{{ config('app.name', 'Point of Sale') }} — POS, Stock & Reports</title>

        <link rel="manifest" href="/manifest.json">
        <link rel="icon" type="image/png" href="/icons/icon-192.png">
        <link rel="apple-touch-icon" href="/icons/icon-192.png">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-[#f5f4ef] text-[#1a1a18] antialiased">
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

            $capabilities = [
                ['number' => '01', 'title' => 'Sell', 'description' => 'Counter sales, invoices, returns, held transactions, and end-of-day cash shifts.'],
                ['number' => '02', 'title' => 'Receive', 'description' => 'Purchase orders, goods received notes, batch and serial numbers, supplier ledgers.'],
                ['number' => '03', 'title' => 'Move stock', 'description' => 'Stock adjustments and transfers between warehouses, with every movement recorded.'],
                ['number' => '04', 'title' => 'Report', 'description' => 'Sales, stock, profit, and ledger reports you can export when you need them.'],
            ];

            $organizations = [
                ['title' => 'Your own workspace', 'description' => 'Products, customers, and staff stay inside your organization. Nothing is shared between businesses.'],
                ['title' => 'Roles that make sense', 'description' => 'Cashiers sell, store managers count stock, and owners see the numbers. Give each person only what they need.'],
                ['title' => 'Room for another branch', 'description' => 'Start with one warehouse. Add locations and stock balances when the business grows.'],
            ];
        @endphp

        <div class="min-h-screen">
            <header class="border-b border-[#dedbd1] bg-[#f5f4ef]">
                <div class="mx-auto flex max-w-6xl items-center justify-between gap-6 px-5 py-5 sm:px-8">
                    <a href="{{ route('home') }}" class="flex items-center gap-2.5" aria-label="{{ config('app.name', 'Point of Sale') }} home">
                        <span class="flex h-9 w-9 items-center justify-center rounded-md bg-[#145c48] text-white">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 4h2l2.2 10.5a2 2 0 0 0 2 1.6h8.1a2 2 0 0 0 2-1.6L21 8H6" />
                                <circle cx="10" cy="20" r="1.3" />
                                <circle cx="18" cy="20" r="1.3" />
                            </svg>
                        </span>
                        <span class="text-[17px] font-extrabold tracking-[-0.02em]">{{ config('app.name', 'Point of Sale') }}</span>
                    </a>

                    <nav class="hidden items-center gap-8 text-sm font-semibold text-[#55524b] md:flex" aria-label="Main navigation">
                        <a href="#features" class="hover:text-[#145c48]">Features</a>
                        <a href="#pricing" class="hover:text-[#145c48]">Pricing</a>
                        <a href="#faq" class="hover:text-[#145c48]">FAQ</a>
                    </nav>

                    <div class="flex items-center gap-2 sm:gap-4">
                        @auth
                            <a href="{{ route('billing.index') }}" class="hidden text-sm font-semibold text-[#55524b] hover:text-[#145c48] sm:inline">Billing</a>
                            <a href="{{ route('dashboard') }}" class="rounded-md bg-[#1a1a18] px-4 py-2.5 text-sm font-bold text-white hover:bg-[#33312c]">Open dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="text-sm font-semibold text-[#55524b] hover:text-[#145c48]">Log in</a>
                            <a href="{{ route('register') }}" class="rounded-md bg-[#1a1a18] px-4 py-2.5 text-sm font-bold text-white hover:bg-[#33312c]">Start free trial</a>
                        @endauth
                    </div>
                </div>
            </header>

            <main>
                <section class="mx-auto grid max-w-6xl items-center gap-14 px-5 pb-20 pt-14 sm:px-8 lg:grid-cols-[0.95fr_1.05fr] lg:gap-16 lg:pb-24 lg:pt-20">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.18em] text-[#145c48]">For shops, supermarkets, and wholesalers</p>

                        <h1 class="mt-5 font-serif text-[2.75rem] font-semibold leading-[1.08] tracking-[-0.03em] sm:text-5xl lg:text-[3.6rem]">
                            A POS that knows what's in your stockroom.
                        </h1>

                        <p class="mt-6 max-w-xl text-[17px] leading-8 text-[#55524b]">
                            Ring up a sale, receive stock from a supplier, print a receipt, and see the day's numbers. Built for businesses that have outgrown notebooks and spreadsheets.
                        </p>

                        <div class="mt-8 flex flex-wrap items-center gap-5">
                            @auth
                                <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 rounded-md bg-[#145c48] px-6 py-3.5 text-sm font-bold text-white hover:bg-[#0f4738]">Open your dashboard <span aria-hidden="true">→</span></a>
                                <a href="{{ route('billing.index') }}" class="text-sm font-bold text-[#1a1a18] underline decoration-[#b8b4aa] underline-offset-4 hover:decoration-[#145c48]">View plans & billing</a>
                            @else
                                <a href="{{ route('register') }}" class="inline-flex items-center gap-2 rounded-md bg-[#145c48] px-6 py-3.5 text-sm font-bold text-white hover:bg-[#0f4738]">Create your account <span aria-hidden="true">→</span></a>
                                <a href="{{ route('login') }}" class="text-sm font-bold text-[#1a1a18] underline decoration-[#b8b4aa] underline-offset-4 hover:decoration-[#145c48]">Sign in</a>
                            @endauth
                        </div>

                        <p class="mt-5 text-[13px] text-[#77736b]">14 days free. No card needed. Plans are billed in {{ $currency }}.</p>

                        <div class="mt-9 flex flex-wrap gap-x-5 gap-y-2 border-t border-[#dedbd1] pt-5 text-xs font-semibold text-[#55524b]">
                            <span>Batch & serial numbers</span>
                            <span class="text-[#c3bfb5]">/</span>
                            <span>Cash shifts</span>
                            <span class="text-[#c3bfb5]">/</span>
                            <span>Multi-warehouse</span>
                        </div>
                    </div>

                    <div class="border border-[#d7d3c9] bg-white">
                        <div class="flex items-center justify-between gap-4 border-b border-[#e8e5dd] px-5 py-3.5">
                            <div class="flex items-center gap-2.5">
                                <span class="h-2 w-2 rounded-full bg-[#145c48]"></span>
                                <span class="text-xs font-bold">Point of sale</span>
                            </div>
                            <div class="flex gap-4 text-[11px] font-semibold text-[#8b877f]">
                                <span>Till 01</span>
                                <span>Cashier: Mary</span>
                            </div>
                        </div>

                        <div class="grid sm:grid-cols-[1.25fr_0.9fr]">
                            <div class="p-5 sm:p-6">
                                <p class="text-[11px] font-bold uppercase tracking-[0.15em] text-[#8b877f]">Current sale</p>

                                <div class="mt-4 divide-y divide-[#efede7]">
                                    <div class="flex items-center justify-between gap-3 py-3">
                                        <div><p class="text-sm font-bold">Atta flour</p><p class="text-[11px] text-[#8b877f]">2 × {{ $currency }} 120.00</p></div>
                                        <span class="font-mono text-sm tabular-nums">{{ $currency }} 240.00</span>
                                    </div>
                                    <div class="flex items-center justify-between gap-3 py-3">
                                        <div><p class="text-sm font-bold">Cooking oil</p><p class="text-[11px] text-[#8b877f]">1 × {{ $currency }} 245.00</p></div>
                                        <span class="font-mono text-sm tabular-nums">{{ $currency }} 245.00</span>
                                    </div>
                                    <div class="flex items-center justify-between gap-3 py-3">
                                        <div><p class="text-sm font-bold">Sugar</p><p class="text-[11px] text-[#8b877f]">3 × {{ $currency }} 55.00</p></div>
                                        <span class="font-mono text-sm tabular-nums">{{ $currency }} 165.00</span>
                                    </div>
                                </div>

                                <div class="mt-5 border border-dashed border-[#d7d3c9] px-3 py-2.5 text-[11px] font-semibold text-[#a09c93]">+ Add an item</div>

                                <div class="mt-5 border-t border-[#1a1a18] pt-3">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-bold">Total</span>
                                        <span class="font-mono text-lg font-bold tabular-nums">{{ $currency }} 650.00</span>
                                    </div>
                                </div>
                            </div>

                            <div class="border-t border-[#e8e5dd] bg-[#faf9f5] p-5 sm:border-l sm:border-t-0 sm:p-6">
                                <p class="text-[11px] font-bold uppercase tracking-[0.15em] text-[#8b877f]">Receipt #INV-2048</p>
                                <div class="mt-4 space-y-2 text-xs">
                                    <div class="flex justify-between"><span class="text-[#77736b]">Cash</span><span class="font-mono tabular-nums">{{ $currency }} 700.00</span></div>
                                    <div class="flex justify-between"><span class="text-[#77736b]">Change</span><span class="font-mono tabular-nums">{{ $currency }} 50.00</span></div>
                                </div>
                                <div class="mt-5 border-t border-[#dedbd1] pt-4">
                                    <div class="flex justify-between text-sm font-bold"><span>Today's sales</span><span class="font-mono tabular-nums">{{ $currency }} 4,820</span></div>
                                    <p class="mt-1 text-[11px] text-[#8b877f]">23 transactions on this till</p>
                                </div>
                                <div class="mt-5 rounded-md bg-[#1a1a18] px-4 py-2.5 text-center text-xs font-bold text-white">Print receipt</div>
                            </div>
                        </div>
                    </div>
                </section>

                <section id="features" class="border-y border-[#dedbd1] bg-white">
                    <div class="mx-auto max-w-6xl px-5 py-20 sm:px-8">
                        <div class="max-w-2xl">
                            <p class="text-xs font-bold uppercase tracking-[0.18em] text-[#145c48]">What you can do on day one</p>
                            <h2 class="mt-4 font-serif text-3xl font-semibold tracking-[-0.02em] sm:text-4xl">The parts of the job that actually take time, handled.</h2>
                        </div>

                        <div class="mt-12 grid gap-x-12 gap-y-0 sm:grid-cols-2">
                            @foreach ($capabilities as $capability)
                                <article class="flex gap-5 border-t border-[#dedbd1] py-7">
                                    <span class="font-mono text-xs font-bold text-[#145c48]">{{ $capability['number'] }}</span>
                                    <div>
                                        <h3 class="text-base font-extrabold">{{ $capability['title'] }}</h3>
                                        <p class="mt-2 max-w-md text-sm leading-7 text-[#55524b]">{{ $capability['description'] }}</p>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </div>
                </section>

                <section class="mx-auto max-w-6xl px-5 py-20 sm:px-8">
                    <div class="grid gap-10 lg:grid-cols-[0.8fr_1.2fr] lg:gap-16">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.18em] text-[#145c48]">One business per workspace</p>
                            <h2 class="mt-4 font-serif text-3xl font-semibold tracking-[-0.02em] sm:text-4xl">Your stock, your people, your numbers.</h2>
                            <p class="mt-5 text-sm leading-7 text-[#55524b]">Each business gets its own organization. You decide who can sell, who can count stock, and who sees the reports.</p>
                        </div>

                        <div class="divide-y divide-[#dedbd1] border-y border-[#dedbd1]">
                            @foreach ($organizations as $organization)
                                <div class="grid gap-3 py-6 sm:grid-cols-[0.7fr_1.3fr] sm:gap-6">
                                    <h3 class="text-sm font-extrabold">{{ $organization['title'] }}</h3>
                                    <p class="text-sm leading-7 text-[#55524b]">{{ $organization['description'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </section>

                <section id="pricing" class="border-y border-[#dedbd1] bg-white">
                    <div class="mx-auto max-w-6xl px-5 py-20 sm:px-8">
                        <div class="max-w-2xl">
                            <p class="text-xs font-bold uppercase tracking-[0.18em] text-[#145c48]">Pricing in {{ $currency }}</p>
                            <h2 class="mt-4 font-serif text-3xl font-semibold tracking-[-0.02em] sm:text-4xl">Plans and limits</h2>
                            <p class="mt-4 text-sm leading-7 text-[#55524b]">Every account starts with 14 days on the Growth plan. Move up or down from billing when your limits need room.</p>
                        </div>

                        <div class="mt-12 grid gap-5 md:grid-cols-2 xl:grid-cols-4">
                            @forelse ($plans as $plan)
                                <article @class([
                                    'flex flex-col border bg-white p-6',
                                    'border-[#145c48]' => $plan->slug === 'growth',
                                    'border-[#dedbd1]' => $plan->slug !== 'growth',
                                ])>
                                    <div class="flex items-baseline justify-between gap-3">
                                        <h3 class="text-lg font-extrabold">{{ $plan->name }}</h3>
                                        @if ($plan->slug === 'growth')
                                            <span class="text-[11px] font-bold uppercase tracking-wider text-[#145c48]">Most chosen</span>
                                        @endif
                                    </div>
                                    <p class="mt-2 min-h-[44px] text-[13px] leading-6 text-[#77736b]">{{ $plan->description }}</p>

                                    <div class="mt-5">
                                        @if ((float) $plan->monthly_price === 0.0)
                                            <p class="text-3xl font-extrabold tracking-[-0.03em]">Free</p>
                                            <p class="mt-1 text-xs text-[#8b877f]">No monthly charge</p>
                                        @else
                                            <p class="text-3xl font-extrabold tracking-[-0.03em]">{{ $currency }} {{ $formatMoney($plan->monthly_price) }}<span class="text-sm font-semibold text-[#8b877f]">/mo</span></p>
                                            <p class="mt-1 text-xs text-[#8b877f]">{{ $currency }} {{ $formatMoney($plan->yearly_price) }} billed yearly</p>
                                        @endif
                                    </div>

                                    <dl class="mt-5 space-y-2 border-y border-[#efede7] py-4 text-xs">
                                        <div class="flex justify-between gap-3"><dt class="text-[#77736b]">Users</dt><dd class="font-bold tabular-nums">{{ $formatLimit($plan->max_users) }}</dd></div>
                                        <div class="flex justify-between gap-3"><dt class="text-[#77736b]">Products</dt><dd class="font-bold tabular-nums">{{ $formatLimit($plan->max_products) }}</dd></div>
                                        <div class="flex justify-between gap-3"><dt class="text-[#77736b]">Warehouses</dt><dd class="font-bold tabular-nums">{{ $formatLimit($plan->max_warehouses) }}</dd></div>
                                        <div class="flex justify-between gap-3"><dt class="text-[#77736b]">Sales / month</dt><dd class="font-bold tabular-nums">{{ $formatLimit($plan->max_sales_per_month) }}</dd></div>
                                    </dl>

                                    <ul class="mt-4 flex-1 space-y-2 text-xs text-[#55524b]">
                                        @forelse ($plan->features ?? [] as $feature)
                                            <li class="flex gap-2"><span class="text-[#145c48]" aria-hidden="true">—</span><span>{{ $featureLabel($feature) }}</span></li>
                                        @empty
                                            <li class="text-[#8b877f]">Core POS and inventory tools.</li>
                                        @endforelse
                                    </ul>

                                    <div class="mt-6 space-y-2">
                                        @auth
                                            <a href="{{ route('billing.index') }}" class="block border border-[#1a1a18] px-4 py-2.5 text-center text-xs font-bold hover:bg-[#f5f4ef]">Manage billing</a>
                                        @else
                                            <a href="{{ route('register') }}" class="block bg-[#1a1a18] px-4 py-2.5 text-center text-xs font-bold text-white hover:bg-[#33312c]">Start with {{ $plan->name }}</a>
                                        @endauth

                                        @if ($whatsappConfigured && isset($whatsappLinks[$plan->id]))
                                            <a href="{{ $whatsappLinks[$plan->id]['monthly'] }}" target="_blank" rel="noopener noreferrer" class="flex items-center justify-center gap-2 bg-[#128a5b] px-4 py-2.5 text-center text-xs font-bold text-white hover:bg-[#0f704a]">
                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12.04 2C6.51 2 2.07 6.43 2.07 11.96c0 1.76.46 3.48 1.34 5L2 22l5.17-1.35a9.92 9.92 0 0 0 4.87 1.24h.01c5.53 0 10.47-4.43 10.47-9.96C22.52 6.43 18.09 2 12.04 2Zm0 18.15a8.18 8.18 0 0 1-4.17-1.14l-.3-.18-3.07.8.82-2.99-.2-.31a8.16 8.16 0 0 1-1.26-4.37c0-4.51 3.68-8.18 8.19-8.18 2.19 0 4.25.85 5.8 2.39a8.11 8.11 0 0 1 2.4 5.8c0 4.51-3.68 8.18-8.21 8.18Zm4.49-6.13c-.24-.12-1.44-.71-1.66-.79-.22-.09-.38-.13-.54.13-.16.25-.62.79-.76.96-.14.16-.28.18-.52.06-.24-.13-1.05-.39-2-1.24-.74-.74-1.21-1.66-1.35-1.9-.14-.25-.15-.52-.15-.75 0-.22.06-.52.25-.8.09-.09.13-.21.19-.36.06-.14.03-.27-.02-.4-.04-.13-.54-1.3-.74-1.78-.19-.46-.39-.4-.54-.4h-.46c-.16 0-.42.06-.64.3-.22.24-.85.83-.85 2.02s.87 2.35.99 2.51c.13.16 1.7 2.6 4.13 3.64.58.25 1.03.4 1.38.51.58.19 1.11.16 1.53.1.47-.07 1.45-.59 1.66-1.16.2-.57.2-1.06.14-1.16-.06-.1-.22-.16-.47-.29Z" /></svg>
                                                Chat about monthly
                                            </a>
                                            <a href="{{ $whatsappLinks[$plan->id]['yearly'] }}" target="_blank" rel="noopener noreferrer" class="block border border-[#128a5b] px-4 py-2.5 text-center text-xs font-bold text-[#0f704a] hover:bg-[#edf7f1]">Ask about yearly</a>
                                        @endif
                                    </div>
                                </article>
                            @empty
                                <div class="col-span-full border border-dashed border-[#d7d3c9] bg-white p-10 text-center text-sm text-[#77736b]">
                                    Plans are being prepared. Please check back shortly.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </section>

                <section id="faq" class="mx-auto max-w-6xl px-5 py-20 sm:px-8">
                    <div class="grid gap-10 lg:grid-cols-[0.8fr_1.2fr] lg:gap-16">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.18em] text-[#145c48]">Before you sign up</p>
                            <h2 class="mt-4 font-serif text-3xl font-semibold tracking-[-0.02em] sm:text-4xl">Questions we get</h2>
                            <p class="mt-5 text-sm leading-7 text-[#55524b]">If something isn't covered here, start a chat from the pricing cards above.</p>
                        </div>

                        <div class="divide-y divide-[#dedbd1] border-y border-[#dedbd1]">
                            <details class="group py-5" open>
                                <summary class="flex cursor-pointer list-none items-center justify-between gap-4 text-sm font-extrabold">How does the 14-day trial work?<span class="text-[#145c48] transition group-open:rotate-45" aria-hidden="true">+</span></summary>
                                <p class="mt-3 max-w-xl text-sm leading-7 text-[#55524b]">Your organization gets full access for 14 days. Set up products, add your team, and try the reports before choosing a plan.</p>
                            </details>
                            <details class="group py-5">
                                <summary class="flex cursor-pointer list-none items-center justify-between gap-4 text-sm font-extrabold">How do I pay for a paid plan?<span class="text-[#145c48] transition group-open:rotate-45" aria-hidden="true">+</span></summary>
                                <p class="mt-3 max-w-xl text-sm leading-7 text-[#55524b]">Use the WhatsApp button on the plan you want. You'll chat with the admin, who arranges payment and activates the plan on your account.</p>
                            </details>
                            <details class="group py-5">
                                <summary class="flex cursor-pointer list-none items-center justify-between gap-4 text-sm font-extrabold">Can I change plans later?<span class="text-[#145c48] transition group-open:rotate-45" aria-hidden="true">+</span></summary>
                                <p class="mt-3 max-w-xl text-sm leading-7 text-[#55524b]">Yes. The billing page shows your current plan, usage, and the other plans you can move to.</p>
                            </details>
                            <details class="group py-5">
                                <summary class="flex cursor-pointer list-none items-center justify-between gap-4 text-sm font-extrabold">Is my stock data separate from other businesses?<span class="text-[#145c48] transition group-open:rotate-45" aria-hidden="true">+</span></summary>
                                <p class="mt-3 max-w-xl text-sm leading-7 text-[#55524b]">Yes. Each business has its own organization, users, products, and stock records.</p>
                            </details>
                        </div>
                    </div>
                </section>

                <section class="border-t border-[#dedbd1] bg-white">
                    <div class="mx-auto flex max-w-6xl flex-col gap-6 px-5 py-14 sm:flex-row sm:items-end sm:justify-between sm:px-8">
                        <div>
                            <h2 class="font-serif text-2xl font-semibold tracking-[-0.02em] sm:text-3xl">Start with a free 14-day trial.</h2>
                            <p class="mt-2 text-sm text-[#55524b]">Set up your first products and make a sale today.</p>
                        </div>
                        <div class="flex flex-wrap gap-4">
                            @auth
                                <a href="{{ route('dashboard') }}" class="rounded-md bg-[#145c48] px-6 py-3 text-sm font-bold text-white hover:bg-[#0f4738]">Open dashboard</a>
                            @else
                                <a href="{{ route('register') }}" class="rounded-md bg-[#145c48] px-6 py-3 text-sm font-bold text-white hover:bg-[#0f4738]">Create your account</a>
                                <a href="{{ route('login') }}" class="text-sm font-bold text-[#1a1a18] underline decoration-[#b8b4aa] underline-offset-4">I already have an account</a>
                            @endauth
                        </div>
                    </div>
                </section>
            </main>

            <footer class="border-t border-[#dedbd1] bg-[#f5f4ef]">
                <div class="mx-auto flex max-w-6xl flex-col gap-6 px-5 py-9 text-xs sm:flex-row sm:items-center sm:justify-between sm:px-8">
                    <p class="font-extrabold">{{ config('app.name', 'Point of Sale') }} <span class="font-normal text-[#8b877f]">· POS, stock, and reports</span></p>
                    <nav class="flex flex-wrap gap-5 font-semibold text-[#55524b]" aria-label="Footer navigation">
                        <a href="#features" class="hover:text-[#145c48]">Features</a>
                        <a href="#pricing" class="hover:text-[#145c48]">Pricing</a>
                        <a href="#faq" class="hover:text-[#145c48]">FAQ</a>
                        @guest
                            <a href="{{ route('login') }}" class="hover:text-[#145c48]">Log in</a>
                            <a href="{{ route('register') }}" class="hover:text-[#145c48]">Sign up</a>
                        @endguest
                    </nav>
                    <p class="text-[#8b877f]">© {{ date('Y') }} {{ config('app.name', 'Point of Sale') }}</p>
                </div>
            </footer>
        </div>
        <x-whatsapp-float />
    </body>
</html>
