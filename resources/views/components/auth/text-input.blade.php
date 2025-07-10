@props(['disabled' => false])
<input 
  {{ $disabled ? 'disabled' : '' }} 
  {!! $attributes->merge() !!}
  x-data
  @focus="$el.removeAttribute('data-error'); document.querySelector('[data-toast]') ? document.querySelector('[data-toast]').style.display = 'none' : null;"
  @class([
    'w-full !ring-0 py-8 px-12 bg-transparent',
    'border-black',
    'focus:!border-black focus:bg-white focus:text-black',
    'focus:placeholder:text-black',
    'placeholder:text-black',
    'data-[error=true]:text-red-600',
    'data-[error=true]:border-red-600',
  ])
>