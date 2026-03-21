import { defineConfig, loadEnv } from "vite";
import react from "@vitejs/plugin-react";

export default defineConfig(({ command, mode }) => {
  const env = loadEnv(mode, ".", "");
  const proxyTarget = env.VITE_BACKEND_PROXY_TARGET?.trim();

  return {
    base: command === "build" ? "/dist/" : "/",
    envPrefix: ["VITE_", "UNDER_", "SHOW_"],
    plugins: [react()],
    css: {
      preprocessorOptions: {
        scss: {
          api: "modern"
        }
      }
    },
    build: {
      outDir: "dist",
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
    server: proxyTarget
      ? {
          proxy: {
            "/api": {
              target: proxyTarget,
              changeOrigin: true
            }
          }
        }
      : undefined
  };
});
