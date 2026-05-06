<script setup>
import { ref, onMounted, onUnmounted } from "vue";
import MainLayout from "../layouts/MainLayout.vue";
import { getJobs, createJob } from "../services/jobService";
import { getAccounts } from "../services/accountService";

const jobs = ref([]);
const accounts = ref([]);

const form = ref({
  account_id: "",
  since: "",
  until: "",
});

const loading = ref(false);
let intervalId = null;

const fetchJobs = async () => {
  const res = await getJobs();
  jobs.value = res.data.data;
};

const fetchAccount = async () => {
  const res = await getAccounts();
  accounts.value = res.data.data;
};

const getAccountName = (id) => {
  const acc = accounts.value.find((a) => a.id == id);
  return acc ? acc.name : id;
};

const submitJobs = async () => {
  try {
    loading.value = true;
    await createJob(form.value);

    await fetchJobs();

    form.value = {
      account_id: "",
      since: "",
      until: "",
    };
  } catch (err) {
    alert(err.response?.data?.message || "Failed Create Job");
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchJobs();
  fetchAccount();
  intervalId = setInterval(fetchJobs, 5000);
});

onUnmounted(() => {
  if (intervalId) clearInterval(intervalId);
});
</script>

<template>
  <MainLayout>
    <div class="min-h-screen bg-indigo-50 p-4 md:p-8">
      <div class="max-w-7xl mx-auto">
        
        <!-- Breadcrumb Navbar -->
        <div class="bg-white rounded-xl px-6 py-4 flex justify-between items-center mb-8 shadow-sm border border-gray-100">
          <h2 class="text-indigo-900 text-lg font-bold tracking-wide">Jobs</h2>
          <div class="text-sm font-semibold">
            <router-link to="/" class="text-[#7C3AED] hover:underline">Home</router-link>
            <span class="text-gray-400 mx-2">-</span>
            <span class="text-gray-400">Jobs</span>
          </div>
        </div>

        <!-- Header -->
        <header class="mb-8 mt-3">
          <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Job Management</h1>
          <p class="text-gray-500 mt-1">Buat tugas baru dan pantau status pelaksanaannya.</p>
        </header>

        <!-- Create Job Section -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 mb-8 flex flex-col gap-4">
          <div class="flex items-center mb-2">
            <h3 class="font-bold text-gray-900 text-lg">Create New Job</h3>
          </div>
          
          <div class="flex flex-wrap items-end gap-6">
            <div class="flex-1 min-w-[200px]">
              <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2">PILIH AKUN</label>
              <select v-model="form.account_id" class="w-full appearance-none border-gray-200 rounded-xl p-3 border bg-slate-50 outline-none transition-all text-sm">
                <option value="">Pilih Akun</option>
                <option v-for="acc in accounts" :key="acc.id" :value="acc.id">
                  {{ acc.name }}
                </option>
              </select>
            </div>

            <div class="flex-1 min-w-[200px]">
              <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2">DARI</label>
              <input type="date" v-model="form.since" class="w-full border-gray-200 rounded-xl p-3 border bg-slate-50 outline-none transition-all text-sm" />
            </div>

            <div class="flex-1 min-w-[200px]">
              <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2">SAMPAI</label>
              <input type="date" v-model="form.until" class="w-full border-gray-200 rounded-xl p-3 border bg-slate-50 outline-none transition-all text-sm" />
            </div>

            <button
              @click="submitJobs"
              :disabled="loading"
              class="bg-indigo-600 hover:bg-indigo-700 disabled:bg-indigo-400 text-white font-semibold px-8 py-3 rounded-xl shadow-md shadow-indigo-200 transition-all flex items-center justify-center h-[46px]"
            >
              <span v-if="loading" class="animate-spin mr-2">🌀</span>
              Create Job
            </button>
          </div>
        </div>

        <!-- Job List Table (Dark Theme like Image) -->
        <div class="bg-slate-50 rounded-2xl shadow-lg overflow-hidden border border-gray-800">
          <div class="p-6 border-b border-gray-700 bg-slate-200">
            <h2 class="text-xl font-bold text-gray-900">Job List</h2>
          </div>
          
          <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr class="border-b border-gray-700/80 bg-slate-500">
                  <th class="p-5 text-sm font-bold text-black">Job ID</th>
                  <th class="p-5 text-sm font-bold text-black">Account Name</th>
                  <th class="p-5 text-sm font-bold text-black">Attempt</th>
                  <th class="p-5 text-sm font-bold text-black">Status</th>
                  <th class="p-5 text-sm font-bold text-black">Error Details</th>
                </tr>
              </thead>

              <tbody>
                <tr v-for="job in jobs" :key="job.id" class="border-b border-gray-800 hover:bg-slate-800/40 transition-colors">
                  <td class="p-5 text-sm font-medium text-gray-900">#{{ job.id }}</td>
                  <td class="p-5 text-sm text-gray-900">{{ getAccountName(job.account_id) }}</td>
                  <td class="p-5 text-sm text-gray-900">{{ job.attempt_count }}</td>
                  <td class="p-5 text-sm">
                    <span
                      class="px-3 py-1.5 rounded-md text-xs font-bold"
                      :class="{
                        'bg-yellow-500/10 text-yellow-500': job.status === 'pending',
                        'bg-blue-500/10 text-blue-400': job.status === 'processing',
                        'bg-green-500/10 text-emerald-400': job.status === 'success',
                        'bg-red-500/10 text-red-500': job.status.includes('failed'),
                      }"
                    >
                      {{ job.status.charAt(0).toUpperCase() + job.status.slice(1) }}
                    </span>
                  </td>
                  <td class="p-5 text-sm text-red-400 truncate max-w-xs">{{ job.error_reason || '-' }}</td>
                </tr>
                <tr v-if="jobs.length === 0">
                  <td colspan="5" class="p-8 text-center text-gray-500">No jobs found.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

      </div>
    </div>
  </MainLayout>
</template>
