@props(['status', 'type' => 'success'])
@if ($status)
<div 
  x-data="{ show: true }"
  @if ($type !== 'error')
  x-init="setTimeout(() => show = false, 3000)"
  @endif
  x-on:click="show = false"
  x-show="show"
  class="fixed z-[9999] text-xs w-full max-w-[300px] text-white top-20 right-20 cursor-pointer"
  data-toast>
  <div
    @class([
      'p-8',
      'bg-mintara' => $type == 'success',
      'bg-red-500' => $type == 'error',
      'bg-steelor' => $type == 'info',
    ])>
    {{ $status }}
  </div>
</div>
@endif
