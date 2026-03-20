import flatpickr from "flatpickr";
import { Instance as FlatpickrInstance } from "flatpickr/dist/types/instance";
import { FormEvent, useEffect, useMemo, useRef, useState } from "react";
import { Link, useNavigate } from "react-router-dom";
import { getLandingVehicles, type Vehicle } from "../../lib/api";
import "./HomePage.scss";

type Feature = {
  title: string;
  copy: string;
  icon: string;
};

const FEATURES: Feature[] = [
  {
    title: "Quality Vehicles",
    copy: "Our fleet is cleaned, inspected, and maintained regularly for safe and reliable island driving.",
    icon: "fa-solid fa-star"
  },
  {
    title: "Driving In Antigua",
    copy: "We provide practical guidance for left-side driving so your trip starts smoothly from day one.",
    icon: "fa-solid fa-car-side"
  },
  {
    title: "Outstanding Service",
    copy: "Friendly support, clear communication, and responsive assistance throughout your rental period.",
    icon: "fa-solid fa-thumbs-up"
  },
  {
    title: "Add-On Options",
    copy: "Child seat and navigation requests are available to support your itinerary.",
    icon: "fa-solid fa-compass"
  },
  {
    title: "24 Hour Support",
    copy: "Our local team can assist quickly when you need directions, support, or trip updates.",
    icon: "fa-solid fa-headset"
  },
  {
    title: "Payment",
    copy: "We accept major cards and provide discounted rates on longer rentals.",
    icon: "fa-solid fa-credit-card"
  }
];

const TESTIMONIALS = [
  {
    quote:
      "Amazing rentals this is my 3rd time renting. Clean cars and excellent service every time I arrive.",
    name: "Dee Smith"
  },
  {
    quote:
      "They were prompt and friendly. The vehicle matched exactly what was advertised and had no hidden charges.",
    name: "Derek Clive Matthews"
  },
  {
    quote:
      "Very accommodating team with a clean vehicle in great condition. I would definitely rent from them again.",
    name: "Barbara Ann"
  }
];

function formatVehicleType(type: string): string {
  return type
    .replace(/[_-]/g, " ")
    .trim()
    .replace(/\b\w/g, (char) => char.toUpperCase());
}

