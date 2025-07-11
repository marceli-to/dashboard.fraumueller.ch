<template>
  <div class="flex flex-col justify-between min-h-screen sticky top-0 border-r border-r-white">
    <div>
      <h1 class="text-md leading-[1.25] p-12 text-white">
        Dashboard<br>Frau Müller
      </h1>
      <nav class="mt-48">
        <ul class="border-t border-t-white">
          <li>
            <router-link
              :to="{ name: 'orders' }"
              class="p-12 block border-b border-b-white w-full text-white hover:bg-white hover:text-white transition-colors duration-150"
              :class="{ '!text-black bg-white': $route.name === 'orders' }">
              Bestellungen
            </router-link>
          </li>
          <!-- <li>
            <router-link
              :to="{ name: 'comments' }"
              class="p-12 block border-b border-b-black w-full hover:bg-white hover:text-white transition-colors duration-150"
              :class="{ 'text-white bg-white': $route.name === 'comments' }">
              Import
            </router-link>
          </li> -->
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