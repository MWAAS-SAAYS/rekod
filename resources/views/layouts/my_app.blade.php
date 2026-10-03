<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <meta name="description" content="REKOD — National Industrial Attachment & Placement System">
    <meta name="author" content="Rekod Portal">

    <title>@yield('title', 'REKOD — Central Placement System')</title>

    <!-- Executive Typography: Serif Elegance (Playfair) & Crisp Modern Sans (Plus Jakarta) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,400&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- FontAwesome & Styles -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @include('layouts.partials.styles')

    <meta name="msapplication-TileColor" content="#0b1329">
    <meta name="theme-color" content="#0b1329">

    <!-- Open Graph / Meta -->
    <meta property="og:title" content="REKOD — Central Placement System">
    <meta property="og:description" content="Official National Industrial Attachment & Student Placement System">
    <meta property="og:type" content="website">

    @include('layouts.partials.scripts')

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-serif-header { font-family: 'Playfair Display', Georgia, serif; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased selection:bg-amber-100 selection:text-amber-900 flex flex-col min-h-screen">

    <!-- TOP EXECUTIVE ANNOUNCEMENT & STATUS BAR -->
    <div class="bg-[#0b1329] text-slate-300 text-[11px] font-medium tracking-wide border-b border-amber-500/20 py-2 px-4 z-30">
        <div class="max-w-full mx-auto flex justify-between items-center px-2 sm:px-6">
            <div class="flex items-center space-x-3">
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/20">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400 mr-1.5 shadow-[0_0_6px_rgba(251,191,36,0.9)] animate-pulse"></span> Official Portal
                </span>
                <span class="text-slate-600 hidden sm:inline">|</span>
                <span class="text-slate-300 text-[11px] font-medium hidden sm:inline tracking-wider uppercase">Republic Attachment & Placement Network</span>
            </div>
            <div class="flex items-center space-x-4 text-slate-400 text-[11px]">
                <span class="hidden md:inline"><i class="far fa-calendar-alt text-amber-400/80 mr-1.5"></i>{{ now()->format('l, d F Y') }}</span>
                <span class="text-slate-700 hidden md:inline">|</span>
                <span class="text-amber-300/90 font-medium"><i class="fas fa-shield-alt mr-1"></i> Verified Access</span>
            </div>
        </div>
    </div>

    <!-- HEADER PARTIAL -->
    @include('layouts.partials.header')

    <!-- MAIN BODY CONTAINER -->
    <div class="flex overflow-hidden bg-slate-50 pt-14 min-h-screen">

        <!-- SIDEBAR PARTIAL -->
        @include('layouts.partials.sidebar')

        <!-- SIDEBAR BACKDROP -->
        <div class="bg-slate-950/60 backdrop-blur-sm hidden fixed inset-0 z-10 transition-opacity" id="sidebarBackdrop"></div>

        <!-- MAIN CONTENT AREA -->
        <div id="main-content" class="h-full w-full bg-slate-50 relative overflow-y-auto lg:ml-64 flex flex-col justify-between">
            <main class="flex-1 bg-slate-50 overflow-auto px-4 sm:px-6 py-6">
                
                <!-- SYSTEM FLASH ALERTS -->
                @if (session('success'))
                    <div class="mb-6 p-4 rounded-lg bg-emerald-950 text-emerald-200 border-l-4 border-emerald-400 flex items-center justify-between shadow-sm">
                        <div class="flex items-center space-x-3">
                            <i class="fas fa-check-circle text-emerald-400 text-lg"></i>
                            <span class="text-xs font-semibold tracking-wide uppercase">{{ session('success') }}</span>
                        </div>
                        <button onclick="this.parentElement.remove()" class="text-emerald-400 hover:text-emerald-200"><i class="fas fa-times"></i></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-6 p-4 rounded-lg bg-rose-950 text-rose-200 border-l-4 border-rose-400 flex items-center justify-between shadow-sm">
                        <div class="flex items-center space-x-3">
                            <i class="fas fa-exclamation-triangle text-rose-400 text-lg"></i>
                            <span class="text-xs font-semibold tracking-wide uppercase">{{ session('error') }}</span>
                        </div>
                        <button onclick="this.parentElement.remove()" class="text-rose-400 hover:text-rose-200"><i class="fas fa-times"></i></button>
                    </div>
                @endif

                <!-- MAIN PAGE CONTENT CARD -->
                <div class="flex flex-col rounded-xl border border-slate-200/80 bg-white p-6 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.03)]" style="min-height: calc(100vh - 12rem);">
                    @yield('content')
                </div>
            </main>

            <!-- FOOTER PARTIAL -->
            @include('layouts.partials.footer')
        </div>

    </div>

    <script async defer src="https://buttons.github.io/buttons.js"></script>
    <script src="https://themewagon.github.io/windster/app.bundle.js"></script>
</body>
</html>