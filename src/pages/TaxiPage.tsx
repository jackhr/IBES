import flatpickr from "flatpickr";
import { Instance as FlatpickrInstance } from "flatpickr/dist/types/instance";
import { FormEvent, useEffect, useRef, useState } from "react";
import { showErrorAlert, showSuccessAlert } from "../lib/alerts";
import { postJson } from "../lib/http";

export default function TaxiPage() {
  const [sending, setSending] = useState(false);
  const [message, setMessage] = useState("");
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
      setMessage("");
      pickUpPickerRef.current?.clear();
      await showSuccessAlert("Taxi Request Sent", "Thanks. We will reply with confirmation details shortly.");
    } catch {
      await showErrorAlert("Send Failed", "Unable to send right now. Please try again.");
    } finally {
      setSending(false);
    }
  }

  return (
    <>
      <section className="general-header">
        <h1>Taxi Reservation</h1>
      </section>

      <section id="taxi-info-section">
        <div className="inner">
          <h2>Transportation Tailored for You</h2>
          <div id="taxi-info-card-container">
            <div className="taxi-info-card">
              <h3>Cruise Ship Pickup</h3>
              <div>
                <span>Pickup from cruise</span>
                <span>Quick and easy ride</span>
                <span>US$100 per person / 4-hour island tour</span>
              </div>
            </div>
            <div className="taxi-info-card">
              <h3>Island Tour Package</h3>
              <div>
                <span>US$100 per person</span>
                <span>4-hour island tour</span>
                <span>Comfortable ride</span>
              </div>
            </div>
            <div className="taxi-info-card">
              <h3>VIP Service</h3>
              <div>
                <span>Personalized requests</span>
                <span>Tailored experience</span>
                <span>Contact for details</span>
              </div>
            </div>
            <div className="taxi-info-card">
              <h3>Private Airport Transfer</h3>
              <div>
                <span>Transfer to/from airport</span>
                <span>Additional US$10 regulation fee</span>
                <span>Reliable taxi service</span>
              </div>
            </div>
          </div>
          <p style={{ margin: "50px auto 0", fontWeight: "bold" }}>
            Send us your request details and we will reply within 24 hours
          </p>
        </div>
      </section>

      <section id="contact-form-section">
        <div className="inner">
          <h2>Reserve Your Taxi</h2>
          <form onSubmit={handleSubmit}>
            <div className="left">
              <div className="mutiple-input-container">
                <div className="input-container">
                  <label htmlFor="contact-pick-up">
                    Pick Up Location<sup>*</sup>
                  </label>
                  <input className="form-input" id="contact-pick-up" name="pickUp" type="text" placeholder="Pick up location" required />
                </div>
                <div className="input-container">
                  <label htmlFor="contact-drop-off">
                    Drop Off Location<sup>*</sup>
                  </label>
                  <input className="form-input" id="contact-drop-off" name="dropOff" type="text" placeholder="Drop off location" required />
                </div>
              </div>
              <div className="mutiple-input-container">
                <div className="input-container">
                  <label htmlFor="contact-pick-up-time">
                    Pick Up Time<sup>*</sup>
                  </label>
                  <input
                    ref={pickUpInputRef}
                    className="form-input"
                    id="contact-pick-up-time"
                    name="pickUpTime"
                    type="text"
                    placeholder="Pick up time"
                    required
                  />
                </div>
                <div className="input-container">
                  <label htmlFor="contact-passengers">
                    Number of Passengers<sup>*</sup>
                  </label>
                  <input className="form-input" id="contact-passengers" name="passengers" type="number" min="1" placeholder="5 People" required />
                </div>
              </div>
              <div className="mutiple-input-container">
                <div className="input-container">
                  <label htmlFor="contact-phone">
                    Phone <sup>*</sup>
                  </label>
                  <input className="form-input" id="contact-phone" name="phone" type="tel" placeholder="+1 (234) 565-4321" required />
                </div>
                <div className="input-container">
                  <label htmlFor="contact-email">
                    Email <sup>*</sup>
                  </label>
                  <input className="form-input" id="contact-email" name="email" type="email" placeholder="my_name@email.com" required />
                </div>
              </div>
              <div className="input-container name-container">
                <label htmlFor="contact-name">
                  Name <sup>*</sup>
                </label>
                <input className="form-input" id="contact-name" name="name" type="text" placeholder="Enter your name" required />
              </div>
              <div className="input-container taxi-message" style={{ margin: 0 }}>
                <label htmlFor="contact-message-mobile">Special Requirements</label>
                <textarea
                  id="contact-message-mobile"
                  name="message"
                  cols={30}
                  rows={10}
                  placeholder="Enter any extra details..."
                  value={message}
                  onChange={(event) => setMessage(event.target.value)}
                />
              </div>
              <input name="h826r2whj4fi_cjz8jxs2zuwahhhk6" type="text" autoComplete="off" tabIndex={-1} className="hp-field" />
              <button type="submit" disabled={sending}>
                {sending ? "SUBMITTING..." : "SUBMIT RESERVATION"}
              </button>
            </div>
            <div className="right">
              <div className="input-container taxi-message" style={{ margin: 0 }}>
                <label htmlFor="contact-message-desktop">Special Requirements</label>
                <textarea
                  id="contact-message-desktop"
                  name="message"
                  cols={30}
                  rows={10}
                  placeholder="Enter any extra details..."
                  value={message}
                  onChange={(event) => setMessage(event.target.value)}
                />
              </div>
            </div>
          </form>
        </div>
      </section>
    </>
  );
}
