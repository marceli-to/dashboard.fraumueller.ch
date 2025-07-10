@props(['value'])
<label class="block leading-none mb-8">
  {{ $value ?? $slot }}
</label>