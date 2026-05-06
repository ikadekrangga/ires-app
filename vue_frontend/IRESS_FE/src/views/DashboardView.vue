<script setup>
import { ref, onMounted, computed } from "vue";
import MainLayout from "../layouts/MainLayout.vue";
import { getDashboard } from "../services/insightService";
import { getAccounts } from "../services/accountService";
import { Users, Eye, Heart, MessageCircle, Funnel, ChevronLeft, ChevronRight } from "lucide-vue-next";

// Chart.js imports
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  LineElement,
  PointElement,
  CategoryScale,
  LinearScale,
  ArcElement,
  Filler,
} from "chart.js";
import { Line, Doughnut } from "vue-chartjs";

ChartJS.register(
  Title, Tooltip, Legend,
  LineElement, PointElement,
  CategoryScale, LinearScale,
  ArcElement, Filler
);


const metrics = ref({});
const breakdown = ref([]);
const trend = ref([]);
const accounts = ref([]);
const selectedAccounts = ref("");
const loading = ref(false);

const today = new Date();
const last14Days = new Date(today);
last14Days.setDate(last14Days.getDate() - 14);
const since = ref(last14Days.toISOString().split("T")[0]);
const until = ref(today.toISOString().split("T")[0]);

// =====================
// COLORS
// =====================
const doughnutColors = ["#f59e0b", "#10b981", "#3b82f6", "#ef4444", "#8b5cf6", "#06b6d4", "#ec4899"];

// =====================
// API CALLS
// =====================
const fetchDashboard = async () => {
  loading.value = true;
  try {
    const res = await getDashboard({
      account_id: selectedAccounts.value,
      since: since.value,
      until: until.value,
    });
    metrics.value = res.data.data || {};
    breakdown.value = res.data.breakdown || [];
    trend.value = res.data.trend || [];
  } catch (err) {
    console.error("Failed fetching dashboard", err);
  } finally {
    loading.value = false;
  }
};

const fetchAccounts = async () => {
  const res = await getAccounts();
  accounts.value = res.data.data;
};


const getAccountName = (id) => {
  const acc = accounts.value.find((a) => a.id == id);
  return acc ? acc.name : `Account ${id}`;
};


const activeMetric = ref('reach');

const lineChartData = computed(() => {
  let dataPoints = [];
  let label = "";
  
  if (activeMetric.value === 'reach') {
    dataPoints = trend.value.map((t) => Number(t.reach) || 0);
    label = "Reach";
  } else if (activeMetric.value === 'views') {
    dataPoints = trend.value.map((t) => Number(t.views) || 0);
    label = "Views";
  } else if (activeMetric.value === 'likes') {
    dataPoints = trend.value.map((t) => Number(t.likes) || 0);
    label = "Interactions";
  } else if (activeMetric.value === 'comments') {
    dataPoints = trend.value.map((t) => Number(t.comments) || 0);
    label = "Comments";
  }

  return {
    labels: trend.value.map((t) => {
      const d = new Date(t.date);
      return d.toLocaleDateString("id-ID", { day: 'numeric', month: 'short' });
    }),
    datasets: [
      {
        label: label,
        borderColor: "#10b981", 
        backgroundColor: "rgba(16, 185, 129, 0.15)",
        borderWidth: 3,
        tension: 0.4,
        fill: true,
        pointBackgroundColor: "#ffffff",
        pointBorderColor: "#10b981",
        pointBorderWidth: 2,
        pointRadius: 4,
        pointHoverRadius: 6,
        data: dataPoints,
      },
    ],
  };
});

const doughnutChartData = computed(() => ({
  labels: breakdown.value.map((b) => getAccountName(b.account_id)),
  datasets: [
    {
      backgroundColor: breakdown.value.map((_, i) => doughnutColors[i % doughnutColors.length]),
      borderWidth: 0,
      hoverOffset: 4,
      data: breakdown.value.map((b) => Number(b.reach) || 0),
    },
  ],
}));

