import { FormEvent, useState } from "react";
import { siteData } from "../data/siteData";
import { postJson } from "../lib/http";

type Status = "idle" | "sending" | "success" | "error";

export default function TaxiPage() {
  const [status, setStatus] = useState<Status>("idle");

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

    setStatus("sending");

    try {
      await postJson("/includes/taxi-request-send.php", payload);
      form.reset();
      setStatus("success");
    } catch {
      setStatus("error");
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
                  <li key={detail}>{detail}</li>
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
            <input name="pickUpTime" type="datetime-local" required />
          </label>
          <label className="full-width">
            Special Requirements
            <textarea name="message" rows={4} />
          </label>

          <input name="h826r2whj4fi_cjz8jxs2zuwahhhk6" type="text" autoComplete="off" tabIndex={-1} className="hp-field" />

          <button type="submit" className="btn btn-primary" disabled={status === "sending"}>
            {status === "sending" ? "Sending..." : "Send Taxi Request"}
          </button>

          {status === "success" ? <p className="status success">Taxi request sent successfully.</p> : null}
          {status === "error" ? <p className="status error">Unable to send right now. Please try again.</p> : null}
        </form>
      </div>
    </section>
  );
}
