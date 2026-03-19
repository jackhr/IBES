import { siteData } from "../data/siteData";

export default function FaqPage() {
  return (
    <section className="page section">
      <div className="container narrow">
        <div className="page-header">
          <p className="eyebrow">FAQ</p>
          <h1>Frequently Asked Questions</h1>
          <p>Select a question to view details.</p>
        </div>

        <div className="faq-list">
          {siteData.faqs.map((item) => (
            <details key={item.question} className="faq-item">
              <summary>{item.question}</summary>
              <p>{item.answer}</p>
            </details>
          ))}
        </div>
      </div>
    </section>
  );
}
