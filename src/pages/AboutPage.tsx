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
              src="/assets/images/misc/Freepik-whiteSUV-Palms-SandyBeach-Couple-09.jpg"
              alt="Couple standing by a parked car on the beach"
            />
            <div>
              <h2>WELCOME TO IBES CAR RENTAL</h2>
              <p>
                Ibes Car Rental has been serving Antigua&apos;s transport industry for years and we remain focused on
                reliable service as tourism and the local economy continue to grow.
              </p>
              <p>
                Whether you are visiting for the first time or returning home for family events, we provide practical
                transport options tailored to your plans.
              </p>
              <em>— Irwin Philo, Owner of Ibes Car Rental</em>
            </div>
          </div>

          <div className="about-panel">
            <img src="/assets/images/misc/Harbour-NelsonsDockyard-Air-JMR18809.jpg" alt="Aerial view of English Harbour" />
            <div>
              <h2>WELCOME TO ANTIGUA</h2>
              <p>
                Antigua offers beaches, local heritage, and scenic drives in every direction. If you need suggestions,
                our team can point you to destinations that match your schedule.
              </p>
              <ul>
                <li>365 beaches to explore</li>
                <li>Nelson&apos;s Dockyard National Park</li>
                <li>Fig Tree Drive and rainforest routes</li>
                <li>Nightlife and entertainment</li>
                <li>So much more</li>
              </ul>
            </div>
          </div>
        </div>
      </section>
    </>
  );
}
