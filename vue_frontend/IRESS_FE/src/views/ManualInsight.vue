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
  follows: 0,
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

    // Gunakan feedback yang lebih baik di masa depan (seperti toast)
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
      follows: 0,
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
    <div class="max-w-4xl mx-auto py-8 px-4">
      <header class="mb-8">
        <h1 class="text-3xl font-extrabold text-gray-900">Manual Insights</h1>
        <p class="text-gray-500 mt-2">
          Input performa data Instagram secara manual untuk laporan periodik.
        </p>
      </header>

      <div
        class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden"
      >
        <!-- Form Section -->
        <div class="p-8">
          <div class="space-y-6">
            <!-- Account Selection -->
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2"
                >Target Account</label
              >
              <select
                v-model="form.account_id"
                class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 p-2.5 border"
              >
                <option value="">Choose an Instagram Account</option>
                <option v-for="acc in accounts" :key="acc.id" :value="acc.id">
                  {{ acc.account_name }}
                </option>
              </select>
            </div>

            <!-- Date Range -->
            <div
              class="grid grid-cols-1 md:grid-cols-2 gap-6 p-4 bg-gray-50 rounded-lg"
            >
              <div>
                <label class="block text-sm font-medium text-gray-600 mb-1"
                  >Since</label
                >
                <input
                  type="date"
                  v-model="form.since"
                  class="w-full border-gray-300 rounded-md p-2 border focus:ring-2 focus:ring-indigo-200"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-600 mb-1"
                  >Until</label
                >
                <input
                  type="date"
                  v-model="form.until"
                  class="w-full border-gray-300 rounded-md p-2 border focus:ring-2 focus:ring-indigo-200"
                />
              </div>
            </div>

            <!-- Metrics Grid -->
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-4"
                >Performance Metrics</label
              >
              <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                <div
                  v-for="metric in [
                    'reach',
                    'views',
                    'likes',
                    'comments',
                    'follows',
                  ]"
                  :key="metric"
                  class="relative"
                >
                  <label
                    class="block text-xs font-bold uppercase text-gray-400 mb-1 ml-1"
                    >{{ metric }}</label
                  >
                  <input
                    v-model="form[metric]"
                    type="number"
                    min="0"
                    :placeholder="
                      metric.charAt(0).toUpperCase() + metric.slice(1)
                    "
                    class="w-full border-gray-300 rounded-lg p-3 border focus:bg-white bg-gray-50 transition-all outline-none focus:ring-2 focus:ring-green-400"
                  />
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Footer / Action -->
        <div class="bg-gray-50 px-8 py-4 flex items-center justify-end">
          <button
            @click="submit"
            :disabled="loading || !isFormValid"
            class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white transition-all bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="loading" class="mr-2 animate-spin">🌀</span>
            {{ loading ? "Processing..." : "Save Insights" }}
          </button>
        </div>
      </div>
    </div>
  </MainLayout>
</template>
