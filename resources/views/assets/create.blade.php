@extends('layouts.app')

@php
  $line = 'border-slate-200';
@endphp

@section('title','Create Asset')

@section('page-header')
  <div class="bg-slate-50 border-b {{ $line }}">
    <div class="mx-auto max-w-screen-2xl px-3 sm:px-6 lg:px-8 py-4">
      <div class="flex items-start justify-between gap-4">
        <div>
          <h1 class="text-xl font-semibold text-slate-900">Create Asset</h1>
          <p class="text-sm text-slate-600">สร้างครุภัณฑ์ใหม่</p>
        </div>

        <a href="{{ route('assets.index') }}"
           class="inline-flex items-center gap-2 h-10 px-4 rounded-lg
                  border {{ $line }} bg-white
                  text-sm font-medium text-slate-700 hover:bg-slate-50 whitespace-nowrap">
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
            <path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          Back
        </a>
      </div>
    </div>
  </div>
@endsection

@section('content')
  <div class="page-tight mx-auto max-w-screen-2xl px-3 sm:px-6 lg:px-8 pt-6 pb-8">

    {{-- Error (เหมือน Maintenance) --}}
    @if ($errors->any())
      <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 p-4 text-rose-800">
        <ul class="list-disc pl-5 text-sm space-y-1">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form method="POST"
          action="{{ route('assets.store') }}"
          class="space-y-8"
          novalidate>
      @csrf

      @include('assets._form', [
        'asset'       => new \App\Models\Asset(),
        'categories'  => $categories ?? collect(),
        'departments' => $departments ?? collect(),
      ])

      {{-- Footer actions (เหมือน Maintenance) --}}
      <div class="flex justify-end gap-2 pt-4 border-t {{ $line }}">
        <a href="{{ route('assets.index') }}"
           class="inline-flex items-center justify-center
                  h-11 px-5 rounded-xl
                  border {{ $line }} bg-white
                  text-sm font-medium text-slate-700
                  hover:bg-slate-50">
          ยกเลิก
        </a>

        <button type="submit"
                class="inline-flex items-center justify-center
                       h-11 px-5 rounded-xl
                       bg-emerald-700
                       text-sm font-medium text-white
                       hover:bg-emerald-800
                       focus:outline-none focus:ring-2 focus:ring-emerald-200">
          บันทึก
        </button>
      </div>
    </form>
  </div>
@endsection
