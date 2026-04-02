import { defineConfig } from "vite";
import react from "@vitejs/plugin-react";
import laravel from "laravel-vite-plugin";

export default defineConfig({
  envPrefix: ["VITE_", "UNDER_", "SHOW_"],
  plugins: [
    laravel({
      input: ["src/main.tsx"],
      refresh: true
    }),
    react()
  ],
  build: {
    outDir: "public/build",
    emptyOutDir: true,
    rollupOptions: {
      output: {
        entryFileNames: "assets/js/[name]-[hash].js",
        chunkFileNames: "assets/js/[name]-[hash].js",
        assetFileNames: (assetInfo) => {
          const names = Array.isArray(assetInfo.names) ? assetInfo.names : [];
          const originalFileNames = Array.isArray(assetInfo.originalFileNames) ? assetInfo.originalFileNames : [];
          const fileNames = [...names, ...originalFileNames];
          const isCssAsset = fileNames.some((name) => name.toLowerCase().endsWith(".css"));

          if (isCssAsset) {
            return "assets/css/[name]-[hash][extname]";
          }

          return "assets/[name]-[hash][extname]";
        }
      }
    }
  },
  server: {
    watch: {
      ignored: ["**/storage/framework/views/**"]
    }
  }
});
