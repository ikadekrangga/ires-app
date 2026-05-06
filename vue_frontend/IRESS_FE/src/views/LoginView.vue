<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import api from '../services/api';

const router = useRouter();

const form = ref({
  email: '',
  password: ''
});

const loading = ref(false);
const errorMessage = ref('');

const login = async () => {
  try {
    loading.value = true;
    errorMessage.value = '';
    
    const response = await api.post('/login', form.value);
    
    const token = response.data.access_token;
    const user = response.data.user;
    
    // Store token and user data
    localStorage.setItem('auth_token', token);
    localStorage.setItem('auth_user', JSON.stringify(user));
    
    // Redirect to dashboard
    router.push('/');
    
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Login gagal. Periksa kembali email dan password Anda.';
  } finally {
    loading.value = false;
  }
};
</script>

<template>
  <div class="min-h-screen bg-indigo-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
      <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900 tracking-tight">
        IRESS APP
      </h2>
      <p class="mt-2 text-center text-sm text-gray-600">
        Sign in to your account to continue
      </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
      <div class="bg-white py-8 px-4 shadow-xl shadow-indigo-100/50 sm:rounded-2xl sm:px-10 border border-gray-100">
        
        <div v-if="errorMessage" class="mb-4 bg-red-50 border-l-4 border-red-500 p-4 rounded-md">
          <div class="flex">
            <div class="flex-shrink-0">
              <span class="text-red-500">⚠️</span>
            </div>
            <div class="ml-3">
              <p class="text-sm text-red-700">
                {{ errorMessage }}
              </p>
            </div>
          </div>
        </div>

        <form class="space-y-6" @submit.prevent="login">
          <div>
            <label for="email" class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2">
              Email address
            </label>
            <div class="mt-1">
              <input 
                id="email" 
                v-model="form.email" 
                name="email" 
                type="email" 
                autocomplete="email" 
                required 
                class="appearance-none block w-full px-4 py-3 border border-gray-200 rounded-xl shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm transition-all bg-slate-50 focus:bg-white" 
                placeholder="test@example.com"
              >
            </div>
          </div>

          <div>
            <label for="password" class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2">
              Password
            </label>
            <div class="mt-1">
              <input 
                id="password" 
                v-model="form.password" 
                name="password" 
                type="password" 
                autocomplete="current-password" 
                required 
                class="appearance-none block w-full px-4 py-3 border border-gray-200 rounded-xl shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm transition-all bg-slate-50 focus:bg-white" 
                placeholder="••••••••"
              >
            </div>
          </div>



          <div>
            <button 
              type="submit" 
              :disabled="loading"
              class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-md shadow-indigo-200 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all disabled:opacity-70 disabled:cursor-not-allowed"
            >
              <span v-if="loading" class="animate-spin mr-2">🌀</span>
              {{ loading ? 'Signing in...' : 'Sign in' }}
            </button>
          </div>
        </form>

        <div class="mt-8 text-center text-sm text-gray-600 border-t border-gray-100 pt-6">
          Don't have an account? 
          <router-link to="/register" class="font-bold text-indigo-600 hover:text-indigo-500 transition-colors">Sign up</router-link>
        </div>

      </div>
    </div>
  </div>
</template>
