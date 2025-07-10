@props(['text' => null, 'route'])
<a 
  class="text-xs hover:underline underline-offset-2" 
  href="{{ route($route) }}">
  {{ $slot }}
</a>