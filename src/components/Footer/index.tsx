import { Link } from "react-router-dom";
import { siteData } from "../../data/siteData";
import "./Footer.scss";

export default function Footer() {
  const year = new Date().getFullYear();

  return (
    <footer>
      <section id="contact-banner" />
      <div className="inner">
        <div>
          <div className="footer-nav">
            <h6>Navigation</h6>
            <ul>
              <li>
                <Link to="/">Home</Link>
              </li>
              <li>
                <Link to="/reservation">Book Now</Link>
              </li>
              <li>
                <Link to="/taxi">Taxi</Link>
              </li>
              <li className="confirmation-link">
                <Link to="/confirmation">Your Reservation</Link>
              </li>
              <li>
                <Link to="/about">About</Link>
              </li>
              <li>
                <Link to="/faq">FAQ</Link>
              </li>
              <li>
                <Link to="/contact">Contact</Link>
              </li>
            </ul>
          </div>
          <div className="footer-logo">
            <div>
              <img src="/assets/images/logo.jpeg" alt="Website logo" loading="lazy" />
            </div>
          </div>
          <div className="footer-contact">
            <h6>Contact</h6>
            <div className="contact-link">
              <span>Phone:</span>
              <a href={`tel:${siteData.phone.replace(/[^\d+]/g, "")}`}>{siteData.phone}</a>
            </div>
            <div className="contact-link">
              <span>Email:</span>
              <a href={`mailto:${siteData.email}`}>{siteData.email}</a>
            </div>
          </div>
        </div>
        <div id="copyright">
          {`© ${year} ${siteData.companyName} | US$ to EC$ Exchange at 2.7 | For discounted rate, enter 3 days or more.`}
        </div>
      </div>
    </footer>
  );
}
