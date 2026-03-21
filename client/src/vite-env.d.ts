/// <reference types="vite/client" />

interface ImportMetaEnv {
  readonly UNDER_CONSTRUCTION?: string;
  readonly SHOW_TESTIMONIALS?: string;
  readonly VITE_CAPTCHA_PROVIDER?: string;
  readonly VITE_HCAPTCHA_SITE_KEY?: string;
  readonly VITE_RECAPTCHA_SITE_KEY?: string;
  readonly VITE_SITE_URL?: string;
}

interface ImportMeta {
  readonly env: ImportMetaEnv;
}
