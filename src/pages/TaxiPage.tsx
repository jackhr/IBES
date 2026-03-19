import flatpickr from "flatpickr";
import { Instance as FlatpickrInstance } from "flatpickr/dist/types/instance";
import { FormEvent, useEffect, useRef, useState } from "react";
import { siteData } from "../data/siteData";
import { showErrorAlert, showSuccessAlert } from "../lib/alerts";
import { postJson } from "../lib/http";

export default function TaxiPage() {
  const [sending, setSending] = useState(false);
  const pickUpInputRef = useRef<HTMLInputElement | null>(null);
  const pickUpPickerRef = useRef<FlatpickrInstance | null>(null);

  useEffect(() => {
    if (!pickUpInputRef.current) {
      return;
    }

    pickUpPickerRef.current = flatpickr(pickUpInputRef.current, {
      enableTime: true,
      dateFormat: "Y-m-d\\TH:i",
      altInput: true,
      altFormat: "F j, Y h:i K",
      minDate: "today"
    });

    return () => {
      pickUpPickerRef.current?.destroy();
      pickUpPickerRef.current = null;
    };
  }, []);

  async function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    const form = event.currentTarget;
    const formData = new FormData(form);

    const payload = {
      name: formData.get("name"),
      phone: formData.get("phone"),
      email: formData.get("email"),
      pickUp: formData.get("pickUp"),
      dropOff: formData.get("dropOff"),
      passengers: formData.get("passengers"),
      pickUpTime: formData.get("pickUpTime"),
      message: formData.get("message"),
      h826r2whj4fi_cjz8jxs2zuwahhhk6: formData.get("h826r2whj4fi_cjz8jxs2zuwahhhk6")
    };

    setSending(true);

    try {
      await postJson("/api/taxi-request", payload);
      form.reset();
      pickUpPickerRef.current?.clear();
      await showSuccessAlert("Taxi Request Sent", "Thanks. We will reply with confirmation details shortly.");
    } catch {
      await showErrorAlert("Send Failed", "Unable to send right now. Please try again.");
    } finally {
      setSending(false);
    }
  }

  return (
    <section className="page section">
      <div className="container">
        <div className="page-header narrow">
          <p className="eyebrow">Taxi</p>
          <h1>Taxi Reservation</h1>
          <p>Send your route details and we will reply with confirmation within 24 hours.</p>
        </div>

        <div className="taxi-grid">
          {siteData.taxiPackages.map((pkg) => (
            <article key={pkg.title} className="taxi-card">
              <h2>{pkg.title}</h2>
              <ul>
                {pkg.details.map((detail) => (
                  <li key={detail}>
                    <i className="fa-solid fa-circle-check" aria-hidden />
                    <span>{detail}</span>
                  </li>
                ))}
              </ul>
            </article>
          ))}
        </div>

        <form className="form taxi-form" onSubmit={handleSubmit}>
          <label>
            Name
            <input name="name" type="text" required />
          </label>
          <label>
            Phone
            <input name="phone" type="tel" required />
          </label>
          <label>
            Email
            <input name="email" type="email" required />
          </label>
          <label>
            Pick-up Location
            <input name="pickUp" type="text" required />
          </label>
          <label>
            Drop-off Location
            <input name="dropOff" type="text" required />
          </label>
          <label>
            Passengers
            <input name="passengers" type="number" min="1" required />
          </label>
          <label>
            Pick-up Time
            <input ref={pickUpInputRef} name="pickUpTime" type="text" placeholder="Select date and time" required />
          </label>
          <label className="full-width">
            Special Requirements
            <textarea name="message" rows={4} />
          </label>

          <input name="h826r2whj4fi_cjz8jxs2zuwahhhk6" type="text" autoComplete="off" tabIndex={-1} className="hp-field" />

          <button type="submit" className="btn btn-primary" disabled={sending}>
            {sending ? "Sending..." : "Send Taxi Request"}
          </button>
        </form>
      </div>
    </section>
  );
}
