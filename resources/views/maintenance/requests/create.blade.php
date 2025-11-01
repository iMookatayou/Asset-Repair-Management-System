@extends('layouts.app')

@section('title','Create Maintenance')

@section('page-header')
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-xl font-semibold text-slate-800">Create Maintenance Request</h1>
      <p class="text-sm text-slate-500">
        Fill in the details below. Fields marked with <span class="text-rose-600">*</span> are required.
      </p>
    </div>
    <a href="{{ route('maintenance.requests.index') }}"
       class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-emerald-500">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.75 19.5L8.25 12l7.5-7.5" />
      </svg>
      Back
    </a>
  </div>
@endsection

@section('content')
  <div class="max-w-7xl mx-auto">
    {{-- Global error summary --}}
    @if ($errors->any())
      <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-rose-800">
        <p class="font-medium">There were some problems with your submission:</p>
        <ul class="list-disc pl-5 mt-2 space-y-1">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form method="POST" action="{{ route('maintenance.requests.store') }}" class="space-y-6" aria-label="Create maintenance request form">
      @csrf

      @include('maintenance.requests.form', [
        'req'    => null,
        'assets' => $assets,
        'users'  => $users,
      ])

      <div class="flex items-center gap-3 pt-2">
        <button type="submit"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4">
            <path d="M17 3H7a2 2 0 0 0-2 2v14l7-3 7 3V5a2 2 0 0 0-2-2z" />
          </svg>
          Save
        </button>
        <a href="{{ route('maintenance.requests.index') }}" class="text-slate-600 hover:underline">Cancel</a>
      </div>
    </form>
  </div>
@endsection
