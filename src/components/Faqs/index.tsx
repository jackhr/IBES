import { useState } from "react";
import type { FaqItem } from "../../data/siteData";
import "./Faqs.scss";

type FaqsProps = {
  faqs: FaqItem[];
};

export default function Faqs({ faqs }: FaqsProps) {
  const [openIndex, setOpenIndex] = useState<number | null>(null);

  return (
    <div id="faqs">
      {faqs.map((faq, index) => {
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
  );
}
