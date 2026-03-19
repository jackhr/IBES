import { NavLink } from "react-router-dom";
import { siteData } from "../data/siteData";

export default function Footer() {
  const year = new Date().getFullYear();

  return (
    <footer className="site-footer">
      <div className="container footer-grid">
        <div className="footer-brand">
          <img src="/assets/images/logo-gray.avif" alt="Ibes Car Rental logo" loading="lazy" />
          <p>{siteData.companyName}</p>
          <p>{siteData.location}</p>
        </div>

        <div className="footer-nav" aria-label="Footer navigation">
          {siteData.nav.map((item) => (
            <NavLink key={`footer-${item.path}`} to={item.path}>
              {item.label}
            </NavLink>
          ))}
        </div>

        <div className="footer-contact">
          <a href={`tel:${siteData.phone.replace(/[^\d+]/g, "")}`} className="icon-link">
            <i className="fa-solid fa-phone" aria-hidden />
            {siteData.phone}
          </a>
          <a href={`mailto:${siteData.email}`} className="icon-link">
            <i className="fa-solid fa-envelope" aria-hidden />
            {siteData.email}
          </a>
          <p className="icon-text">
            <i className="fa-solid fa-wallet" aria-hidden />
            US$ to EC$ Exchange at 2.7
          </p>
        </div>
      </div>
      <div className="footer-legal">Copyright {year} {siteData.companyName}</div>
    </footer>
  );
}
