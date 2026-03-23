const VISITOR_ID_KEY = "ibes_visitor_id";
const SESSION_ID_KEY = "ibes_session_id";
const LAST_TRACKED_SIGNATURE_KEY = "ibes_last_tracked_signature";

const toUuid = (): string => {
  if (typeof crypto !== "undefined" && typeof crypto.randomUUID === "function") {
    return crypto.randomUUID();
  }

  const randomHex = (length: number): string => {
    let value = "";

    while (value.length < length) {
      value += Math.floor(Math.random() * 0xffffffff)
        .toString(16)
        .padStart(8, "0");
    }

    return value.slice(0, length);
  };

  return `${randomHex(8)}-${randomHex(4)}-4${randomHex(3)}-a${randomHex(3)}-${randomHex(12)}`;
};

const getOrCreateStorageId = (storage: Storage, key: string): string => {
  const existing = storage.getItem(key);

  if (existing && existing.trim() !== "") {
    return existing;
  }

  const created = toUuid();
  storage.setItem(key, created);

  return created;
};

const normalizePath = (pathname: string): string => {
  const value = pathname.trim();

  if (value === "") {
    return "/";
  }

  return value.startsWith("/") ? value : `/${value}`;
};

export const trackVisitorPageView = (pathname: string, search = ""): void => {
  if (typeof window === "undefined") {
    return;
  }

  try {
    const path = normalizePath(pathname);
    const signature = `${path}${search}`;
    const previousSignature = window.sessionStorage.getItem(LAST_TRACKED_SIGNATURE_KEY);

    if (previousSignature === signature) {
      return;
    }

    window.sessionStorage.setItem(LAST_TRACKED_SIGNATURE_KEY, signature);

    const payload = {
      visitorId: getOrCreateStorageId(window.localStorage, VISITOR_ID_KEY),
      sessionId: getOrCreateStorageId(window.sessionStorage, SESSION_ID_KEY),
      path,
      fullUrl: window.location.href,
      queryString: search,
      referrer: document.referrer,
      language: navigator.language || "",
      timezone: Intl.DateTimeFormat().resolvedOptions().timeZone || "",
      userAgent: navigator.userAgent || "",
      viewportWidth: window.innerWidth,
      viewportHeight: window.innerHeight,
      screenWidth: window.screen?.width ?? null,
      screenHeight: window.screen?.height ?? null,
      occurredAt: new Date().toISOString(),
      eventType: "page_view"
    };

    const body = JSON.stringify(payload);

    if (typeof navigator.sendBeacon === "function") {
      const blob = new Blob([body], { type: "application/json" });
      const queued = navigator.sendBeacon("/api/visitor-events", blob);

      if (queued) {
        return;
      }
    }

    void fetch("/api/visitor-events", {
      method: "POST",
      credentials: "same-origin",
      keepalive: true,
      headers: {
        "Content-Type": "application/json"
      },
      body
    }).catch(() => undefined);
  } catch {
    // Swallow tracking errors so page navigation is never impacted.
  }
};

