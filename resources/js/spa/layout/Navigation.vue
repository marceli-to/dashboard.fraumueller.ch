<template>
  <div class="flex flex-col justify-between min-h-screen sticky top-0 ">
    <div>
      <h1 class="text-md font-medium leading-[1.25] p-12 text-white mt-8">
        Dashboard<br>Frau Müller
      </h1>
      <nav class="mt-48">
        <ul class="flex flex-col gap-y-1">
          <li>
            <router-link
              :to="{ name: 'orders' }"
              class="p-12 block w-full text-white hover:bg-white hover:text-black transition-colors duration-150"
              :class="{ '!text-black bg-white border-b-black': $route.name === 'orders' || $route.name === 'orders.edit' }">
              Bestellungen
            </router-link>
          </li>
          <li>
            <router-link
              :to="{ name: 'products' }"
              class="p-12 block w-full text-white hover:bg-white hover:text-black transition-colors duration-150"
              :class="{ '!text-black bg-white border-b-black': $route.name === 'products' }">
              Produkte
            </router-link>
          </li>
          <li>
            <router-link
              :to="{ name: 'import' }"
              class="p-12 block w-full text-white hover:bg-white hover:text-black transition-colors duration-150"
              :class="{ '!text-black bg-white border-b-black': $route.name === 'import' }">
              Import
            </router-link>
          </li>
          <li>
            <router-link
              :to="{ name: 'logs' }"
              class="p-12 block w-full text-white hover:bg-white hover:text-black transition-colors duration-150"
              :class="{ '!text-black bg-white border-b-black': $route.name === 'logs' }">
              Log
            </router-link>
          </li>
        </ul>
      </nav>
    </div>
    <div class="p-16">
      <a 
        href="/logout" 
        class="text-xs no-underline hover:underline underline-offset-4 decoration-1 block w-full text-white"
        title="Abmelden">
        Abmelden
      </a>
    </div>
  </div>
</template>