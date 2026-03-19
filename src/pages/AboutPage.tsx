import { siteData } from "../data/siteData";

export default function AboutPage() {
  return (
    <section className="page section">
      <div className="container">
        <div className="page-header narrow">
          <p className="eyebrow">About</p>
          <h1>About Ibes Car Rental</h1>
        </div>

        <div className="about-grid">
          {siteData.aboutSections.map((section) => (
            <article key={section.title} className="about-card">
              <img src={section.image} alt={section.imageAlt} loading="lazy" />
              <div className="about-copy">
                <h2>{section.title}</h2>
                <div className="stack">
                  {section.paragraphs.map((paragraph) => (
                    <p key={paragraph}>{paragraph}</p>
                  ))}
                </div>

                {section.bullets ? (
                  <ul className="bullet-list">
                    {section.bullets.map((bullet) => (
                      <li key={bullet}>{bullet}</li>
                    ))}
                  </ul>
                ) : null}
              </div>
            </article>
          ))}
        </div>
      </div>
    </section>
  );
}
