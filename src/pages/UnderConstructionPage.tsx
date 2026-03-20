import { siteData } from "../data/siteData";

export default function UnderConstructionPage() {
  return (
    <section style={{ minHeight: "100vh", display: "grid", placeItems: "center", padding: "24px" }}>
      <div
        style={{
          background: "#fff",
          borderRadius: "8px",
          maxWidth: "760px",
          width: "100%",
          padding: "32px",
          textAlign: "center"
        }}
      >
        <img src="/assets/images/misc/hardhat.avif" alt="Hardhat icon" style={{ maxWidth: "180px", margin: "0 auto 24px" }} />
        <h1 style={{ marginTop: 0 }}>Under Construction</h1>
        <p style={{ lineHeight: 1.6 }}>
          The site is getting an upgrade. For immediate assistance, call{" "}
          <a href={`tel:${siteData.phone.replace(/[^\d+]/g, "")}`}>{siteData.phone}</a> or email{" "}
          <a href={`mailto:${siteData.email}`}>{siteData.email}</a>.
        </p>
      </div>
    </section>
  );
}
