import { useEffect, useState } from "react";
import { Link, NavLink, useLocation } from "react-router-dom";
import "./Header.scss";

export default function Header() {
  const [open, setOpen] = useState(false);
  const location = useLocation();

  const navLinks = [
    { label: "Home", path: "/" },
    { label: "Book Now", path: "/reservation" },
    { label: "Taxi", path: "/taxi" },
    { label: "About", path: "/about" },
    { label: "FAQ", path: "/faq" },
    { label: "Contact", path: "/contact" }
  ];

  useEffect(() => {
    setOpen(false);
  }, [location.pathname]);

  useEffect(() => {
    const root = document.documentElement;
    root.classList.toggle("viewing-hamburger-menu", open);

    return () => {
      root.classList.remove("viewing-hamburger-menu");
    };
  }, [open]);

  return (
    <>
      <div className="overlay" onClick={() => setOpen(false)} aria-hidden />
      <header>
        <div className="inner">
          <Link to="/" aria-label="Ibes Car Rental Home">
            <img src="/assets/images/logo.jpeg" alt="Website logo" />
          </Link>

          <nav>
            {navLinks.map((item) => (
              <NavLink key={item.path} to={item.path}>
                {item.label}
              </NavLink>
            ))}
          </nav>

          <button
            id="hamburger-button"
            type="button"
            aria-label={open ? "Close navigation menu" : "Open navigation menu"}
            aria-expanded={open}
            onClick={() => setOpen((value) => !value)}
          >
            <div id="hamburger-icon">
              <div className="hamburger-line" />
              <div className="hamburger-line" />
              <div className="hamburger-line" />
            </div>
          </button>

          <div id="hamburger-nav" aria-hidden={!open}>
            <button id="close-hamburger" type="button" aria-label="Close menu" onClick={() => setOpen(false)}>
              <i className="fa-solid fa-xmark" />
            </button>
            <nav>
              {navLinks.map((item) => (
                <NavLink key={`mobile-${item.path}`} to={item.path} onClick={() => setOpen(false)}>
                  {item.label}
                </NavLink>
              ))}
            </nav>
          </div>
        </div>
      </header>
    </>
  );
}
