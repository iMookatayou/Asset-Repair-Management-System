@props(['name','id'=>null,'items'=>collect(),'labelField'=>'name','valueField'=>'id','value'=>null,'placeholder'=>'— ไม่ระบุ —'])
@php
  $id = $id ?: $name;
  $items = $items instanceof \Illuminate\Support\Collection ? $items : collect($items);
  $value = old($name,$value);
@endphp
@php($variant = $variant ?? 'dropdown')
@php($inline = $inline ?? true)
<x-searchable-select :name="$name" :id="$id" :items="$items" :label-field="$labelField" :value-field="$valueField" :value="$value" :placeholder="$placeholder" :variant="$variant" :inline="$inline" />
