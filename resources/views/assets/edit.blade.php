@extends('layouts.app')

@php
  $line = 'border-slate-200';
@endphp

@section('title','Edit Asset')

@section('page-header')
  <div class="w-full bg-slate-50 border-b {{ $line }}">
    <div class="mx-auto max-w-screen-2xl px-4 sm:px-6 lg:px-8 py-5">
      <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">

        {{-- LEFT --}}
        <div class="min-w-0">
          <div class="flex items-start gap-3">
            <span class="mt-0.5 inline-flex h-9 w-9 items-center justify-center rounded-xl text-emerald-700">
              <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M12 20h9" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4 11.5-11.5Z"
                      stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </span>

            <div class="min-w-0">
              <h1 class="text-[20px] sm:text-[22px] font-semibold text-slate-900 leading-tight">
                Edit Asset
                <span class="ml-2 text-slate-500 text-[13px] sm:text-[14px] font-semibold">#{{ $asset->id }}</span>
              </h1>

              <div class="mt-1 text-xs sm:text-[13px] text-slate-600 flex flex-wrap gap-x-4 gap-y-1">
                <span>แก้ไขข้อมูลครุภัณฑ์</span>
                @if($asset->updated_at)
                  <span>อัปเดต: <span class="font-medium text-slate-900">{{ $asset->updated_at->format('Y-m-d H:i') }}</span></span>
                @endif
                <span>
                  รหัส: <span class="font-semibold text-slate-900">{{ $asset->asset_code }}</span>
                </span>
                <span class="truncate">
                  ชื่อ: <span class="font-semibold text-slate-900">{{ $asset->name }}</span>
                </span>
              </div>
            </div>
          </div>
        </div>

        {{-- RIGHT --}}
        <div class="flex flex-wrap items-center justify-start sm:justify-end gap-2">
          <a href="{{ route('assets.index') }}"
             class="inline-flex items-center gap-2 rounded-lg border {{ $line }} bg-white px-4 py-2 text-[13px] font-medium text-slate-700 hover:bg-slate-50">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Back
          </a>
        </div>

      </div>
    </div>
  </div>
@endsection

@section('content')
  <div class="mx-auto max-w-screen-2xl px-4 sm:px-6 lg:px-8 pb-8 pt-6">

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
          action="{{ route('assets.update', $asset) }}"
          class="space-y-8"
          novalidate>
      @csrf
      @method('PUT')

      @include('assets._form', [
        'asset'       => $asset,
        'categories'  => $categories ?? collect(),
        'departments' => $departments ?? collect(),
      ])

      <div class="flex justify-end gap-2 pt-4 border-t {{ $line }}">
        <a href="{{ route('assets.index') }}"
           class="inline-flex items-center justify-center h-11 px-5 rounded-xl border {{ $line }} bg-white
                  text-sm font-medium text-slate-700 hover:bg-slate-50">
          ยกเลิก
        </a>
        <button type="submit"
                class="inline-flex items-center justify-center h-11 px-5 rounded-xl bg-emerald-700
                       text-sm font-medium text-white hover:bg-emerald-800 focus:ring-2 focus:ring-emerald-200">
          บันทึกการแก้ไข
        </button>
      </div>
    </form>
  </div>
@endsection
