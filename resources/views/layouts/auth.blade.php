<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sign in') • PPK Hospital System</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-[#0E2B51] bg-gradient-to-b from-[#0E2B51] to-[#123860] text-slate-800">
<main class="min-h-screen flex items-center justify-center p-5">
    <div class="w-full max-w-md">

        {{-- Card --}}
        <div class="rounded-2xl border border-slate-200 shadow-[0_10px_32px_-12px_rgba(2,6,23,0.45)] overflow-hidden">

            {{-- Header (ขาว) --}}
            <div class="px-8 pt-8 pb-5 text-center bg-white">
                <div class="mx-auto w-20 h-20 rounded-full bg-white ring-1 ring-slate-200 shadow flex items-center justify-center mb-4">
                    <img src="{{ asset('images/logoppk.png') }}" class="w-16 h-16 object-contain" alt="PPK Logo">
                </div>
                <h1 class="text-[18px] font-semibold text-slate-800">PPK Asset Repair</h1>
                <p class="text-xs text-slate-600 tracking-wide">Hospital Information Service</p>
            </div>

            {{-- Divider --}}
            <div class="h-px bg-slate-200"></div>

            {{-- Content (ขาว) --}}
            <div class="px-8 py-6 bg-white">
                @yield('content')
            </div>
        </div>

        <p class="text-center text-[11px] text-slate-200 mt-6">
            &copy; {{ date('Y') }} PPK Hospital IT
        </p>
    </div>
</main>
</body>
</html>
