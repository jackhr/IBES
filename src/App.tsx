import { Suspense, lazy, useEffect } from "react";
import { Route, Routes, useLocation } from "react-router-dom";
import Header from "./components/Header";
import Footer from "./components/Footer";

const HomePage = lazy(() => import("./pages/HomePage"));
const AboutPage = lazy(() => import("./pages/AboutPage"));
const FaqPage = lazy(() => import("./pages/FaqPage"));
const ContactPage = lazy(() => import("./pages/ContactPage"));
const ReservationPage = lazy(() => import("./pages/ReservationPage"));
const TaxiPage = lazy(() => import("./pages/TaxiPage"));
const ConfirmationPage = lazy(() => import("./pages/ConfirmationPage"));
const NotFoundPage = lazy(() => import("./pages/NotFoundPage"));
const UnderConstructionPage = lazy(() => import("./pages/UnderConstructionPage"));

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

  return (
    <>
      {underConstructionEnabled ? null : <Header />}
      <main>
        <Suspense fallback={null}>
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
        </Suspense>
      </main>
      {underConstructionEnabled ? null : <Footer />}
    </>
  );
}
