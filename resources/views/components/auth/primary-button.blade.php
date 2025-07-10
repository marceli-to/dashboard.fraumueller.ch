<button {{ $attributes->merge(['type' => 'submit', 'class' => 'border border-black bg-black hover:bg-white hover:text-black text-white w-auto inline-flex items-center justify-start py-8 px-12 text-sm leading-none !ring-0 !outline-none transition-all']) }}>
  {{ $slot }}
</button>
