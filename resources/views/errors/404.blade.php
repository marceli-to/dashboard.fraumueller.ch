@extends('layout.guest')
@section('seo_title', '404 – Seite nicht gefunden')

@section('header')
<x-headings.h1>404 – Seite nicht gefunden</x-headings.h1>
@endsection

@section('content')
<x-layout.container class="p-20 xl:p-30">
  <x-layout.article>
    <p>
      Die gewünschte Seite konnte nicht gefunden werden.
    </p>
    <p>
      <a 
        href="{{ route('page.home') }}"
        title="Zurück zur Startseite"
        class="underline underline-offset-4 decoration-1 xl:decoration-2 hover:no-underline">
        Zurück zur Startseite
      </a>
    </p>
  </x-layout.article>
</x-layout.container>
@endsection