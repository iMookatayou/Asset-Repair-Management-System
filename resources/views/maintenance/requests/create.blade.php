@extends('layouts.app')

@section('title','Create Maintenance')

@section('page-header')
  <div class="bg-gradient-to-r from-slate-50 to-slate-100 border-b border-slate-200">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 py-5">
      <div class="flex items-start justify-between gap-4">
        <div>
          <h1 class="text-2xl font-semibold text-slate-900">Create Maintenance</h1>
          <p class="mt-1 text-sm text-slate-600">สร้างคำขอซ่อมใหม่</p>
        </div>

        <a href="{{ route('maintenance.requests.index') }}"
           class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm text-slate-700 hover:bg-slate-50"
           data-no-loader>
          กลับ
        </a>
      </div>
    </div>
  </div>
@endsection

@section('content')
  <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 py-6">

    @if ($errors->any())
      <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 p-4 text-rose-800">
        <ul class="list-disc pl-5 text-sm space-y-1">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form method="POST"
          action="{{ route('maintenance.requests.store') }}"
          enctype="multipart/form-data"
          class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-6"
          novalidate>
      @csrf

      @include('maintenance.requests._form', [
        'req'         => null,
        'assets'      => $assets ?? collect(),
        'depts'       => $depts  ?? collect(),
        'attachments' => [],
      ])

      <div class="flex justify-end gap-2 border-t pt-4">
        <a href="{{ route('maintenance.requests.index') }}"
           class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm text-slate-700 hover:bg-slate-50"
           data-no-loader>
          ยกเลิก
        </a>
        <button type="submit"
                class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
          บันทึก
        </button>
      </div>
    </form>
  </div>
@endsection
