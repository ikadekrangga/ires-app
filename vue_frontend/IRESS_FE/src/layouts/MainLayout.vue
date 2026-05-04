<template>
  <div class="flex">
    <div class="w-64 bg-gray-900 text-white min-h-screen p-4">
      <h1 class="text-xl font-bold mb-6">Dashboard</h1>

      <router-link to="/" class="block mb-2">Dashboard</router-link>
      <router-link to="/jobs" class="block mb-2">Jobs</router-link>
      <router-link to="/manual" class="block mb-2">Manual</router-link>
      <router-link to="/accounts" class="block mb-2">Accounts</router-link>
    </div>

    <div class="flex-1 p-6">
      <slot />
    </div>
  </div>
</template>
