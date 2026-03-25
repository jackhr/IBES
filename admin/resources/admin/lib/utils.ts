import { type ClassValue, clsx } from "clsx";
import { twMerge } from "tailwind-merge";
import { CachedResourceEnvelope, PaginationMeta } from "../types";
import { RESOURCE_CACHE_TTL_MS } from "../consts";

export function cn(...inputs: ClassValue[]): string {
  return twMerge(clsx(inputs));
}

export const initialPaginationMeta = (perPage: number): PaginationMeta => ({
  current_page: 1,
  last_page: 1,
  per_page: perPage,
  total: 0
});

export const readCachedResource = <TResource,>(cacheKey: string): TResource | null => {
  if (typeof window === "undefined" || !window.localStorage) {
    return null;
  }

  try {
    const rawValue = window.localStorage.getItem(cacheKey);

    if (!rawValue) {
      return null;
    }

    const cached = JSON.parse(rawValue) as CachedResourceEnvelope<TResource>;

    if (Date.now() >= cached.expiresAt) {
      window.localStorage.removeItem(cacheKey);
      return null;
    }

    return cached.value;
  } catch {
    return null;
  }
};

export const writeCachedResource = <TResource,>(cacheKey: string, value: TResource) => {
  if (typeof window === "undefined" || !window.localStorage) {
    return;
  }

  try {
    const payload: CachedResourceEnvelope<TResource> = {
      value,
      expiresAt: Date.now() + RESOURCE_CACHE_TTL_MS
    };
    window.localStorage.setItem(cacheKey, JSON.stringify(payload));
  } catch {
    // Ignore storage quota/privacy mode errors.
  }
};