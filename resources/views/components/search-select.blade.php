@props([
  'name',
  'id' => null,
  'items' => collect(),
  'labelField' => 'name',
  'valueField' => 'id',
  'value' => null,
  'placeholder' => '— ไม่ระบุ —',
  'variant' => 'dropdown',
  'inline' => true,
])

@php
  $id = $id ?: $name;
  $items = $items instanceof \Illuminate\Support\Collection ? $items : collect($items);
  $value = old($name, $value);
@endphp

<x-searchable-select
  :name="$name"
  :id="$id"
  :items="$items"
  :label-field="$labelField"
  :value-field="$valueField"
  :value="$value"
  :placeholder="$placeholder"
  :variant="$variant"
  :inline="$inline"
/>
