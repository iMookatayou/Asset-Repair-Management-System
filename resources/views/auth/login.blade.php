@extends('layouts.auth')
@section('title', 'Sign in')

@section('content')
    @if (session('status'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        {{-- Citizen ID --}}
        <div>
            <label for="citizen_id" class="block text-sm font-medium text-slate-700">
                CID
            </label>
            <input id="citizen_id"
                   type="text"
                   name="citizen_id"
                   value="{{ old('citizen_id') }}"
                   required
                   autofocus
                   maxlength="13"
                   autocomplete="username"
                   class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2
                          focus:border-[#0E2B51] focus:ring-[#0E2B51]">
            @error('citizen_id')
                <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Password --}}
        <div>
            <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
            <input id="password" type="password" name="password" required autocomplete="current-password"
                   class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2
                          focus:border-[#0E2B51] focus:ring-[#0E2B51]">
            @error('password')
                <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Remember + Forgot --}}
        <div class="mt-2 flex items-center justify-between">
            <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                <input type="checkbox" name="remember"
                       class="rounded border-slate-300 text-[#0E2B51] focus:ring-[#0E2B51]">
                Remember me
            </label>

            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="text-sm text-[#0E2B51] hover:underline">
                    Forgot password?
                </a>
            @endif
        </div>

        {{-- Submit --}}
        <button class="mt-2 w-full h-11 rounded-lg bg-[#0E2B51] text-white font-medium
                       hover:opacity-95 focus:ring-2 focus:ring-offset-2 focus:ring-[#0E2B51]">
            Sign in
        </button>

        {{-- Register (optional) --}}
        @if (Route::has('register'))
            <p class="text-center text-sm text-slate-600">
                Don’t have an account?
                <a href="{{ route('register') }}" class="text-[#0E2B51] hover:underline">Register</a>
            </p>
        @endif
    </form>
@endsection
