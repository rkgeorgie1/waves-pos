<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="manifest" href="/manifest.json">
        <link rel="icon" type="image/png" href="/icons/icon-192.png">
        <link rel="apple-touch-icon" href="/icons/apple-touch-icon.png">
        <meta name="theme-color" content="#4f46e5">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @php
            try {
                $guestLogoPath = trim((string) (\App\Models\Setting::where('key', 'business_logo')->value('value') ?? ''));
            } catch (\Throwable $exception) {
                $guestLogoPath = '';
            }
            $guestLogoUrl = '';
            if ($guestLogoPath !== '') {
                $guestLogoUrl = str_starts_with($guestLogoPath, 'http://') || str_starts_with($guestLogoPath, 'https://')
                    ? $guestLogoPath
                    : (str_starts_with($guestLogoPath, '/') ? $guestLogoPath : asset('storage/'.$guestLogoPath));
            }
        @endphp

        <style>
            :root {
                --auth-blue: #3b82f6;
                --auth-blue-dark: #2563eb;
                --auth-ink: #0f172a;
                --auth-muted: #64748b;
            }

            body.auth-body {
                margin: 0;
                min-height: 100vh;
                color: var(--auth-ink);
                font-family: 'Figtree', ui-sans-serif, system-ui, -apple-system, 'Segoe UI', sans-serif;
                -webkit-font-smoothing: antialiased;
            }

            .auth-shell {
                display: flex;
                align-items: center;
                justify-content: center;
                min-height: 100vh;
                padding: 40px 24px;
                background:
                    radial-gradient(circle at 14% 18%, rgba(59, 130, 246, 0.12), transparent 30%),
                    radial-gradient(circle at 86% 82%, rgba(59, 130, 246, 0.08), transparent 28%),
                    #eef2f7;
            }

            .auth-frame {
                display: grid;
                grid-template-columns: minmax(300px, 0.85fr) minmax(0, 1.15fr);
                width: 100%;
                max-width: 980px;
                min-height: 560px;
                overflow: hidden;
                border: 1px solid #e2e8f0;
                border-radius: 24px;
                background: #ffffff;
                box-shadow: 0 30px 80px rgba(15, 23, 42, 0.12);
            }

            .auth-brand {
                position: relative;
                display: flex;
                align-items: center;
                justify-content: center;
                overflow: hidden;
                padding: 48px;
                background: var(--auth-blue);
            }

            .auth-brand::before {
                content: '';
                position: absolute;
                width: 420px;
                height: 420px;
                top: -170px;
                right: -190px;
                border: 1px solid rgba(255, 255, 255, 0.2);
                border-radius: 50%;
            }

            .auth-brand::after {
                content: '';
                position: absolute;
                inset: 0;
                background: repeating-linear-gradient(135deg, rgba(255, 255, 255, 0.045) 0 1px, transparent 1px 18px);
                pointer-events: none;
            }

            .auth-brand-inner {
                position: relative;
                z-index: 1;
                display: flex;
                justify-content: center;
                width: 100%;
            }

            .auth-logo {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-width: 200px;
                max-width: 280px;
                padding: 18px 22px;
                border-radius: 16px;
                background: #ffffff;
                box-shadow: 0 14px 34px rgba(15, 23, 42, 0.18);
            }

            .auth-logo img {
                display: block;
                max-width: 100%;
                max-height: 48px;
                height: auto;
                object-fit: contain;
            }

            .auth-logo svg {
                width: 40px;
                height: 40px;
                color: var(--auth-blue);
                fill: currentColor;
            }

            .auth-main {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                padding: 64px;
                background: #ffffff;
            }

            .auth-mobile-brand {
                display: none;
            }

            .auth-card {
                width: 100%;
                max-width: 380px;
            }

            .auth-card h2 {
                margin: 0 0 28px;
                font-size: 28px;
                font-weight: 700;
                letter-spacing: -0.01em;
                color: var(--auth-ink);
            }

            .auth-label {
                display: block;
                margin-bottom: 8px;
                font-size: 13px;
                font-weight: 600;
                color: #334155;
            }

            .auth-input {
                width: 100%;
                padding: 13px 14px !important;
                border: 1px solid #cbd5e1 !important;
                border-radius: 10px !important;
                background: #ffffff;
                color: var(--auth-ink);
                font-size: 14px;
                box-shadow: none !important;
                transition: border-color 0.15s ease, box-shadow 0.15s ease;
            }

            .auth-input:focus {
                border-color: var(--auth-blue) !important;
                box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15) !important;
                outline: none;
            }

            .auth-input::placeholder {
                color: #94a3b8;
            }

            .auth-actions-row {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
                margin: 20px 0 26px;
            }

            .auth-remember {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                font-size: 13px;
                color: #475569;
                cursor: pointer;
            }

            .auth-checkbox {
                width: 16px;
                height: 16px;
                margin: 0;
                border: 1px solid #cbd5e1;
                border-radius: 5px;
                accent-color: var(--auth-blue);
                cursor: pointer;
            }

            .auth-link {
                font-size: 13px;
                font-weight: 600;
                color: var(--auth-blue-dark);
                text-decoration: none;
            }

            .auth-link:hover {
                text-decoration: underline;
            }

            .auth-submit {
                width: 100%;
                padding: 14px 16px;
                border: 0;
                border-radius: 10px;
                background: var(--auth-blue);
                color: #ffffff;
                font-size: 14px;
                font-weight: 700;
                cursor: pointer;
                box-shadow: 0 8px 18px rgba(59, 130, 246, 0.25);
                transition: background 0.15s ease, transform 0.15s ease;
            }

            .auth-submit:hover {
                background: var(--auth-blue-dark);
            }

            .auth-submit:active {
                transform: translateY(1px);
            }

            .auth-submit:focus-visible {
                outline: 3px solid rgba(59, 130, 246, 0.35);
                outline-offset: 2px;
            }

            @media (max-width: 900px) {
                .auth-shell {
                    display: block;
                    min-height: 100vh;
                    padding: 32px 16px 24px;
                    background: #f8fafc;
                }

                .auth-frame {
                    display: block;
                    max-width: 420px;
                    min-height: 0;
                    border: 0;
                    border-radius: 0;
                    background: transparent;
                    box-shadow: none;
                }

                .auth-brand {
                    display: none;
                }

                .auth-main {
                    padding: 0;
                }

                .auth-mobile-brand {
                    display: block;
                    margin-bottom: 22px;
                    text-align: center;
                }

                .auth-card {
                    padding: 28px 22px;
                    border: 1px solid #e2e8f0;
                    border-radius: 18px;
                    background: #ffffff;
                    box-shadow: 0 20px 50px rgba(15, 23, 42, 0.08);
                }
            }
        </style>
    </head>
    <body class="auth-body">
        <div class="auth-shell">
            <div class="auth-frame">
                <aside class="auth-brand">
                    <div class="auth-brand-inner">
                        <div class="auth-logo">
                            @if($guestLogoUrl !== '')
                                <img src="{{ $guestLogoUrl }}" alt="{{ config('app.name', 'Waves Store') }} logo">
                            @else
                                <x-application-logo />
                            @endif
                        </div>
                    </div>
                </aside>

                <main class="auth-main">
                    <div class="auth-mobile-brand">
                        <div class="auth-logo">
                            @if($guestLogoUrl !== '')
                                <img src="{{ $guestLogoUrl }}" alt="{{ config('app.name', 'Waves Store') }} logo">
                            @else
                                <x-application-logo />
                            @endif
                        </div>
                    </div>

                    <div class="auth-card">
                        {{ $slot }}
                    </div>
                </main>
            </div>
        </div>
        <x-whatsapp-float />
    </body>
</html>