// =====================
// CHART OPTIONS
// =====================
const lineOptions = {
  responsive: true,
  maintainAspectRatio: false,
  scales: {
    y: {
      beginAtZero: true,
      grid: { color: "#e2e8f0" },
      border: { display: false },
      ticks: { color: "#64748b" }
    },
    x: {
      grid: { display: false },
      border: { display: false },
      ticks: { color: "#64748b" }
    },
  },
  plugins: {
    legend: {
      display: false
    },
  },
};

const doughnutOptions = {
  responsive: true,
  maintainAspectRatio: false,
  cutout: "70%",
  plugins: {
    legend: { display: false },
  },
};

// =====================
// LIFECYCLE
// =====================
onMounted(() => {
  fetchAccounts().then(() => fetchDashboard());
});
</script>

<template>
  <MainLayout>
    <div class="min-h-screen bg-indigo-50 p-4 md:p-8">
      <div class="max-w-7xl mx-auto">

        <!-- Breadcrumb Navbar -->
        <div class="bg-gray-50 rounded-xl px-6 py-4 flex justify-between items-center mb-8">
          <h2 class="text-indigo-900 text-lg font-bold tracking-wide">Dashboard</h2>
          <div class="text-sm font-semibold">
            <router-link to="/" class="text-[#7C3AED] hover:underline">Home</router-link>
            <span class="text-gray-400 mx-2">-</span>
            <span class="text-gray-400">Dashboard</span>
          </div>
        </div>

        <!-- Header / Greeting & Filter Section -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8 mt-3">
          <!-- Hello Ana Card -->
          <div class="lg:col-span-2 rounded-2xl p-8 flex flex-col justify-between shadow-sm" style="background: linear-gradient(90deg, rgba(184,33,33,1) 0%, rgba(66,80,68,1) 48%, rgba(22,143,101,1) 100%);">
            <div>
              <h2 class="text-3xl font-bold text-white mb-3 flex items-center gap-2">
                <span class="text-3xl">👋</span> Hello Ana,
              </h2>
              <p class="text-gray-200 mb-6 max-w-lg text-sm leading-relaxed">
                Welcome to your E-commerce Dashboard! Monitor your sales, track your progress, and gain valuable insights.
              </p>
            </div>
            <div>
              <router-link to="/jobs" class="inline-block bg-white text-gray-900 font-bold px-6 py-2.5 rounded-lg shadow-md hover:bg-gray-50 transition-colors text-sm">
                Insert Job
              </router-link>
            </div>
          </div>

          <!-- Filter Section (Replacing Ideas for You) -->
          <div class="bg-white rounded-2xl p-6 shadow-sm flex flex-col justify-between border border-gray-100">
            <div class="flex items-center mb-4">
              <div class="flex items-center justify-center bg-blue-50 rounded-md p-2 mr-3">
                <Funnel class="text-blue-800 w-5 h-5"></Funnel>
              </div>
              <div>
                <h3 class="font-bold text-gray-900">Filter Laporan</h3>
                <p class="text-gray-400 text-xs">Saring Data Secara Spesifik</p>
              </div>
            </div>

            <div class="flex flex-col gap-3">
              <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-1">
                  Pilih Akun
                </label>
                <select
                  v-model="selectedAccounts"
                  class="w-full appearance-none border-gray-200 rounded-xl p-2.5 border bg-slate-50 outline-none transition-all text-sm"
                >
                  <option value=""> Semua Akun </option>
                  <option v-for="acc in accounts" :key="acc.id" :value="acc.id">
                    {{ acc.name }}
                  </option>
                </select>
              </div>

              <div class="grid grid-cols-2 gap-3">
                <div>
                  <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-1">Dari</label>
                  <input
                    type="date"
                    v-model="since"
                    class="w-full border-gray-200 rounded-xl p-2 border bg-slate-50 outline-none transition-all text-xs"
                  />
                </div>
                <div>
                  <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-1">Sampai</label>
                  <input
                    type="date"
                    v-model="until"
                    class="w-full border-gray-200 rounded-xl p-2 border bg-slate-50 outline-none transition-all text-xs"
                  />
                </div>
              </div>

              <button
                @click="fetchDashboard"
                :disabled="loading"
                class="bg-indigo-600 hover:bg-indigo-700 disabled:bg-indigo-400 text-white font-semibold py-2.5 rounded-xl shadow-md shadow-indigo-200 transition-all flex items-center justify-center w-full mt-1 text-sm"
              >
                <span v-if="loading" class="animate-spin mr-2">🌀</span>
                Apply Filter
              </button>
            </div>
          </div>
        </div>

        <!-- Top Metrics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">

          <!-- Reach (Gradient) -->
          <div class="bg-gradient-to-br from-indigo-500 to-blue-600 rounded-2xl p-6 text-white shadow-lg shadow-indigo-200/50 flex justify-between items-center">
            <div class="flex flex-col justify-center items-center">
              <span class="text-sm font-medium text-indigo-100 mb-1">Reach</span>
              <span class="text-4xl font-extrabold">{{ Number(metrics.reach || 0).toLocaleString() }}</span>
            </div>
            <div class="bg-white/20 p-3 rounded-xl backdrop-blur-sm">
              <Users class="w-8 h-8 text-white" />
            </div>
          </div>

          <!-- Views -->
          <div class="bg-gradient-to-br from-green-100 to-green-200 rounded-2xl p-6 text-white shadow-lg shadow-green-200/50 flex justify-between items-center">
            <div class="flex flex-col justify-center items-center">
              <span class="text-sm font-medium text-emerald-500 mb-1 flex items-center">Views</span>
              <span class="text-4xl font-extrabold text-gray-900">{{ Number(metrics.views || 0).toLocaleString() }}</span>
            </div>
            <div class="bg-emerald-200 p-3 rounded-xl flex items-center justify-center">
              <Eye class="w-8 h-8 text-emerald-500" />
            </div>
          </div>

          <!-- Likes -->
          <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-2xl shadow-sm border border-gray-100 p-6 flex justify-between items-center">
            <div class="flex flex-col justify-center items-center">
              <span class="text-sm font-medium text-amber-500 mb-1 flex items-center">Interactions</span>
              <span class="text-4xl font-extrabold text-gray-900">{{ Number(metrics.likes || 0).toLocaleString() }}</span>
            </div>
            <div class="bg-yellow-50 p-3 rounded-xl flex items-center justify-center">
              <Heart class="w-8 h-8 text-amber-500" />
            </div>
          </div>

          <!-- Comments -->
          <div class="bg-gradient-to-br from-slate-100 to-slate-200 rounded-2xl shadow-sm border border-gray-100 p-6 flex justify-between items-center">
            <div class="flex flex-col justify-center items-center">
              <span class="text-sm font-medium text-slate-500 mb-1 flex items-center">Comments</span>
              <span class="text-4xl font-extrabold text-gray-900">{{ Number(metrics.comments || 0).toLocaleString() }}</span>
            </div>  
            <div class="bg-slate-300 p-3 rounded-xl flex items-center justify-center">
              <MessageCircle class="w-8 h-8 text-slate-500" />
            </div>
          </div>

        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

          <!-- Line Chart -->
          <div class="lg:col-span-2 bg-white rounded-2xl shadow-lg p-8">
            <div class="mb-8">
              <h2 class="text-xl font-bold text-gray-900 mb-6">Metrics Trend</h2>
              
              <!-- The 4 filter boxes -->
              <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <!-- Reach -->
                <div 
                  @click="activeMetric = 'reach'"
                  class="cursor-pointer rounded-xl p-4 transition-all border"
                  :class="activeMetric === 'reach' ? 'bg-slate-800 border-emerald-500' : 'bg-slate-500 border-transparent hover:bg-slate-800'"
                >
                  <div class="flex items-center gap-2 mb-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                    <span class="text-xs text-white font-bold uppercase tracking-wider">Reach</span>
                  </div>
                  <div class="text-2xl font-bold text-white">{{ Number(metrics.reach || 0).toLocaleString() }}</div>
                </div>

                <!-- Views -->
                <div 
                  @click="activeMetric = 'views'"
                  class="cursor-pointer rounded-xl p-4 transition-all border"
                  :class="activeMetric === 'views' ? 'bg-slate-800 border-emerald-500' : 'bg-slate-500 border-transparent hover:bg-slate-800'"
                >
                  <div class="flex items-center gap-2 mb-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                    <span class="text-xs text-white font-bold uppercase tracking-wider">Views</span>
                  </div>
                  <div class="text-2xl font-bold text-white">{{ Number(metrics.views || 0).toLocaleString() }}</div>
                </div>

                <!-- Interactions -->
                <div 
                  @click="activeMetric = 'likes'"
                  class="cursor-pointer rounded-xl p-4 transition-all border"
                  :class="activeMetric === 'likes' ? 'bg-slate-800 border-emerald-500' : 'bg-slate-500 border-transparent hover:bg-slate-800'"
                >
                  <div class="flex items-center gap-2 mb-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                    <span class="text-xs text-white font-bold uppercase tracking-wider">Interactions</span>
                  </div>
                  <div class="text-2xl font-bold text-white">{{ Number(metrics.likes || 0).toLocaleString() }}</div>
                </div>

                <!-- Comments -->
                <div 
                  @click="activeMetric = 'comments'"
                  class="cursor-pointer rounded-xl p-4 transition-all border"
                  :class="activeMetric === 'comments' ? 'bg-slate-800 border-emerald-500' : 'bg-slate-500 border-transparent hover:bg-slate-800'"
                >
                  <div class="flex items-center gap-2 mb-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-red-400"></span>
                    <span class="text-xs text-white font-bold uppercase tracking-wider">Comments</span>
                  </div>
                  <div class="text-2xl font-bold text-white">{{ Number(metrics.comments || 0).toLocaleString() }}</div>
                </div>
              </div>
            </div>

            <div class="h-[350px] relative w-full">
              <Line v-if="trend.length > 0" :data="lineChartData" :options="lineOptions" />
              <div v-else class="absolute inset-0 flex flex-col items-center justify-center text-gray-500 bg-slate-800/30 rounded-xl">
                <span class="text-4xl mb-2">📈</span>
                <p>Tidak ada data untuk rentang waktu ini</p>
              </div>
            </div>
          </div>

          <!-- Doughnut Chart + Custom Legend -->
          <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 flex flex-col">
            <div class="text-center mb-6">
              <h2 class="text-xl font-bold text-gray-900">Reach Distribution</h2>
              <p class="text-sm text-gray-500 mt-1">Perbandingan jumlah Reach antar akun</p>
            </div>

            <div v-if="breakdown.length > 0" class="flex flex-col gap-6">
              <!-- Pie Chart -->
              <div class="relative w-full flex items-center justify-center h-[280px]">
                <Doughnut :data="doughnutChartData" :options="doughnutOptions" />
              </div>

              <!-- Custom HTML Legend -->
              <div class="flex flex-col space-y-5">
                <div
                  v-for="(b, i) in breakdown"
                  :key="b.account_id"
                  class="flex justify-between items-center pb-3 border-b border-gray-100 last:border-0 last:pb-0"
                >
                  <div class="flex items-center">
                    <span
                      class="w-3 h-3 rounded-full mr-3 flex-shrink-0"
                      :style="{ backgroundColor: doughnutColors[i % doughnutColors.length] }"
                    ></span>
                    <span class="text-sm text-gray-600 truncate max-w-[120px]">{{ getAccountName(b.account_id) }}</span>
                  </div>
                  <span class="text-sm font-bold text-gray-900 ml-2">{{ Number(b.reach || 0).toLocaleString() }}</span>
                </div>
              </div>
            </div>

            <div v-else class="flex-1 flex flex-col items-center justify-center text-gray-400">
              <span class="text-4xl mb-2">🍩</span>
              <p class="text-center px-4">Belum ada data akun</p>
            </div>
          </div>

        </div>
      </div>
    </div>
  </MainLayout>
</template>
