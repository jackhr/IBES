import { useEffect } from "react";
import { DEFAULT_OG_IMAGE, normalizePath, resolveRouteSeo } from "../../data/routeSeo";

type RouteMetaProps = {
  pathname: string;
  underConstructionEnabled: boolean;
};

function upsertMeta(selector: string, attributes: Record<string, string>): HTMLMetaElement {
  const existing = document.head.querySelector<HTMLMetaElement>(selector);
  const meta = existing ?? document.createElement("meta");

  Object.entries(attributes).forEach(([key, value]) => {
    meta.setAttribute(key, value);
  });

  if (!existing) {
    document.head.appendChild(meta);
  }

  return meta;
}

function upsertCanonical(url: string): void {
  const existing = document.head.querySelector<HTMLLinkElement>("link[rel='canonical']");
  const canonical = existing ?? document.createElement("link");
  canonical.setAttribute("rel", "canonical");
  canonical.setAttribute("href", url);

  if (!existing) {
    document.head.appendChild(canonical);
  }
}

export default function RouteMeta({ pathname, underConstructionEnabled }: RouteMetaProps) {
  useEffect(() => {
    const meta = resolveRouteSeo(pathname, underConstructionEnabled);
    const baseUrl = (import.meta.env.VITE_SITE_URL ?? "https://www.ibescarrental.com").replace(/\/+$/, "");
    const normalizedPath = normalizePath(pathname);
    const canonicalPath = meta.canonicalPath || normalizedPath;
    const canonicalUrl = new URL(canonicalPath, `${baseUrl}/`).toString();
    const ogImage = new URL(DEFAULT_OG_IMAGE, `${baseUrl}/`).toString();
    const robotsValue = meta.noindex ? "noindex, nofollow" : "index, follow";

    document.title = meta.title;

    upsertMeta("meta[name='description']", {
      name: "description",
      content: meta.description
    });

    upsertMeta("meta[name='robots']", {
      name: "robots",
      content: robotsValue
    });

    upsertMeta("meta[property='og:title']", {
      property: "og:title",
      content: meta.title
    });

    upsertMeta("meta[property='og:description']", {
      property: "og:description",
      content: meta.description
    });

    upsertMeta("meta[property='og:type']", {
      property: "og:type",
      content: meta.ogType ?? "website"
    });

    upsertMeta("meta[property='og:url']", {
      property: "og:url",
      content: canonicalUrl
    });

    upsertMeta("meta[property='og:image']", {
      property: "og:image",
      content: ogImage
    });

    upsertMeta("meta[name='twitter:card']", {
      name: "twitter:card",
      content: "summary_large_image"
    });

    upsertMeta("meta[name='twitter:title']", {
      name: "twitter:title",
      content: meta.title
    });

    upsertMeta("meta[name='twitter:description']", {
      name: "twitter:description",
      content: meta.description
    });

    upsertMeta("meta[name='twitter:image']", {
      name: "twitter:image",
      content: ogImage
    });

    upsertCanonical(canonicalUrl);
  }, [pathname, underConstructionEnabled]);

  return null;
}
