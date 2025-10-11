@props(['title' => '', 'value' => '-', 'hint' => null])

<div class="rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-5 shadow-sm">
  <div class="text-sm text-zinc-500 dark:text-zinc-400">{{ $title }}</div>
  <div class="mt-1 text-3xl font-bold text-zinc-900 dark:text-white">
    {{ $value }}
    @if($hint) <span class="ml-1 text-xs font-normal text-zinc-500 dark:text-zinc-400">{{ $hint }}</span> @endif
  </div>
</div>
