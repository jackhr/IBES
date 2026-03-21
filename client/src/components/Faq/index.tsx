import { useState } from "react";
import type { FaqItem } from "../../data/siteData";
import "./Faq.scss";

type FaqProps = {
  faq: FaqItem;
};

export default function Faq({ faq }: FaqProps) {
  const [open, setOpen] = useState(false);

  function toggleFaq() {
    setOpen((current) => !current);
  }

  return (
    <div key={faq.question} className={`faq ${open ? "open" : ""}`} onClick={toggleFaq}>
      <button type="button" className="faq-top">
        <div className="faq-toggle" />
        <span className="faq-question">{faq.question}</span>
      </button>
      <div className={`faq-answer-wrap ${open ? "open" : ""}`}>
        <p className="faq-answer">{faq.answer}</p>
      </div>
    </div>
  );
}
