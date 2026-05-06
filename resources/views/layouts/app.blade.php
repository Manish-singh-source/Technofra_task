<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Google Leads')</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 text-slate-900">
    <div class="min-h-screen">
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            @yield('content')
        </main>
    </div>
</body>
</html>
