/// <reference types="vite/client" />

interface ImportMetaEnv {
  readonly UNDER_CONSTRUCTION?: string;
}

interface ImportMeta {
  readonly env: ImportMetaEnv;
}
