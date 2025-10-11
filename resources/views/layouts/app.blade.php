{{-- resources/views/components/app-layout.blade.php --}}
@include('layouts.app', [
  'slot'   => $slot,
  'header' => $header ?? null,
  'title'  => $title ?? null,
])
