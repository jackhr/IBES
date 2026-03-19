export type NavItem = {
  label: string;
  path: string;
};

export type FeatureItem = {
  title: string;
  description: string;
};

export type AboutSection = {
  image: string;
  imageAlt: string;
  title: string;
  paragraphs: string[];
  bullets?: string[];
};

export type FaqItem = {
  question: string;
  answer: string;
};

export type TaxiPackage = {
  title: string;
  details: string[];
};

export const siteData = {
  brand: "IBES",
  companyName: "Ibes Car Rental",
  phone: "+1 (268) 773-2900",
  email: "irwinphilo390@gmail.com",
  location: "Coolidge, Antigua and Barbuda",
  nav: [
    { label: "Home", path: "/" },
    { label: "About", path: "/about" },
    { label: "FAQ", path: "/faq" },
    { label: "Reservation", path: "/reservation" },
    { label: "Taxi", path: "/taxi" },
    { label: "Contact", path: "/contact" }
  ] satisfies NavItem[],
  heroImage: "/assets/images/bg/shirley-heights-antigua-view.avif",
  heroTitle: "Reliable Antigua Car Rental.",
  heroSubtitle:
    "Book airport-ready vehicles and private transfers with a local team focused on responsive support.",
  metrics: [
    { label: "Support", value: "24/7" },
    { label: "Coverage", value: "365 Beaches" },
    { label: "Pickup", value: "Airport + Hotel" }
  ],
  features: [
    {
      title: "Quality Vehicles",
      description:
        "Our fleet is cleaned, inspected, and maintained regularly so you can drive with confidence across the island."
    },
    {
      title: "Easy Island Driving",
      description:
        "We provide practical guidance for left-side driving in Antigua so your trip starts smoothly from day one."
    },
    {
      title: "Helpful Add-Ons",
      description:
        "Child seats and navigation options are available upon request to make your booking fit your itinerary."
    },
    {
      title: "Straightforward Pricing",
      description:
        "Transparent rental terms with optional insurance and multi-day discounts for longer stays."
    }
  ] satisfies FeatureItem[],
  aboutSections: [
    {
      image: "/assets/images/misc/Freepik-whiteSUV-Palms-SandyBeach-Couple-09.jpg",
      imageAlt: "Couple near their rental car by the beach",
      title: "Welcome to Ibes Car Rental",
      paragraphs: [
        "Ibes Car Rental has served Antigua's transport market for years, with a strong focus on reliability and service consistency.",
        "Whether you're visiting for the first time or returning home for family events, our team can match the right vehicle or transfer option to your plans."
      ]
    },
    {
      image: "/assets/images/misc/Harbour-NelsonsDockyard-Air-JMR18809.jpg",
      imageAlt: "Aerial view of English Harbour in Antigua",
      title: "Explore Antigua With Confidence",
      paragraphs: [
        "Antigua offers beaches, cultural sites, and scenic drives in every direction.",
        "If you need destination suggestions, our local team is happy to help with practical recommendations."
      ],
      bullets: [
        "365 beaches to explore",
        "Nelson's Dockyard National Park",
        "Fig Tree Drive and rainforest routes",
        "Nightlife and entertainment districts"
      ]
    }
  ] satisfies AboutSection[],
  faqs: [
    {
      question: "Is insurance included in my rental agreement?",
      answer:
        "Insurance is optional, but recommended. Without insurance, the renter is responsible for full damages. With insurance, renter liability is reduced according to agreement terms."
    },
    {
      question: "What is required when I rent a car?",
      answer: "A valid driver's license and Antigua & Barbuda temporary license are required."
    },
    {
      question: "Will there be a fee if I have to cancel my reservation?",
      answer: "No, provided we have not already dispatched a vehicle at the time of cancellation."
    },
    {
      question: "Which side of the road do you drive on in Antigua?",
      answer: "In Antigua & Barbuda, traffic drives on the left side of the road."
    },
    {
      question: "What methods of payment do you accept?",
      answer: "We accept cash and major credit cards including Visa and MasterCard."
    },
    {
      question: "Do you provide child safety seats?",
      answer: "Yes, complimentary child seats are offered subject to availability."
    },
    {
      question: "What should I do if I have an accident or mechanical issue?",
      answer:
        "Contact emergency services when needed, then contact Ibes Car Rental using the phone number in your agreement."
    },
    {
      question: "Can I rent without a reservation?",
      answer: "Yes, but advance booking is strongly recommended during peak travel periods."
    }
  ] satisfies FaqItem[],
  taxiPackages: [
    {
      title: "Cruise Ship Pickup",
      details: ["Pickup from cruise terminal", "Quick transfer support", "US$100 per person / 4-hour island tour"]
    },
    {
      title: "Island Tour Package",
      details: ["US$100 per person", "4-hour guided island run", "Comfortable private ride"]
    },
    {
      title: "VIP Service",
      details: ["Personalized requests", "Tailored transport schedule", "Contact us for details"]
    },
    {
      title: "Private Airport Transfer",
      details: ["To/from airport transfers", "Additional US$10 regulation fee", "Reliable pre-booked pickup"]
    }
  ] satisfies TaxiPackage[]
};
