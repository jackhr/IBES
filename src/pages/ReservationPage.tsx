import { useEffect, useState } from "react";

const INTEGRATOR_SCRIPT_SRC =
  "https://ibes-car-rental-and-taxi-service.hqrentals.app/public/car-rental/integrations/assets/integrator";
const INTEGRATOR_LINK = "https://ibes-car-rental-and-taxi-service.hqrentals.app/public/car-rental/integrations";
const BRAND_ID = "l8ivujtj-69ui-2yks-znlo-jubptz7rrmul";

export default function ReservationPage() {
  const [loadError, setLoadError] = useState(false);

  useEffect(() => {
    const existingScript = document.querySelector<HTMLScriptElement>("script[data-hq-integrator='true']");

    if (existingScript) {
      return;
    }

    const script = document.createElement("script");
    script.src = INTEGRATOR_SCRIPT_SRC;
    script.async = true;
    script.dataset.hqIntegrator = "true";
    script.onerror = () => setLoadError(true);

    document.body.appendChild(script);
  }, []);

  return (
    <section className="page section">
      <div className="container narrow">
        <div className="page-header">
          <p className="eyebrow">Reservation</p>
          <h1>Start your booking</h1>
          <p>Use the booking module below to select dates, vehicle options, and complete your reservation request.</p>
        </div>

        {loadError ? (
          <p className="status error">
            Booking module failed to load. Please refresh the page or contact us directly by phone.
          </p>
        ) : null}

        <div
          className="hq-rental-software-integration"
          data-integrator_link={INTEGRATOR_LINK}
          data-brand={BRAND_ID}
          data-snippet="reservations"
          data-skip_language=""
          data-rate_type_uuid=""
          data-referral=""
          data-enable_auto_language_update=""
        />
      </div>
    </section>
  );
}
