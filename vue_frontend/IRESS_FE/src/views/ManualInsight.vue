<script setup>
import { ref, onMounted, computed } from "vue";
import MainLayout from "../layouts/MainLayout.vue";
import { createManualInsight } from "../services/insightService";
import { getAccounts } from "../services/accountService";

const accounts = ref([]);
const loading = ref(false);

const form = ref({
  account_id: "",
  since: "",
  until: "",
  reach: 0,
  views: 0,
  likes: 0,
  comments: 0,
  follows_and_unfollows: 0,
});

const fetchAccounts = async () => {
  const res = await getAccounts();
  accounts.value = res.data.data;
};

// UX: Validasi sederhana untuk tombol disable
const isFormValid = computed(() => {
  return form.value.account_id && form.value.since && form.value.until;
});

const submit = async () => {
  try {
    loading.value = true;
    await createManualInsight(form.value);

    alert("✨ Insight Saved Successfully!");

    // Reset form
    form.value = {
      account_id: "",
      since: "",
      until: "",
      reach: 0,
      views: 0,
      likes: 0,
      comments: 0,
      follows_and_unfollows: 0,
    };
  } catch (err) {
    alert(err.response?.data?.message || "Failed to save insights!");
  } finally {
    loading.value = false;
  }
};

onMounted(fetchAccounts);
</script>

<template>
  <MainLayout>
    <div class="min-h-screen bg-indigo-50 p-4 md:p-8">
      <div class="max-w-7xl mx-auto">
        <!-- Breadcrumb Navbar -->
        <div class="bg-white rounded-xl px-6 py-4 flex justify-between items-center mb-8 shadow-sm border border-gray-100">
          <h2 class="text-indigo-900 text-lg font-bold tracking-wide">Manual Insight</h2>
          <div class="text-sm font-semibold">
            <router-link to="/" class="text-[#7C3AED] hover:underline">Home</router-link>
            <span class="text-gray-400 mx-2">-</span>
            <span class="text-gray-400">Manual Insight</span>
          </div>
        </div>

        <!-- Header -->
        <header class="mb-8 mt-3">
          <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Manual Insights</h1>
          <p class="text-gray-500 mt-1">Input performa data Instagram secara manual untuk laporan periodik.</p>
        </header>

        <!-- Form Section -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 mb-8 flex flex-col gap-6">
          <div class="flex items-center mb-2 border-b border-gray-100 pb-4">
            <h3 class="font-bold text-gray-900 text-lg">Input New Data</h3>
          </div>
          
          <!-- Basic Info -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2">TARGET ACCOUNT</label>
              <select v-model="form.account_id" class="w-full appearance-none border-gray-200 rounded-xl p-3 border bg-slate-50 outline-none transition-all text-sm">
                <option value="">Pilih Akun</option>
                <option v-for="acc in accounts" :key="acc.id" :value="acc.id">
                  {{ acc.name }}
                </option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2">DARI</label>
              <input type="date" v-model="form.since" class="w-full border-gray-200 rounded-xl p-3 border bg-slate-50 outline-none transition-all text-sm" />
            </div>
            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2">SAMPAI</label>
              <input type="date" v-model="form.until" class="w-full border-gray-200 rounded-xl p-3 border bg-slate-50 outline-none transition-all text-sm" />
            </div>
          </div>

          <!-- Metrics Grid -->
          <div class="mt-4">
            <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-4">PERFORMANCE METRICS</label>
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
              <div v-for="metric in ['reach', 'views', 'likes', 'comments', 'follows_and_unfollows']" :key="metric">
                <label class="block text-[10px] font-bold uppercase text-gray-400 mb-2">{{ metric.replace(/_/g, ' ') }}</label>
                <input v-model="form[metric]" type="number" min="0" :placeholder="metric" class="w-full border-gray-200 rounded-xl p-3 border bg-slate-50 outline-none transition-all text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500" />
              </div>
            </div>
          </div>

          <!-- Footer / Action -->
          <div class="flex justify-end mt-4 pt-6 border-t border-gray-100">
            <button
              @click="submit"
              :disabled="loading || !isFormValid"
              class="bg-indigo-600 hover:bg-indigo-700 disabled:bg-indigo-400 text-white font-semibold px-8 py-3 rounded-xl shadow-md shadow-indigo-200 transition-all flex items-center justify-center h-[46px]"
            >
              <span v-if="loading" class="animate-spin mr-2">🌀</span>
              {{ loading ? "Processing..." : "Save Insights" }}
            </button>
          </div>
        </div>

      </div>
    </div>
  </MainLayout>
</template>
