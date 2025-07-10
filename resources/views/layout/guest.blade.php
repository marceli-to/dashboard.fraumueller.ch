<x-layout.head />

@if (!request()->routeIs('auth.*') && !request()->routeIs('page.home'))
<x-layout.header>
  @hasSection('header')
    @yield('header')
  @endif
</x-layout.header>
@endif

<x-layout.main class="{{ $class ?? '' }}">
  @yield('content')
</x-layout.main>

<x-layout.footer />