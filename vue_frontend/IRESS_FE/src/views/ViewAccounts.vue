<script setup>
import { ref, onMounted } from "vue";
import api from "../services/api";
import MainLayout from "../layouts/MainLayout.vue";
import { Plus, Pencil, Trash2, X, Loader2 } from "lucide-vue-next";

// --- State ---
const accounts = ref([]);
const loading = ref(true);
const submitting = ref(false);
const showForm = ref(false);
const isEdit = ref(false);
const selectedId = ref(null);

const form = ref({
  name: "",
  instagram_business_id: "", 
  access_token: "",
});

// --- Actions ---
const fetchAccounts = async () => {
  loading.value = true;
  try {
    const res = await api.get("/accounts");
    accounts.value = res.data.data;
  } catch (err) {
    console.error("Fetch error:", err);
  } finally {
    loading.value = false;
  }
};

const submitForm = async () => {
  submitting.value = true;
  try {
    if (isEdit.value) {
      await api.put(`/accounts/${selectedId.value}`, form.value);
    } else {
      await api.post("/accounts", form.value);
    }
    closeForm();
    await fetchAccounts();
  } catch (err) {
    console.error("Submit error:", err);
    alert(err.response?.data?.error || err.response?.data?.message || "Terjadi kesalahan saat menyimpan akun. Pastikan Token valid.");
  } finally {
    submitting.value = false;
  }
};

const editAccount = (acc) => {
  isEdit.value = true;
  selectedId.value = acc.id;
  form.value = { ...acc };
  showForm.value = true;
};

const deleteAccount = async (id) => {
  if (!confirm("Yakin hapus account?")) return;
  try {
    await api.delete(`/accounts/${id}`);
    fetchAccounts();
  } catch (err) {
    console.error("Delete error:", err);
  }
};

const closeForm = () => {
  showForm.value = false;
  isEdit.value = false;
  selectedId.value = null;
  form.value = {
    name: "",
    instagram_business_id: "",
    access_token: "",
  };
};

onMounted(fetchAccounts);
</script>

