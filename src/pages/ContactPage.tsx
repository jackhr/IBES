import { FormEvent, useState } from "react";
import { siteData } from "../data/siteData";
import { showErrorAlert, showSuccessAlert } from "../lib/alerts";
import { postJson } from "../lib/http";

export default function ContactPage() {
  const [sending, setSending] = useState(false);

  async function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    const form = event.currentTarget;
    const formData = new FormData(form);

    const payload = {
      name: formData.get("name"),
      email: formData.get("email"),
      message: formData.get("message"),
      h826r2whj4fi_cjz8jxs2zuwahhhk6: formData.get("h826r2whj4fi_cjz8jxs2zuwahhhk6")
    };

    setSending(true);

    try {
      await postJson("/includes/contact-send.php", payload);
      form.reset();
      await showSuccessAlert("Message Sent", "Thanks for reaching out. Our team will respond shortly.");
    } catch {
      await showErrorAlert("Send Failed", "Unable to send right now. Please try again.");
    } finally {
      setSending(false);
    }
  }

  return (
    <section className="page section">
      <div className="container contact-layout">
        <div className="contact-card">
          <p className="eyebrow">Contact</p>
          <h1>Talk to our team</h1>
          <p>
            Need help with a booking or airport transfer? Call or email us and we will respond as quickly as possible.
          </p>

          <a href={`tel:${siteData.phone.replace(/[^\d+]/g, "")}`} className="icon-link">
            <i className="fa-solid fa-phone" aria-hidden />
            {siteData.phone}
          </a>
          <a href={`mailto:${siteData.email}`} className="icon-link">
            <i className="fa-solid fa-envelope" aria-hidden />
            {siteData.email}
          </a>
          <p className="icon-text">
            <i className="fa-solid fa-location-dot" aria-hidden />
            {siteData.location}
          </p>
          <p>Service hours: Monday to Sunday, 8:00 am to 8:00 pm</p>
        </div>

        <form className="form" onSubmit={handleSubmit}>
          <label>
            Name
            <input name="name" type="text" required />
          </label>
          <label>
            Email
            <input name="email" type="email" required />
          </label>
          <label>
            Message
            <textarea name="message" rows={6} required />
          </label>

          <input name="h826r2whj4fi_cjz8jxs2zuwahhhk6" type="text" autoComplete="off" tabIndex={-1} className="hp-field" />

          <button type="submit" className="btn btn-primary" disabled={sending}>
            {sending ? "Sending..." : "Send Message"}
          </button>
        </form>
      </div>
    </section>
  );
}
