import { defineConfig, loadEnv } from "vite";
import react from "@vitejs/plugin-react";

export default defineConfig(({ command, mode }) => {
  const env = loadEnv(mode, ".", "");
  const proxyTarget = env.VITE_BACKEND_PROXY_TARGET?.trim();

  return {
    base: command === "build" ? "/dist/" : "/",
    envPrefix: ["VITE_", "UNDER_"],
    plugins: [react()],
    build: {
      outDir: "dist",
      emptyOutDir: true
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
