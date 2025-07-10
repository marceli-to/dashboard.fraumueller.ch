@extends('layout.guest')
@section('seo_title', '500 – Serverfehler')

@section('header')
<x-headings.h1>500 – Serverfehler</x-headings.h1>
@endsection

@section('content')
<x-layout.container class="p-20 xl:p-30">
  <x-layout.article>
    <p>
      Leider ist ein Fehler aufgetreten.
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