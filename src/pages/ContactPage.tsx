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
      await postJson("/api/contact", payload);
      form.reset();
      await showSuccessAlert("Message Sent", "Thanks for reaching out. Our team will respond shortly.");
    } catch {
      await showErrorAlert("Send Failed", "Unable to send right now. Please try again.");
    } finally {
      setSending(false);
    }
  }

  return (
    <>
      <section className="general-header">
        <h1>Contact Ibes</h1>
      </section>

      <section id="contact-card-section">
        <div className="inner">
          <h2>{siteData.companyName}</h2>

          <div className="contact-brief-info">
            <span>Ibes Car Rental</span>
            <span>Coolidge</span>
            <span>St. George, Antigua</span>
          </div>

          <div>
            <div className="contact-link">
              <span>Phone:</span>
              <a href={`tel:${siteData.phone.replace(/[^\d+]/g, "")}`}>{siteData.phone}</a>
            </div>
            <div className="contact-link">
              <span>Email:</span>
              <a href={`mailto:${siteData.email}`}>{siteData.email}</a>
            </div>
          </div>

          <div className="contact-brief-info">
            <span>Service Hours</span>
            <span>Monday to Sunday</span>
            <span>8:00 am to 8:00 pm</span>
          </div>
        </div>
      </section>

      <section id="contact-form-section">
        <div className="inner">
          <h2>SEND US AN EMAIL</h2>
          <form onSubmit={handleSubmit}>
            <div className="left">
              <div className="input-container">
                <label htmlFor="contact-message">Your Message</label>
                <textarea
                  id="contact-message"
                  name="message"
                  cols={30}
                  rows={10}
                  placeholder="Enter your message..."
                  required
                />
              </div>
            </div>
            <div className="right">
              <div className="input-container">
                <label htmlFor="contact-name">Name *</label>
                <input id="contact-name" name="name" type="text" placeholder="Enter your name" required />
              </div>
              <div className="input-container">
                <label htmlFor="contact-email">Email *</label>
                <input id="contact-email" name="email" type="email" placeholder="email@domain.com" required />
              </div>
              <input name="h826r2whj4fi_cjz8jxs2zuwahhhk6" type="text" autoComplete="off" tabIndex={-1} className="hp-field" />
              <button type="submit" disabled={sending}>
                {sending ? "SENDING..." : "SUBMIT"}
              </button>
            </div>
          </form>
        </div>
      </section>
    </>
  );
}
