<script setup>
import { ref, onMounted } from "vue";
import MainLayout from "../layouts/MainLayout.vue";
import { getDashboard } from "../services/insightService";
import { getAccounts } from "../services/accountService";

const metrics = ref({});
const accounts = ref({});
const selectedAccounts = ref([]);
const since = ref("");
const until = ref("");

const fetchDashboard = async () => {
  const res = await getDashboard({
    account_id: selectedAccounts.value.join(","),
    since: since.value,
    until: until.value,
  });

  metrics.value = res.data.data;
};

const fetchAccounts = async () => {
  const res = await getAccounts();
  accounts.value = res.data.data;
};

onMounted(() => {
  fetchAccounts();
  fetchDashboard();
});
</script>

<template>
  <MainLayout>
    <h1 class="text-2xl font-bold mb-4">Dashboard</h1>

    <div class="flex gap-4 mb-6">
      <select v-model="selectedAccounts" multiple class="border p-2">
        <option v-for="acc in accounts" :key="acc.id" :value="'acc.id'">
          {{ acc.account_name }}
        </option>
      </select>

      <input type="date" v-model="since" class="border p-2" />
      <input type="date" v-model="until" class="border p-2" />

      <button
        @click="fetchDashboard"
        class="'bg-blue-500 text-white px-4 py-2'"
      >
        Apply
      </button>
    </div>

    <div class="grid grid-cols-4 gap-4">
      <div class="bg-white p-4 shadow">Reach: {{ metrics.reach }}</div>
      <div class="bg-white p-4 shadow">Views: {{ metrics.views }}</div>
      <div class="bg-white p-4 shadow">Likes: {{ metrics.likes }}</div>
      <div class="bg-white p-4 shadow">Comments: {{ metrics.comments }}</div>
    </div>
  </MainLayout>
</template>
