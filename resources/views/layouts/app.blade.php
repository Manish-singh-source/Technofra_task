<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Google Leads')</title>
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- PWA manifest and theme metadata --}}
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#0f172a">
    <meta name="mobile-web-app-capable" content="yes">

    {{-- iOS Safari PWA support --}}
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="{{ config('app.name', 'Technofra CRM') }}">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('icons/apple-touch-icon-180x180.png') }}">

    @vite(['resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-900">
    <div class="min-h-screen">
        {{-- Install app controls and iOS install guidance --}}
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4">
            <div class="flex items-center justify-end gap-2">
                <button
                    id="pwa-install-btn"
                    type="button"
                    class="hidden rounded-md bg-slate-900 px-3 py-2 text-sm font-medium text-white hover:bg-slate-800"
                >
                    Install App
                </button>
                <span
                    id="pwa-installed-badge"
                    class="hidden rounded-md bg-emerald-600 px-3 py-2 text-sm font-medium text-white"
                >
                    Installed
                </span>
            </div>

            <div
                id="ios-install-banner"
                class="hidden mt-3 rounded-md border border-sky-200 bg-sky-50 px-3 py-2 text-sm text-sky-700"
            >
                Tap Share -&gt; Add to Home Screen
            </div>
        </div>

        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            @yield('content')
        </main>
    </div>

    {{-- Small toast shown after app installation --}}
    <div
        id="pwa-installed-toast"
        class="hidden fixed bottom-4 right-4 rounded-md bg-emerald-600 px-4 py-2 text-sm text-white shadow-lg"
        role="status"
        aria-live="polite"
    >
        Installed
    </div>
</body>
</html>
