import { useEffect, useState } from "react";
import { NavLink, useLocation } from "react-router-dom";
import { siteData } from "../data/siteData";

export default function Header() {
  const [open, setOpen] = useState(false);
  const location = useLocation();

  useEffect(() => {
    setOpen(false);
  }, [location.pathname]);

  return (
    <header className="site-header">
      <div className="container header-inner">
        <NavLink to="/" className="brand" aria-label={`${siteData.companyName} home`}>
          <span className="brand-mark" aria-hidden>
            I
          </span>
          <span>{siteData.brand}</span>
        </NavLink>

        <button
          className={`menu-toggle ${open ? "is-open" : ""}`}
          type="button"
          aria-expanded={open}
          aria-label={open ? "Close navigation" : "Open navigation"}
          onClick={() => setOpen((current) => !current)}
        >
          <span />
          <span />
          <span />
        </button>

        <nav className={`site-nav ${open ? "is-open" : ""}`}>
          {siteData.nav.map((item) => (
            <NavLink key={item.path} to={item.path} className="nav-link">
              {item.label}
            </NavLink>
          ))}
        </nav>
      </div>
    </header>
  );
}