<template>
  <MainLayout>
    <div class="min-h-screen bg-indigo-50 p-4 md:p-8">
      <div class="max-w-7xl mx-auto">
        <!-- Breadcrumb Navbar -->
        <div class="bg-white rounded-xl px-6 py-4 flex justify-between items-center mb-8 shadow-sm border border-gray-100">
          <h2 class="text-indigo-900 text-lg font-bold tracking-wide">Accounts</h2>
          <div class="text-sm font-semibold">
            <router-link to="/" class="text-[#7C3AED] hover:underline">Home</router-link>
            <span class="text-gray-400 mx-2">-</span>
            <span class="text-gray-400">Accounts</span>
          </div>
        </div>

        <!-- Header -->
        <header class="mb-8 mt-3 flex flex-wrap gap-4 justify-between items-end">
          <div>
            <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Instagram Accounts</h1>
            <p class="text-gray-500 mt-1">Kelola koneksi akun Instagram Business Anda.</p>
          </div>
          <button v-if="!showForm" @click="showForm = true" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-6 py-3 rounded-xl shadow-md shadow-indigo-200 transition-all flex items-center gap-2 h-[46px]">
            <Plus class="w-5 h-5" /> Tambah Akun
          </button>
        </header>

        <!-- Form Section -->
        <transition name="slide">
          <div v-if="showForm" class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 mb-8 flex flex-col gap-4">
            <div class="flex items-center justify-between mb-2">
              <h3 class="font-bold text-gray-900 text-lg">{{ isEdit ? "Edit Akun" : "Tambah Akun Baru" }}</h3>
              <button @click="closeForm" class="text-gray-400 hover:text-gray-600 transition-colors">
                <X class="w-5 h-5" />
              </button>
            </div>
            
            <div class="flex flex-wrap items-end gap-6">
              <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2">Nama Akun</label>
                <input v-model="form.name" placeholder="Misal: Marketing IG" class="w-full border-gray-200 rounded-xl p-3 border bg-slate-50 outline-none transition-all text-sm" />
              </div>
              <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2">IG Business ID</label>
                <input v-model="form.instagram_business_id" placeholder="178414..." class="w-full border-gray-200 rounded-xl p-3 border bg-slate-50 outline-none transition-all text-sm" />
              </div>
              <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2">Access Token</label>
                <input v-model="form.access_token" type="password" placeholder="EAA..." class="w-full border-gray-200 rounded-xl p-3 border bg-slate-50 outline-none transition-all text-sm" />
              </div>
              
              <div class="flex gap-3 h-[46px] mt-2 w-full md:w-auto justify-end">
                <button @click="closeForm" class="border border-gray-200 hover:bg-gray-50 text-gray-700 font-semibold px-6 py-2 rounded-xl transition-all">Batal</button>
                <button @click="submitForm" :disabled="submitting" class="bg-indigo-600 hover:bg-indigo-700 disabled:bg-indigo-400 text-white font-semibold px-6 py-2 rounded-xl shadow-md transition-all flex items-center">
                  <Loader2 v-if="submitting" class="mr-2 h-4 w-4 animate-spin" />
                  {{ isEdit ? "Update" : "Simpan" }}
                </button>
              </div>
            </div>
          </div>
        </transition>

        <!-- Table Section -->
        <div class="bg-slate-50 rounded-2xl shadow-lg overflow-hidden border border-gray-800">
          <div class="p-6 border-b border-gray-700 bg-slate-200">
            <h2 class="text-xl font-bold text-gray-900">Account List</h2>
          </div>
          
          <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr class="border-b border-gray-700/80 bg-slate-500">
                  <th class="p-5 text-sm font-bold text-black">Account Name</th>
                  <th class="p-5 text-sm font-bold text-black">IG Business ID</th>
                  <th class="p-5 text-sm font-bold text-black">Status</th>
                  <th class="p-5 text-sm font-bold text-black text-right">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="loading">
                  <td colspan="4" class="p-8 text-center text-gray-900">
                    <div class="flex items-center justify-center gap-2">
                      <Loader2 class="h-5 w-5 animate-spin text-indigo-600" /> Memuat data...
                    </div>
                  </td>
                </tr>
                <tr v-else v-for="acc in accounts" :key="acc.id" class="border-b border-gray-800 hover:bg-slate-200/50 transition-colors">
                  <td class="p-5 text-sm font-medium text-gray-900">{{ acc.name }}</td>
                  <td class="p-5 text-sm text-gray-900">
                    <span class="bg-white border border-gray-200 px-2 py-1 rounded text-xs font-mono">{{ acc.ig_account_id }}</span>
                  </td>
                  <td class="p-5 text-sm">
                    <span class="px-3 py-1.5 rounded-md text-xs font-bold" :class="acc.status === 'active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'">
                      {{ acc.status === 'active' ? 'Active' : 'Need Re-Auth' }}
                    </span>
                    <div v-if="acc.active_credential || acc.activeCredential" class="text-xs text-gray-500 mt-2">
                      Expires: {{ new Date((acc.active_credential || acc.activeCredential).expires_at).toLocaleDateString() }}
                    </div>
                  </td>
                  <td class="p-5 text-sm text-right space-x-2 whitespace-nowrap">
                    <button @click="editAccount(acc)" class="text-indigo-600 hover:text-indigo-800 hover:bg-indigo-50 px-3 py-1.5 rounded-lg transition-colors inline-flex items-center">
                      <Pencil class="w-4 h-4 mr-1" /> Edit
                    </button>
                    <button @click="deleteAccount(acc.id)" class="text-red-600 hover:text-red-800 hover:bg-red-50 px-3 py-1.5 rounded-lg transition-colors inline-flex items-center">
                      <Trash2 class="w-4 h-4 mr-1" /> Hapus
                    </button>
                  </td>
                </tr>
                <tr v-if="!loading && accounts.length === 0">
                  <td colspan="4" class="p-8 text-center text-gray-500">Tidak ada data akun ditemukan.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

      </div>
    </div>
  </MainLayout>
</template>

<style scoped>
.slide-enter-active,
.slide-leave-active {
  transition: all 0.3s ease-out;
}
.slide-enter-from,
.slide-leave-to {
  opacity: 0;
  transform: translateY(-20px);
}
</style>
