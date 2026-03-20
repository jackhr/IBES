import { Feature } from "../components/Features";
import { Testimonial } from "../components/Testimonials";

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
        "For the insurance policy, it is optional but we do recommend that you rent with the additional insurance because if you do not rent with the insurance and you damage the vehicle you are responsible to cover all damages for the vehicle. Whereas if you rent with the insurance and any damage occur you are only allowed to pay $1500 and the insurance will cover the rest."
    },
    {
      question: "What is required when I rent a car?",
      answer: "A valid Driver's License and Antigua & Barbuda Temporary License which is valid for 3 months at a cost of USD$20.00."
    },
    {
      question: "Will there be a fee if I have to cancel my reservation?",
      answer: "No, provided that Ibes Car Rental has not dispatched a vehicle at the time you cancel."
    },
    {
      question: "When does the rental time start and stop?",
      answer:
        "The start time of the rental period shall be at the actual delivery and acceptance by the individual of the vehicle. The ending time shall be at the end of rental time period. Ibes Car Rental will make prior arrangements for pick ups and drop offs at locations most convenient for you."
    },
    {
      question: "What is the rental time period?",
      answer:
        "Daily Rentals: Daily rentals are based on a 24-hour period. Weekly Rentals: Weekly rentals are based on a week to week period and the same for Monthly rentals. Long Term/Leasing: Terms will be arranged. If the car is returned after the 24-hour period, then an additional charge is assessed."
    },
    {
      question: "Which side of the road do you drive on in Antigua? Can I drive my rental vehicle off road?",
      answer:
        "In Antigua & Barbuda, we drive on the left side of the road. Our rental vehicles should stay on main roads vs off-roads as they are not off-road vehicles."
    },
    {
      question: "What methods of payment do you accept?",
      answer: "We accept cash, and major Credit Cards - Visa & MasterCard."
    },
    {
      question: "What exchange rate do you use from $USD to $ECD?",
      answer:
        "The local currency in Antigua is Eastern Caribbean Dollars (ECD). The fixed bank rate for conversion of USD to ECD is US$1.00 = EC$2.7169. We accept payment in both USD and ECD."
    },
    {
      question: "Will my rental payment cover for damages?",
      answer:
        "Only if accident collision insurance is taken at an additional daily rate indicated in your rental agreement. This is optional."
    },
    {
      question: "Can anyone else drive my rental vehicle?",
      answer: "Yes. Additional driver(s) can be added to rental agreement."
    },
    {
      question: "Do you provide child safety seats?",
      answer:
        "Yes. This option is a complimentary offer (upon availability) and is recommended by Law for children under the age of four."
    },
    {
      question: "How do I navigate around the Island?",
      answer:
        "A complementary offer (upon availability) is given for GPS turn-by-turn navigation. Also, maps are available in your vehicle."
    },
    {
      question: "How should I return my vehicle?",
      answer:
        "All vehicles should be returned in the same condition as when the rental vehicle were delivered or picked up. If the vehicle is not fueled, you will be charged a fuel charge."
    },
    {
      question: "What is the total cost of renting a car?",
      answer:
        "The total includes the base rental fee plus insurance, taxes, and any extras. Request a cost breakdown to avoid surprises."
    },
    {
      question: "What is the fuel policy?",
      answer:
        "Most agreements require you to return the car with a full tank. Returning it with less may incur refueling charges."
    },
    {
      question: "What should I do if I have an accident or mechanical issue?",
      answer:
        "Contact emergency services if needed, then call Ibes Car Rental using the number in your agreement for assistance."
    },
    {
      question: "What happens if I return the car late?",
      answer: "Late returns may incur extra charges. Contact Ibes Car Rental ahead of time if you're running late."
    },
    {
      question: "Can I rent without a reservation?",
      answer: "Yes, but it's recommended to book in advance, especially during peak seasons."
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
  ] satisfies TaxiPackage[],
  features: [
    {
      title: "Quality Vehicles",
      copy: "Each day, we prioritize our clients' well-being above all else. Ensuring safety, cleanliness, and reliability remains our foremost commitment. Our fleet undergoes rigorous maintenance, meeting the highest safety benchmarks for your peace of mind.",
      icon: "fa-solid fa-medal"
    },
    {
      title: "Driving In Antigua",
      copy: "Welcome to our enchanting island nation! Our aim is to enrich your journey as you explore and uncover the beauty of Antigua. As a unique characteristic, we drive on the left, adhering to international driving norms and regulations to ensure a seamless and safe experience for all travelers.",
      icon: "fa-solid fa-car-side"
    },
    {
      title: "Outstanding Service",
      copy: "At Ibes Car Rental, we're dedicated to delivering a service experience that surpasses expectations. Our approach is rooted in honesty, professionalism, and a warm, friendly demeanor towards all our clients. We prioritize your user experience, understanding that the most powerful endorsement comes from satisfied customers sharing their positive experiences through word-of-mouth.",
      icon: "fa-solid fa-thumbs-up"
    },
    {
      title: "Add-On Options",
      copy: "We strive to enhance your journey by offering complimentary child safety seats and GPS turn-by-turn navigation whenever possible. If you're interested, simply inform us during the booking process. Additionally, maps of Antigua are readily available in every vehicle for your convenience. It's worth noting that according to the law, child seats are recommended for children under the age of four.",
      icon: "fa-solid fa-compass"
    },
    {
      title: "24 Hour Support",
      copy: "In our ongoing commitment to exceptional service, we offer round-the-clock support to assist you whenever necessary. While roads and signage might not always be the most straightforward, our intimate knowledge of Antigua ensures that we can promptly reach all our customers in times of need.",
      icon: "fa-solid fa-headset"
    },
    {
      title: "Payment",
      copy: "We gladly accept major credit cards, including Visa, MasterCard, and American Express.\n\nFor discounted rates, simply book for two or more days.",
      icon: "fa-solid fa-credit-card"
    }
  ] satisfies Feature[],
  testimonials: [
    {
      quote:
        "Amazing rentals this is my 3rd time renting. Clean cars and excellent service every time I arrive.",
      name: "Dee Smith"
    },
    {
      quote:
        "They were prompt and friendly. The vehicle matched exactly what was advertised and had no hidden charges.",
      name: "Derek Clive Matthews"
    },
    {
      quote:
        "Very accommodating team with a clean vehicle in great condition. I would definitely rent from them again.",
      name: "Barbara Ann"
    }
  ] satisfies Testimonial[]
};