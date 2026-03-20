export type RouteSeoMeta = {
  title: string;
  description: string;
  canonicalPath: string;
  ogType?: "website" | "article";
  noindex?: boolean;
};

const DEFAULT_META: RouteSeoMeta = {
  title: "Ibes Car Rental | Antigua Car Rental and Taxi Services",
  description: "Reliable Antigua car rental and airport transfer services from Ibes Car Rental.",
  canonicalPath: "/",
  ogType: "website"
};

const UNDER_CONSTRUCTION_META: RouteSeoMeta = {
  title: "Ibes Car Rental | Under Construction",
  description: "Our website is currently under construction. Contact Ibes Car Rental for reservations and support.",
  canonicalPath: "/",
  ogType: "website",
  noindex: true
};

const ROUTE_META: Record<string, RouteSeoMeta> = {
  "/": {
    title: "Ibes Car Rental | Affordable Antigua Car Rental and Taxi Services",
    description:
      "Book reliable car rentals and private taxi transfers in Antigua with responsive local support from Ibes Car Rental.",
    canonicalPath: "/"
  },
  "/about": {
    title: "About Ibes Car Rental | Local Antigua Rental and Taxi Team",
    description:
      "Learn about Ibes Car Rental, our service standards, and why travelers trust our local team across Antigua.",
    canonicalPath: "/about/"
  },
  "/faq": {
    title: "FAQ | Ibes Car Rental Antigua",
    description:
      "Find answers to common questions on licenses, insurance, payments, fuel policy, child seats, and driving in Antigua.",
    canonicalPath: "/faq/"
  },
  "/contact": {
    title: "Contact Ibes Car Rental | Antigua Booking and Support",
    description:
      "Contact Ibes Car Rental for reservations, taxi requests, and rental support in Antigua. We respond quickly to inquiries.",
    canonicalPath: "/contact/"
  },
  "/reservation": {
    title: "Book Car Rental in Antigua | Ibes Car Rental Reservation",
    description:
      "Reserve your Antigua rental vehicle with Ibes Car Rental and get clear rates, add-ons, and dependable service.",
    canonicalPath: "/reservation/"
  },
  "/taxi": {
    title: "Taxi Reservation in Antigua | Ibes Car Rental Transfers",
    description:
      "Request airport pickups, cruise transfers, and private taxi rides across Antigua with Ibes Car Rental.",
    canonicalPath: "/taxi/"
  },
  "/confirmation": {
    title: "Reservation Confirmation | Ibes Car Rental",
    description: "Review your request status and confirmation details from Ibes Car Rental.",
    canonicalPath: "/confirmation/"
  },
  "/404": {
    title: "Page Not Found | Ibes Car Rental",
    description: "The page you requested was not found. Browse active routes at Ibes Car Rental.",
    canonicalPath: "/",
    noindex: true
  }
};

export function resolveRouteSeo(pathname: string, underConstructionEnabled: boolean): RouteSeoMeta {
  if (underConstructionEnabled) {
    return UNDER_CONSTRUCTION_META;
  }

  const normalizedPath = normalizePath(pathname);

  return ROUTE_META[normalizedPath] ?? ROUTE_META["/404"] ?? DEFAULT_META;
}

export function normalizePath(pathname: string): string {
  if (pathname === "") {
    return "/";
  }

  if (pathname.length > 1 && pathname.endsWith("/")) {
    return pathname.slice(0, -1);
  }

  return pathname;
}

export const DEFAULT_OG_IMAGE = "/assets/images/misc/optimized/Freepik-whiteSUV-Palms-SandyBeach-Couple-09-1800.jpg";
