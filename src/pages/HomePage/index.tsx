import flatpickr from "flatpickr";
import { Instance as FlatpickrInstance } from "flatpickr/dist/types/instance";
import { FormEvent, useEffect, useMemo, useRef, useState } from "react";
import { Link, useNavigate } from "react-router-dom";
import Features, { type Feature } from "../../components/Features";
import Testimonials, { type Testimonial } from "../../components/Testimonials";
import Vehicles from "../../components/Vehicles";
import { getLandingVehicles, type Vehicle } from "../../lib/api";
import "./HomePage.scss";

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

const TESTIMONIALS: Testimonial[] = [
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

export default function HomePage() {
  const navigate = useNavigate();
  const showTestimonials = (import.meta.env.SHOW_TESTIMONIALS ?? "true") === "true";
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
          <Features features={FEATURES} />
        </div>
      </section>

      <section id="landing-cars-section">
        <div className="mobile-paralax" />
        <div className="inner">
          <Vehicles vehicles={vehicles} vehiclesError={vehiclesError} />
          <Link to="/reservation">BOOK NOW</Link>
        </div>
      </section>

      {showTestimonials ? (
        <section id="testimonial-section">
          <div className="inner">
            <Testimonials testimonials={TESTIMONIALS} />
          </div>
        </section>
      ) : null}
    </>
  );
}
