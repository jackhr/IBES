import "./AboutPage.scss";

export default function AboutPage() {
  return (
    <>
      <section className="general-header">
        <h1>About Ibes Car Rental</h1>
      </section>

      <section id="about-section">
        <div className="inner">
          <div className="about-panel">
            <img
              src="/assets/images/misc/optimized/Freepik-whiteSUV-Palms-SandyBeach-Couple-09-1800.jpg"
              alt="Couple standing by a parked car on the beach"
            />
            <div>
              <h2>WELCOME TO IBES CAR RENTAL</h2>
              <p>
                Ibes Car Rental has been servicing the transport industry in Antigua for years and we are dedicated to
                growing our company as the tourism sector and economy of Antigua & Barbuda expands.
              </p>
              <p>
                Whether you’re a new visitor to our islands or a national returning for a family gathering, we welcome
                all. Ibes Car Rental is here to provide quality transportation services customized to your specific needs.
              </p>
              <em>— Irwin Philo, Owner of Ibes Car Rental</em>
            </div>
          </div>

          <div className="about-panel">
            <img src="/assets/images/misc/optimized/Harbour-NelsonsDockyard-Air-JMR18809-1800.jpg" alt="Aerial view of English Harbour" />
            <div>
              <h2>WELCOME TO ANTIGUA</h2>
              <p>
                Antigua has so much to offer our visitors. We at Ibes Car Rental want to make your visit one of discovery
                and exploration. Please don’t hesitate to ask us for guidance – we will do our best to point you in the
                right direction.
              </p>
              <ul>
                <li>365 beaches to explore</li>
                <li>World Renowned Nelson’s Dockyard National Park</li>
                <li>Fig Tree Drive & The Rainforest</li>
                <li>Nightlife & Entertainment</li>
                <li>So much more...</li>
              </ul>
            </div>
          </div>
        </div>
      </section>
    </>
  );
}
