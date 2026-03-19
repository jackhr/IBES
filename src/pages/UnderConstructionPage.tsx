import { siteData } from "../data/siteData";

export default function UnderConstructionPage() {
  return (
    <section className="under-construction-page">
      <div className="under-construction-card">
        <img src="/assets/images/misc/hardhat.avif" alt="Hardhat icon" />
        <p className="eyebrow">Temporary Notice</p>
        <h1>Under Construction</h1>
        <p>
          The site is getting an upgrade. For immediate assistance, call{" "}
          <a href={`tel:${siteData.phone.replace(/[^\d+]/g, "")}`}>{siteData.phone}</a> or email{" "}
          <a href={`mailto:${siteData.email}`}>{siteData.email}</a>.
        </p>
      </div>
    </section>
  );
}
