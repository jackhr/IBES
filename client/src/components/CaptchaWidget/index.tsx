import HCaptcha, { HCaptcha as HCaptchaInstance } from "@hcaptcha/react-hcaptcha";
import { useEffect, useRef } from "react";
import { CaptchaProvider } from "../../lib/captcha";
import "./CaptchaWidget.scss";

type CaptchaWidgetProps = {
  enabled: boolean;
  provider: CaptchaProvider;
  siteKey: string;
  resetCounter: number;
  onTokenChange: (token: string | null) => void;
};

declare global {
  interface Window {
    __ibesCaptchaSolved?: (token: string) => void;
    __ibesCaptchaExpired?: () => void;
    grecaptcha?: {
      reset: () => void;
    };
  }
}

const SCRIPT_IDS = {
  recaptcha: "ibes-recaptcha-script"
} as const;

const CALLBACK_NAME = "__ibesCaptchaSolved";
const EXPIRED_CALLBACK_NAME = "__ibesCaptchaExpired";

export default function CaptchaWidget({
  enabled,
  provider,
  siteKey,
  resetCounter,
  onTokenChange
}: CaptchaWidgetProps) {
  const hcaptchaRef = useRef<HCaptchaInstance | null>(null);

  useEffect(() => {
    if (!enabled || provider !== "recaptcha") {
      return;
    }

    window[CALLBACK_NAME] = (token: string) => {
      onTokenChange(token || null);
    };

    window[EXPIRED_CALLBACK_NAME] = () => {
      onTokenChange(null);
    };

    return () => {
      delete window[CALLBACK_NAME];
      delete window[EXPIRED_CALLBACK_NAME];
    };
  }, [enabled, provider, onTokenChange]);

  useEffect(() => {
    if (!enabled || provider !== "recaptcha") {
      return;
    }

    const scriptId = SCRIPT_IDS[provider];

    if (document.getElementById(scriptId)) {
      return;
    }

    const script = document.createElement("script");
    script.id = scriptId;
    script.async = true;
    script.defer = true;
    script.src = "https://www.google.com/recaptcha/api.js";

    document.body.appendChild(script);
  }, [enabled, provider]);

  useEffect(() => {
    if (!enabled || provider === "none" || resetCounter < 1) {
      return;
    }

    onTokenChange(null);

    if (provider === "hcaptcha" && hcaptchaRef.current) {
      hcaptchaRef.current.resetCaptcha();
      return;
    }

    if (provider === "recaptcha" && window.grecaptcha) {
      window.grecaptcha.reset();
    }
  }, [enabled, provider, resetCounter, onTokenChange]);

  if (!enabled || provider === "none") {
    return null;
  }

  if (provider === "hcaptcha") {
    return (
      <div className="captcha-widget captcha-widget--hcaptcha">
        <HCaptcha
          ref={hcaptchaRef}
          sitekey={siteKey}
          onVerify={(token: string) => onTokenChange(token || null)}
          onExpire={() => onTokenChange(null)}
          onError={() => onTokenChange(null)}
          reCaptchaCompat={false}
        />
      </div>
    );
  }

  return (
    <div
      className="captcha-widget g-recaptcha"
      data-sitekey={siteKey}
      data-callback={CALLBACK_NAME}
      data-expired-callback={EXPIRED_CALLBACK_NAME}
    />
  );
}
