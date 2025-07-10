<header class="sticky top-0 z-[2000] bg-limora min-h-52 pb-5 pt-2 md:py-0 md:min-h-60 xl:min-h-72 flex items-center border-b-3 xl:border-b-4 border-black">
  <x-layout.container class="flex justify-between items-start md:items-center">
    {{ $slot }}
    @if (!request()->routeIs('page.building'))
      <x-menu.buttons.back />
    @endif
  </x-layout.container>
</header>