export default function HomePage() {
  const navigate = useNavigate();
  const [vehicles, setVehicles] = useState<Vehicle[]>([]);
  const [vehiclesError, setVehiclesError] = useState<string | null>(null);
  const [activeSelect, setActiveSelect] = useState<"pickup" | "return" | null>(null);
  const [returnToSameLocation, setReturnToSameLocation] = useState(true);
  const [pickUpLocation, setPickUpLocation] = useState("Choose Location");
  const [returnLocation, setReturnLocation] = useState("Choose Location");
  const pickUpInputRef = useRef<HTMLInputElement | null>(null);
  const returnInputRef = useRef<HTMLInputElement | null>(null);
  const pickUpPickerRef = useRef<FlatpickrInstance | null>(null);
  const returnPickerRef = useRef<FlatpickrInstance | null>(null);

  const locationOptions = useMemo(() => ["Airport", "Your Hotel"], []);

  useEffect(() => {
    let cancelled = false;

    async function loadVehicles() {
      try {
        const landingVehicles = await getLandingVehicles();

        if (cancelled) {
          return;
        }

        setVehicles(landingVehicles);
        setVehiclesError(null);
      } catch {
        if (cancelled) {
          return;
        }

        setVehicles([]);
        setVehiclesError("Unable to load vehicles right now. Please check back shortly.");
      }
    }

    void loadVehicles();

    return () => {
      cancelled = true;
    };
  }, []);

  useEffect(() => {
    document.documentElement.classList.toggle("viewing-custom-select-options", activeSelect !== null);

    return () => {
      document.documentElement.classList.remove("viewing-custom-select-options");
    };
  }, [activeSelect]);

  useEffect(() => {
    function closeSelect() {
      setActiveSelect(null);
    }

    document.addEventListener("click", closeSelect);
    return () => {
      document.removeEventListener("click", closeSelect);
    };
  }, []);

  useEffect(() => {
    if (!pickUpInputRef.current || !returnInputRef.current) {
      return;
    }

    pickUpPickerRef.current = flatpickr(pickUpInputRef.current, {
      enableTime: true,
      dateFormat: "Y-m-d\\TH:i",
      altInput: true,
      altFormat: "F j, Y h:i K",
      minDate: "today"
    });

    returnPickerRef.current = flatpickr(returnInputRef.current, {
      enableTime: true,
      dateFormat: "Y-m-d\\TH:i",
      altInput: true,
      altFormat: "F j, Y h:i K",
      minDate: "today"
    });

    return () => {
      pickUpPickerRef.current?.destroy();
      returnPickerRef.current?.destroy();
      pickUpPickerRef.current = null;
      returnPickerRef.current = null;
    };
  }, []);

  function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();

    const query = new URLSearchParams();

    if (pickUpLocation !== "Choose Location") {
      query.set("pickUpLocation", pickUpLocation);
    }

    if (!returnToSameLocation && returnLocation !== "Choose Location") {
      query.set("returnLocation", returnLocation);
    }

    const pickUpDate = pickUpPickerRef.current?.input.value;
    const returnDate = returnPickerRef.current?.input.value;

    if (pickUpDate) {
      query.set("pickUpDate", pickUpDate);
    }

    if (returnDate) {
      query.set("returnDate", returnDate);
    }

    navigate(`/reservation${query.toString() ? `?${query.toString()}` : ""}`);
  }

  return (
    <>
      <section id="intro-section">
        <div className="inner">
          <img src="/assets/images/logo.jpeg" alt="Website logo" />

          <form onSubmit={handleSubmit}>
            <h2>PICK UP</h2>
            <div
              className={`custom-select pick-up form-input ${activeSelect === "pickup" ? "active" : ""}`}
              onClick={(event) => {
                event.stopPropagation();
                setActiveSelect((current) => (current === "pickup" ? null : "pickup"));
              }}
            >
              <i className="fa-solid fa-location-dot" aria-hidden />
              <span>{pickUpLocation}</span>
              <i className="fa-solid fa-chevron-down" aria-hidden />
              <div className="custom-select-options">
                <span className={pickUpLocation === "Choose Location" ? "selected" : ""}>Choose Location</span>
                {locationOptions.map((location) => (
                  <span
                    key={location}
                    className={pickUpLocation === location ? "selected" : ""}
                    onClick={() => {
                      setPickUpLocation(location);
                      if (returnToSameLocation) {
                        setReturnLocation(location);
                      }
                      setActiveSelect(null);
                    }}
                  >
                    {location}
                  </span>
                ))}
              </div>
            </div>

            <div className="checkbox-container" onClick={() => setReturnToSameLocation((value) => !value)}>
              <input
                id="return-to-same-location"
                type="checkbox"
                className="hidden-checkbox"
                hidden
                checked={returnToSameLocation}
                readOnly
              />
              <div className="custom-checkbox" />
              <label className="custom-checkbox-label" htmlFor="return-to-same-location">
                Return to the same location
              </label>
            </div>

            <div>
              <input ref={pickUpInputRef} type="text" id="pick-up-flatpickr" className="flatpickr-input" placeholder="Pickup Date" />
            </div>

            <h2>RETURN</h2>
            <div
              className={`custom-select return form-input ${activeSelect === "return" ? "active" : ""}`}
              style={{ display: returnToSameLocation ? "none" : "flex" }}
              onClick={(event) => {
                event.stopPropagation();
                setActiveSelect((current) => (current === "return" ? null : "return"));
              }}
            >
              <i className="fa-solid fa-location-dot" aria-hidden />
              <span>{returnLocation}</span>
              <i className="fa-solid fa-chevron-down" aria-hidden />
              <div className="custom-select-options">
                <span className={returnLocation === "Choose Location" ? "selected" : ""}>Choose Location</span>
                {locationOptions.map((location) => (
                  <span
                    key={`return-${location}`}
                    className={returnLocation === location ? "selected" : ""}
                    onClick={() => {
                      setReturnLocation(location);
                      setActiveSelect(null);
                    }}
                  >
                    {location}
                  </span>
                ))}
              </div>
            </div>
            <div>
              <input ref={returnInputRef} type="text" id="return-flatpickr" className="flatpickr-input" placeholder="Return Date" />
            </div>

            <button type="submit">
              <span>Find a Vehicle</span>
              <i className="fa-solid fa-arrow-right-long" aria-hidden />
            </button>
          </form>
        </div>
      </section>

      <section id="feature-section">
        <div className="inner">
          <h1>Antigua Car Rental Services</h1>
          <div id="features">
            {FEATURES.map((feature) => (
              <div key={feature.title} className="feature-container">
                <div className="feature-icon">
                  <i className={feature.icon} aria-hidden />
                </div>
                <div className="feature-info">
                  <h2>{feature.title}</h2>
                  <p>{feature.copy}</p>
                </div>
              </div>
            ))}
          </div>
        </div>
      </section>

      <section id="landing-cars-section">
        <div className="mobile-paralax" />
        <div className="inner">
          <div id="cars">
            {vehicles.map((vehicle) => (
              <Link className="car-container" key={vehicle.id} to="/reservation">
                <div className="overlay">
                  <div />
                </div>
                <div className="top">
                  <div className="left">
                    <h2>{vehicle.name}</h2>
                    <h3>{`${formatVehicleType(vehicle.type)} - USD$${vehicle.insurance}/day Insurance`}</h3>
                    <div>
                      <span>FROM</span>
                      <span>
                        USD${vehicle.basePriceUsd}
                        <span style={{ fontSize: 15 }}>/</span>
                      </span>
                      <span>DAY</span>
                    </div>
                  </div>
                  <div className="right">
                    <div>
                      <i className="fa-solid fa-user-group" aria-hidden />
                      <span>{vehicle.people} Seats</span>
                    </div>
                    <div>
                      <i className="fa-solid fa-suitcase-rolling" aria-hidden />
                      <span>{vehicle.bags ?? 0} Bags</span>
                    </div>
                    <div>
                      <i className="fa-solid fa-door-open" aria-hidden />
                      <span>{vehicle.doors} Doors</span>
                    </div>
                    {vehicle.fourWd ? (
                      <div>
                        <i className="fa-solid fa-mountain" aria-hidden />
                        <span>4WD</span>
                      </div>
                    ) : null}
                    {vehicle.ac ? (
                      <div>
                        <i className="fa-solid fa-snowflake" aria-hidden />
                        <span>A/C</span>
                      </div>
                    ) : null}
                  </div>
                </div>
                <div className="bottom">
                  <img loading="lazy" src={`/assets/images/vehicles/${vehicle.slug}.avif`} alt={`${vehicle.name} thumbnail`} />
                </div>
                {/* {vehicle.discountDays && vehicle.discountDays > 0 ? (
                  <div className="discount-text">{`${vehicle.discountDays}+ days are discounted`}</div>
                ) : null} */}
              </Link>
            ))}
            {vehiclesError ? <p>{vehiclesError}</p> : null}
          </div>
          <Link to="/reservation">BOOK NOW</Link>
        </div>
      </section>

      <section id="testimonial-section">
        <div className="inner">
          <div id="testimonials">
            {TESTIMONIALS.map((testimonial) => (
              <div key={testimonial.name} className="testimonial">
                <i className="fa-solid fa-quote-left" aria-hidden />
                <div>
                  <p>{testimonial.quote}</p>
                  <span>{testimonial.name}</span>
                </div>
                <i className="fa-solid fa-quote-right" aria-hidden />
              </div>
            ))}
          </div>
        </div>
      </section>
    </>
  );
}
