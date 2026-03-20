import { useEffect } from "react";
import { Route, Routes, useLocation } from "react-router-dom";
import Header from "./components/Header";
import Footer from "./components/Footer";
import HomePage from "./pages/HomePage";
import AboutPage from "./pages/AboutPage";
import FaqPage from "./pages/FaqPage";
import ContactPage from "./pages/ContactPage";
import ReservationPage from "./pages/ReservationPage";
import TaxiPage from "./pages/TaxiPage";
import ConfirmationPage from "./pages/ConfirmationPage";
import NotFoundPage from "./pages/NotFoundPage";
import UnderConstructionPage from "./pages/UnderConstructionPage";
import { library } from "@fortawesome/fontawesome-svg-core";
import { fas } from "@fortawesome/free-solid-svg-icons";
import { far } from "@fortawesome/free-regular-svg-icons";
import { fab } from "@fortawesome/free-brands-svg-icons";

export default function App() {
  const location = useLocation();
  const underConstructionEnabled = import.meta.env.UNDER_CONSTRUCTION === "true";

  useEffect(() => {
    window.scrollTo({ top: 0, behavior: "auto" });
  }, [location.pathname]);

  useEffect(() => {
    if (underConstructionEnabled) {
      document.body.id = "under-construction-page";
      return;
    }

    const routeIdMap: Record<string, string> = {
      "/": "index-page",
      "/about": "about-page",
      "/faq": "faq-page",
      "/contact": "contact-page",
      "/reservation": "reservation-page",
      "/taxi": "taxi-page",
      "/confirmation": "confirmation-page"
    };

    document.body.id = routeIdMap[location.pathname] ?? "index-page";
  }, [location.pathname, underConstructionEnabled]);

  library.add(fas, far, fab);

  return (
    <>
      {underConstructionEnabled ? null : <Header />}
      <main>
        <Routes>
          {underConstructionEnabled ? (
            <Route path="*" element={<UnderConstructionPage />} />
          ) : (
            <>
              <Route path="/" element={<HomePage />} />
              <Route path="/about" element={<AboutPage />} />
              <Route path="/faq" element={<FaqPage />} />
              <Route path="/contact" element={<ContactPage />} />
              <Route path="/reservation" element={<ReservationPage />} />
              <Route path="/taxi" element={<TaxiPage />} />
              <Route path="/confirmation" element={<ConfirmationPage />} />
              <Route path="*" element={<NotFoundPage />} />
            </>
          )}
        </Routes>
      </main>
      {underConstructionEnabled ? null : <Footer />}
    </>
  );
}
