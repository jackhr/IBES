import Faq from "../../components/Faq";
import { siteData } from "../../data/siteData";
import "./FaqPage.scss";

export default function FaqPage() {
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
              return <Faq key={index} faq={faq} />;
            })}
          </div>
        </div>
      </section>
    </>
  );
}
