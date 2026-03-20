import "./Features.scss";

export type Feature = {
  title: string;
  copy: string;
  icon: string;
};

type FeaturesProps = {
  features: Feature[];
};

export default function Features({ features }: FeaturesProps) {
  return (
    <div id="features">
      {features.map((feature) => (
        <div key={feature.title} className="feature-container">
          <div className="feature-icon">
            <i className={feature.icon} aria-hidden />
          </div>
          <div className="feature-info">
            <h2>{feature.title}</h2>
            <p>{feature.copy}</p>
          </div>
        </div>
      ))}
    </div>
  );
}
