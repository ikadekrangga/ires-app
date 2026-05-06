<script setup>
import { ref } from 'vue';
import { Menu, X, LogOut } from 'lucide-vue-next';
import { useRouter } from 'vue-router';
import api, { setLoggingOut } from '../services/api';

const isSidebarOpen = ref(true);
const router = useRouter();

const toggleSidebar = () => {
  isSidebarOpen.value = !isSidebarOpen.value;
};

const handleLogout = async () => {
  // 1. Set flag so 401 interceptor doesn't interfere
  setLoggingOut(true);
  
  // 2. Save token before clearing (needed for the API call)
  const token = localStorage.getItem('auth_token');
  
  // 3. Clear localStorage FIRST (guarantee user is logged out locally)
  localStorage.removeItem('auth_token');
  localStorage.removeItem('auth_user');
  
  // 4. Navigate to login IMMEDIATELY
  router.push('/login');
  
  // 5. Fire-and-forget: tell backend to revoke token
  try {
    await api.post('/logout', {}, {
      headers: { Authorization: `Bearer ${token}` }
    });
  } catch (error) {
    // Silently ignore — user is already logged out locally
  } finally {
    setLoggingOut(false);
  }
};
</script>

<template>
  <div class="flex min-h-screen relative bg-slate-50">
    <!-- Toggle Button (Floating) -->
    <button 
      @click="toggleSidebar" 
      class="fixed top-6 z-[60] p-2.5 rounded-xl bg-gray-900 text-white shadow-lg hover:bg-gray-800 transition-all duration-300 border border-gray-700"
      :class="isSidebarOpen ? 'left-[236px]' : 'left-6'"
    >
      <Menu v-if="!isSidebarOpen" class="w-5 h-5" />
      <X v-else class="w-5 h-5" />
    </button>

    <!-- Sidebar -->
    <aside 
      class="bg-gray-900 text-white transition-all duration-300 ease-in-out sticky top-0 h-screen flex flex-col"
      :class="isSidebarOpen ? 'w-64 px-6' : 'w-0 px-0 opacity-0 overflow-hidden'"
    >
      <div class="w-52 pt-12 flex-1 flex flex-col">
        <h1 class="text-2xl font-bold mb-10 px-4">IRESS APP</h1>

        <nav class="flex flex-col gap-2 flex-1">
          <router-link to="/" class="px-4 py-3 rounded-xl hover:bg-gray-800 transition-colors">Dashboard</router-link>
          <router-link to="/jobs" class="px-4 py-3 rounded-xl hover:bg-gray-800 transition-colors">Jobs</router-link>
          <router-link to="/manual" class="px-4 py-3 rounded-xl hover:bg-gray-800 transition-colors">Manual</router-link>
          <router-link to="/accounts" class="px-4 py-3 rounded-xl hover:bg-gray-800 transition-colors">Accounts</router-link>
        </nav>

        <div class="pb-8 mt-auto px-2">
          <button @click="handleLogout" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-red-500/10 text-gray-400 hover:text-red-400 transition-colors font-semibold">
            <LogOut class="w-5 h-5" />
            Logout
          </button>
        </div>
      </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 w-full min-w-0 transition-all duration-300">
      <!-- Konten Anda (misal Dashboard) akan masuk di sini -->
      <slot />
    </main>
  </div>
</template>

<style scoped>
.router-link-active {
  background-color: #1f2937; /* bg-gray-800 */
  color: #a5b4fc; /* text-indigo-300 */
  font-weight: 600;
}
</style>
