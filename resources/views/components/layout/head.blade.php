<!doctype html>
<html lang="de" class="scroll-smooth overflow-y-scroll {{ $class ?? ''}}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>@seo_title</title>
<meta name="description" content="@seo_description">
<meta property="og:title" content="@seo_title">
<meta property="og:description" content="@seo_description">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:site_name" content="@seo_title">
<meta property="og:image" content="{{ url('/') }}@seo_image">
<meta name="twitter:card" content="{{ url('/') }}@seo_image">
<meta name="twitter:title" content="@seo_title">
<meta name="twitter:description" content="@seo_description">
<meta name="twitter:image" content="summary_large_image">
<meta name="view-transition" content="same-origin" />
<meta name="msapplication-TileColor" content="#ffffff">
<meta name="theme-color" content="#ffffff">
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="icon" type="image/png" href="/favicon-96x96.png" sizes="96x96" />
<link rel="icon" type="image/svg+xml" href="/favicon.svg" />
<link rel="shortcut icon" href="/favicon.ico" />
<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png" />
<meta name="apple-mobile-web-app-title" content="Dashboard Frau Müller" />
<link rel="manifest" href="/site.webmanifest" />
@vite('resources/css/app.css')
@livewireStyles
</head>
<body 
  class="antialiased font-sans bg-white text-sm md:text-md xl:text-lg text-black flex flex-col min-h-screen"
  x-data="{ menu: false }">