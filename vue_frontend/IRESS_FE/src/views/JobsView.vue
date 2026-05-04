<script setup>
import { ref, onMounted } from "vue";
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

const fetchJobs = async () => {
  const res = await getJobs();
  jobs.value = res.data.data;
};

const fetchAccount = async () => {
  const res = await getAccounts();
  accounts.value = res.data.data;
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

  setInterval(fetchJobs, 5000);
});
</script>

<template>
  <MainLayout>
    <h1 class="text-2xl font-bold mb-6">Jobs</h1>

    <div class="bg-white p-4 shadow mb-6">
      <h2 class="font-semibold mb-4">Create Job</h2>

      <div class="flex gap-4">
        <select v-model="form.account_id" class="border p-2">
          <option value="">Select Account</option>
          <option v-for="acc in accounts" :key="acc.id" :value="acc.id">
            {{ acc.account_name }}
          </option>
        </select>

        <input type="date" v-model="form.since" class="border p-2" />
        <input type="date" v-model="form.until" class="border p-2" />

        <button
          @click="submitJobs"
          class="bg-blue-500 text-white px-4 py-2"
          :disabled="loading"
        >
          {{ loading ? "Processing..." : "Create" }}
        </button>
      </div>
    </div>

    <div class="bg-white p-4 shadow">
      <h2 class="font-semibold mb-4">Job List</h2>

      <table class="w-full border">
        <thead>
          <tr class="bg-gray-100">
            <th class="p-2">ID</th>
            <th>Account</th>
            <th>Status</th>
            <th>Attempt</th>
            <th>Error</th>
          </tr>
        </thead>

        <tbody>
          <tr v-for="job in jobs" :key="job.id" class="border-t">
            <td class="p-2">{{ job.id }}</td>
            <td>{{ job.account_id }}</td>

            <td>
              <span
                :class="{
                  'text-yellow-500': job.status === 'pending',
                  'text-blue-500': job.status === 'processing',
                  'text-green-500': job.status === 'success',
                  'text-red-500': job.status.includes('failed'),
                }"
                >{{ job.status }}</span
              >
            </td>

            <td>{{ job.attempt_count }}</td>
            <td class="text-red-500 text-sm">{{ job.last_error }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </MainLayout>
</template>
