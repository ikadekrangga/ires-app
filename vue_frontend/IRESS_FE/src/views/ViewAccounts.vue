<script setup>
import { ref, onMounted } from "vue";
import api from "../services/api";

// Import shadcn-like components (pastikan path sesuai dengan setup Anda)
import { Button } from "../components/ui/button";
import { Input } from "../components/ui/input";
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "../components/ui/table";
import {
  Card,
  CardContent,
  CardHeader,
  CardTitle,
} from "../components/ui/card";
import { Badge } from "../components/ui/badge";
import { Plus, Pencil, Trash2, X, Loader2 } from "lucide-vue-next";

// --- State ---
const accounts = ref([]);
const loading = ref(true);
const submitting = ref(false);
const showForm = ref(false);
const isEdit = ref(false);
const selectedId = ref(null);

const form = ref({
  account_name: "",
  instagram_business_id: "",
  access_token: "",
});

// --- Actions ---
const fetchAccounts = async () => {
  loading.value = true;
  try {
    const res = await api.get("/accounts");
    accounts.value = res.data;
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
    account_name: "",
    instagram_business_id: "",
    access_token: "",
  };
};

onMounted(fetchAccounts);
</script>

<template>
  <div class="p-8 max-w-5xl mx-auto space-y-6">
    <!-- Header Section -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-3xl font-bold tracking-tight">Instagram Accounts</h1>
        <p class="text-muted-foreground">
          Kelola koneksi akun Instagram Business Anda.
        </p>
      </div>

      <Button v-if="!showForm" @click="showForm = true" class="gap-2">
        <Plus class="w-4 h-4" /> Tambah Akun
      </Button>
    </div>

    <!-- Form Section (Card Component) -->
    <transition name="slide">
      <Card v-if="showForm" class="border-2 border-primary/10 shadow-lg">
        <CardHeader
          class="flex flex-row items-center justify-between space-y-0 pb-4"
        >
          <CardTitle class="text-xl">
            {{ isEdit ? "Edit Akun" : "Tambah Akun Baru" }}
          </CardTitle>
          <Button variant="ghost" size="icon" @click="closeForm">
            <X class="w-4 h-4" />
          </Button>
        </CardHeader>
        <CardContent>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="space-y-2">
              <label class="text-sm font-medium">Nama Akun</label>
              <Input
                v-model="form.account_name"
                placeholder="Misal: Marketing IG"
              />
            </div>
            <div class="space-y-2">
              <label class="text-sm font-medium">IG Business ID</label>
              <Input
                v-model="form.instagram_business_id"
                placeholder="178414..."
              />
            </div>
            <div class="space-y-2">
              <label class="text-sm font-medium">Access Token</label>
              <Input
                v-model="form.access_token"
                type="password"
                placeholder="EAA..."
              />
            </div>
          </div>
          <div class="flex justify-end gap-3">
            <Button variant="outline" @click="closeForm">Batal</Button>
            <Button :disabled="submitting" @click="submitForm">
              <Loader2 v-if="submitting" class="mr-2 h-4 w-4 animate-spin" />
              {{ isEdit ? "Update Akun" : "Simpan Akun" }}
            </Button>
          </div>
        </CardContent>
      </Card>
    </transition>

    <!-- Table Section -->
    <Card>
      <CardContent class="p-0">
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead class="w-[250px]">Account Name</TableHead>
              <TableHead>IG Business ID</TableHead>
              <TableHead class="text-right">Actions</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-if="loading">
              <TableCell colspan="3" class="h-24 text-center">
                <div class="flex items-center justify-center gap-2">
                  <Loader2 class="h-4 w-4 animate-spin" /> Memuat data...
                </div>
              </TableCell>
            </TableRow>

            <TableRow v-else v-for="acc in accounts" :key="acc.id">
              <TableCell class="font-medium">
                {{ acc.account_name }}
              </TableCell>
              <TableCell>
                <Badge variant="secondary" class="font-mono font-normal">
                  {{ acc.instagram_business_id }}
                </Badge>
              </TableCell>
              <TableCell class="text-right space-x-2">
                <Button variant="ghost" size="sm" @click="editAccount(acc)">
                  <Pencil class="w-4 h-4 mr-1" /> Edit
                </Button>
                <Button
                  variant="ghost"
                  size="sm"
                  class="text-destructive hover:text-destructive"
                  @click="deleteAccount(acc.id)"
                >
                  <Trash2 class="w-4 h-4 mr-1" /> Hapus
                </Button>
              </TableCell>
            </TableRow>

            <TableRow v-if="!loading && accounts.length === 0">
              <TableCell
                colspan="3"
                class="h-24 text-center text-muted-foreground"
              >
                Tidak ada data akun ditemukan.
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </CardContent>
    </Card>
  </div>
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
