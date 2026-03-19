import { Link } from "react-router-dom";
import { siteData } from "../data/siteData";

export default function HomePage() {
  return (
    <>
      <section className="hero-page">
        <div className="hero-media">
          <img src={siteData.heroImage} alt="Scenic Antigua coastline" />
        </div>
        <div className="hero-overlay" />

        <div className="container hero-content">
          <p className="eyebrow">Antigua Car Rental & Taxi Services</p>
          <h1>{siteData.heroTitle}</h1>
          <p>{siteData.heroSubtitle}</p>

          <div className="hero-actions">
            <Link to="/reservation" className="btn btn-primary">
              Start Reservation
            </Link>
            <Link to="/taxi" className="btn btn-outline">
              Book Taxi
            </Link>
          </div>

          <div className="metrics-grid">
            {siteData.metrics.map((item) => (
              <div key={item.label} className="metric-card">
                <span>{item.value}</span>
                <small>{item.label}</small>
              </div>
            ))}
          </div>
        </div>
      </section>

      <section className="section section-features">
        <div className="container">
          <div className="section-heading">
            <h2>Built for smooth island travel</h2>
            <p>From arrival pickup to your final drop-off, we focus on practical service and responsive communication.</p>
          </div>

          <div className="feature-grid">
            {siteData.features.map((feature) => (
              <article key={feature.title} className="feature-card">
                <h3>{feature.title}</h3>
                <p>{feature.description}</p>
              </article>
            ))}
          </div>
        </div>
      </section>
    </>
  );
}
