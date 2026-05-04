import { fileURLToPath, URL } from "node:url";
import { defineConfig } from "vite";
import vue from "@vitejs/plugin-vue"; // Gunakan ini, bukan bundle-renderer
import path from "path";

export default defineConfig({
  plugins: [vue()],
  resolve: {
    alias: {
      // Pastikan alias ini merujuk ke folder src proyek IRESS_FE Anda
      "@": fileURLToPath(new URL("./src", import.meta.url)),
    },
  },
});
