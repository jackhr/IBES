import { FormEvent, useMemo, useState } from "react";
import { useLocation, useNavigate } from "react-router-dom";

export default function ConfirmationPage() {
  const location = useLocation();
  const navigate = useNavigate();
  const [keyInput, setKeyInput] = useState("");
  const existingKey = useMemo(() => new URLSearchParams(location.search).get("key"), [location.search]);

  function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    const key = keyInput.trim();

    if (!key) {
      return;
    }

    navigate(`/confirmation?key=${encodeURIComponent(key)}`);
  }

  return (
    <>
      <section className="general-header">
        <h1>Order Confirmation</h1>
      </section>

      {existingKey ? (
        <section id="confirmation-section">
          <div className="inner">
            <h2>Thank you! Your order has been requested.</h2>
            <div className="reservation-flow-container">
              <div className="left">
                <div id="reservation-summary">
                  <h5>Reservation Summary</h5>
                  <h6>Your confirmation details were received successfully.</h6>
                  <div className="summary car">
                    <img src="/assets/images/logo-gray.avif" alt="Ibes logo" />
                  </div>
                  <div className="summary rate">
                    <h6>Order Key</h6>
                    <div>
                      <span>Confirmation</span>
                      <span>{existingKey}</span>
                    </div>
                  </div>
                </div>
              </div>
              <div className="right">
                <div className="order-header">
                  <span>Order Key:</span>
                  <span>{existingKey}</span>
                </div>
                <h6>Summary</h6>
                <div id="order-summary">
                  <div className="order-summary-item">
                    <div>
                      <h6>Status</h6>
                      <p>Requested</p>
                    </div>
                    <div>
                      <h6>Next Step</h6>
                      <p>Our team will contact you shortly.</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>
      ) : (
        <section id="key-submit-section">
          <div className="inner">
            <h2>
              Need to review your request? <span>Enter your confirmation key below.</span>
            </h2>
            <form id="confirmation-key-form" onSubmit={handleSubmit}>
              <div className="input-container">
                <label htmlFor="confirmation-key">Confirmation Key</label>
                <input
                  id="confirmation-key"
                  name="key"
                  type="text"
                  placeholder="Enter your key"
                  value={keyInput}
                  onChange={(event) => setKeyInput(event.target.value)}
                  required
                />
              </div>
              <button type="submit">SUBMIT</button>
            </form>
          </div>
        </section>
      )}
    </>
  );
}
