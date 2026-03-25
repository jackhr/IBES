import { useEffect } from "react";
import { DEFAULT_OG_IMAGE, normalizePath, resolveRouteSeo } from "../../data/routeSeo";
import { siteData } from "../../data/siteData";

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

function upsertJsonLd(id: string, payload: Record<string, unknown>): void {
  const existing = document.head.querySelector<HTMLScriptElement>(`script[type='application/ld+json']#${id}`);
  const script = existing ?? document.createElement("script");

  script.setAttribute("type", "application/ld+json");
  script.setAttribute("id", id);
  script.textContent = JSON.stringify(payload);

  if (!existing) {
    document.head.appendChild(script);
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

    upsertMeta("meta[property='og:site_name']", {
      property: "og:site_name",
      content: siteData.companyName
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

    upsertMeta("meta[name='twitter:url']", {
      name: "twitter:url",
      content: canonicalUrl
    });

    upsertCanonical(canonicalUrl);

    const schemaGraph: Array<Record<string, unknown>> = [
      {
        "@type": "WebPage",
        "@id": `${canonicalUrl}#webpage`,
        url: canonicalUrl,
        name: meta.title,
        description: meta.description,
        isPartOf: { "@id": `${baseUrl}/#website` }
      }
    ];

    if (normalizedPath === "/faq") {
      schemaGraph.push({
        "@type": "FAQPage",
        "@id": `${canonicalUrl}#faq`,
        url: canonicalUrl,
        mainEntity: siteData.faqs.map((faq) => ({
          "@type": "Question",
          name: faq.question,
          acceptedAnswer: {
            "@type": "Answer",
            text: faq.answer
          }
        }))
      });
    }

    upsertJsonLd("route-structured-data", {
      "@context": "https://schema.org",
      "@graph": schemaGraph
    });
  }, [pathname, underConstructionEnabled]);

  return null;
}
