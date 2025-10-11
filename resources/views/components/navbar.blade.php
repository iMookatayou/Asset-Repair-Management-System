@props(['header' => ''])

<div class="topbar px-4 py-3 flex items-center gap-3">
  <button id="btnSidebar"
          class="lg:hidden inline-flex items-center px-2 py-1 rounded border border-zinc-700"
          aria-controls="side" aria-expanded="false">☰</button>

  <div class="font-semibold">{{ config('app.name','Asset Repair') }}</div>

  <div class="ml-auto flex items-center gap-3 text-sm">
    {!! $header !!}
  </div>
</div>
