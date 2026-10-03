<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'REKOD — Authentication')</title>

    <!-- Executive Typography -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- FontAwesome & Styling -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="min-h-screen flex flex-col items-center justify-center bg-slate-950 p-4 sm:p-6 antialiased selection:bg-amber-100 selection:text-amber-900">

    <!-- BRAND / LOGO HEADER -->
    <div class="mb-6 text-center">
        <span class="text-2xl font-bold tracking-tight text-white">
            REKOD<span class="text-amber-400">.</span>
        </span>
        <p class="text-xs text-slate-400 mt-1 uppercase tracking-wider font-semibold">National Industrial Attachment & Placement System</p>
    </div>

    <!-- MAIN AUTH CARD -->
    <div class="w-full max-w-md bg-white p-8 rounded-2xl shadow-2xl border border-slate-100 relative overflow-hidden">
        
        <!-- SYSTEM STATUS / SESSION MESSAGES -->
        @if (session('status'))
            <div class="mb-4 p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-medium flex items-center">
                <i class="fas fa-check-circle mr-2 text-emerald-600"></i>
                {{ session('status') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-4 p-3 rounded-lg bg-rose-50 border border-rose-200 text-rose-800 text-xs font-medium flex items-center">
                <i class="fas fa-exclamation-circle mr-2 text-rose-600"></i>
                {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </div>

    <!-- FOOTER -->
    <p class="text-center text-xs text-slate-500 mt-8">
        &copy; {{ date('Y') }} Republic Attachment & Placement Network. All rights reserved.
    </p>

</body>
</html>