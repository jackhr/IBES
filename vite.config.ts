import { defineConfig, loadEnv } from "vite";
import react from "@vitejs/plugin-react";

export default defineConfig(({ mode }) => {
  const env = loadEnv(mode, ".", "");
  const proxyTarget = env.VITE_BACKEND_PROXY_TARGET?.trim();

  return {
    plugins: [react()],
    build: {
      manifest: true
    },
    server: proxyTarget
      ? {
          proxy: {
            "/includes": {
              target: proxyTarget,
              changeOrigin: true
            }
          }
        }
      : undefined
  };
});
