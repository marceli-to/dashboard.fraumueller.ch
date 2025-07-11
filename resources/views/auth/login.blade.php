@extends('layout.guest')
@section('seo_title', 'Login')
@section('content')
<div class="flex justify-center min-h-screen bg-white">

  <div class="hidden w-1/2 overflow-hidden relative md:flex items-center justify-center">
    <div class="font-chlorite-medium text-5xl 2xl:text-6xl text-center text-white relative z-40">
      Frau Müller
      <span class="font-sangbleu-italic text-md 2xl:text-lg block uppercase">
        Das Magazin zur Fussball-EM 2025
      </span>
    </div>
    <div class="absolute top-0 left-0 w-full h-full bg-black opacity-20 z-20"></div>
    <img src="/img/splash/splash.jpg" width="1125" height="1500" alt="" class="block w-full h-full absolute top-0 left-0 object-cover">
  </div>

  <div class="w-full md:w-1/2 flex flex-col justify-center">

    <div class="p-32 lg:p-48 max-w-lg mx-auto w-full">
      <h1 class="text-lg leading-[1.2] mb-24">
        Login
      </h1>
      @if ($errors->any())
        <x-auth.toast status="Es ist ein Fehler aufgetreten." type="error" />
      @endif

      @if (session('status'))
        <x-auth.toast :status="session('status')" type="success" />
      @endif

      <x-auth.wrapper>
      
        <form 
          method="POST" 
          action="{{ route('auth.login') }}" 
          class="flex flex-col gap-y-24 w-full">
          @csrf

          <x-auth.text-input 
            type="email" 
            name="email" 
            :value="old('email')"
            data-error="{{ $errors->has('email') ? 'true' : null }}"
            placeholder="{{ __('E-Mail') }}"
            required />

          <x-auth.text-input 
            type="password"
            name="password"
            placeholder="{{ __('Passwort') }}"
            data-error="{{ $errors->has('password') ? 'true' : null }}"
            required />

          <div>
            <x-auth.primary-button>
              {{ __('Login') }}
            </x-auth.primary-button>
          </div>

        </form>
      
      </x-auth.wrapper>
    </div>
  </div>
  
</div>
@endsection

