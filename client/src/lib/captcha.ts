export type CaptchaProvider = "none" | "hcaptcha" | "recaptcha";

export type CaptchaConfig = {
  enabled: boolean;
  provider: CaptchaProvider;
  siteKey: string;
};

function normalizeProvider(rawValue: string): CaptchaProvider {
  const value = rawValue.trim().toLowerCase();

  if (value === "hcaptcha") {
    return "hcaptcha";
  }

  if (value === "recaptcha") {
    return "recaptcha";
  }

  return "none";
}

export function getCaptchaConfig(): CaptchaConfig {
  const provider = normalizeProvider(import.meta.env.VITE_CAPTCHA_PROVIDER ?? "none");

  if (provider === "none") {
    return {
      enabled: false,
      provider,
      siteKey: ""
    };
  }

  const siteKey =
    provider === "hcaptcha"
      ? (import.meta.env.VITE_HCAPTCHA_SITE_KEY ?? "").trim()
      : (import.meta.env.VITE_RECAPTCHA_SITE_KEY ?? "").trim();

  return {
    enabled: siteKey !== "",
    provider,
    siteKey
  };
}
