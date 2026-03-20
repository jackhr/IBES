import { useState } from "react";
import { siteData } from "../../data/siteData";
import "./FaqPage.scss";

export default function FaqPage() {
  const [openIndex, setOpenIndex] = useState<number | null>(null);

  return (
    <>
      <section className="general-header">
        <h1>Frequently Asked Questions</h1>
      </section>

      <section id="faq-section">
        <div className="inner">
          <div id="faq-header">
            <span>Select a question to see the answer</span>
            <div />
          </div>

          <div id="faqs">
            {siteData.faqs.map((faq, index) => {
              const open = openIndex === index;

              return (
                <div key={faq.question} className={`faq ${open ? "open" : ""}`} onClick={() => setOpenIndex(open ? null : index)}>
                  <div className="faq-top">
                    <div className="faq-toggle" />
                    <span className="faq-question">{faq.question}</span>
                  </div>
                  <p className="faq-answer" style={{ display: open ? "block" : "none" }}>
                    {faq.answer}
                  </p>
                </div>
              );
            })}
          </div>
        </div>
      </section>
    </>
  );
}
