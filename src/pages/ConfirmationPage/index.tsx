import { FormEvent, useEffect, useMemo, useState } from "react";
import { useLocation, useNavigate } from "react-router-dom";
import { getOrderRequestByKey, type OrderRequest } from "../../lib/api";
import "./ConfirmationPage.scss";

function formatOrderDate(dateString: string): string {
  const date = new Date(dateString);

  if (Number.isNaN(date.getTime())) {
    return dateString;
  }

  return date.toLocaleString();
}

export default function ConfirmationPage() {
  const location = useLocation();
  const navigate = useNavigate();
  const [keyInput, setKeyInput] = useState("");
  const [orderRequest, setOrderRequest] = useState<OrderRequest | null>(null);
  const [loadingOrder, setLoadingOrder] = useState(false);
  const [orderError, setOrderError] = useState<string | null>(null);
  const existingKey = useMemo(() => new URLSearchParams(location.search).get("key"), [location.search]);

  useEffect(() => {
    if (!existingKey) {
      setOrderRequest(null);
      setOrderError(null);
      return;
    }

    let cancelled = false;
    setLoadingOrder(true);
    setOrderError(null);
    setOrderRequest(null);

    getOrderRequestByKey(existingKey)
      .then((response) => {
        if (cancelled) {
          return;
        }

        setOrderRequest(response);
      })
      .catch(() => {
        if (cancelled) {
          return;
        }

        setOrderRequest(null);
        setOrderError("We could not find a reservation for that confirmation key.");
      })
      .finally(() => {
        if (!cancelled) {
          setLoadingOrder(false);
        }
      });

    return () => {
      cancelled = true;
    };
  }, [existingKey]);

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
            <h2>{orderError ? "Confirmation Key Not Found" : "Thank you! Your order has been requested."}</h2>
            <div className="reservation-flow-container">
              <div className="left">
                <div id="reservation-summary">
                  <h5>Reservation Summary</h5>
                  <h6>{orderError ? orderError : "Your confirmation details were received successfully."}</h6>
                  <div className="summary car">
                    <img src="/assets/images/logo.avif" alt="Ibes logo" />
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
                  {loadingOrder ? (
                    <div className="order-summary-item">
                      <div>
                        <h6>Status</h6>
                        <p>Loading</p>
                      </div>
                      <div>
                        <h6>Next Step</h6>
                        <p>Retrieving reservation details.</p>
                      </div>
                    </div>
                  ) : orderRequest ? (
                    <>
                      <div className="order-summary-item">
                        <div>
                          <h6>Status</h6>
                          <p>{orderRequest.confirmed ? "Confirmed" : "Requested"}</p>
                        </div>
                        <div>
                          <h6>Total</h6>
                          <p>USD${orderRequest.subTotal}</p>
                        </div>
                      </div>
                      <div className="order-summary-item">
                        <div>
                          <h6>Pickup</h6>
                          <p>{`${orderRequest.pickUpLocation} - ${formatOrderDate(orderRequest.pickUp)}`}</p>
                        </div>
                        <div>
                          <h6>Dropoff</h6>
                          <p>{`${orderRequest.dropOffLocation} - ${formatOrderDate(orderRequest.dropOff)}`}</p>
                        </div>
                      </div>
                    </>
                  ) : (
                    <div className="order-summary-item">
                      <div>
                        <h6>Status</h6>
                        <p>Not Found</p>
                      </div>
                      <div>
                        <h6>Next Step</h6>
                        <p>Check your confirmation key and try again.</p>
                      </div>
                    </div>
                  )}
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
