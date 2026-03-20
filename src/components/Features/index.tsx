import {
  faCarSide,
  faCompass,
  faCreditCard,
  faHeadset,
  faMedal,
  faThumbsUp
} from "@fortawesome/free-solid-svg-icons";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import "./Features.scss";

export type Feature = {
  title: string;
  copy: string;
  icon: "medal" | "car-side" | "thumbs-up" | "compass" | "headset" | "credit-card";
};

type FeaturesProps = {
  features: Feature[];
};

const ICONS = {
  medal: faMedal,
  "car-side": faCarSide,
  "thumbs-up": faThumbsUp,
  compass: faCompass,
  headset: faHeadset,
  "credit-card": faCreditCard
} as const;

export default function Features({ features }: FeaturesProps) {
  return (
    <div id="features">
      {features.map((feature) => (
        <div key={feature.title} className="feature-container">
          <div className="feature-icon">
            <FontAwesomeIcon className="feature-icon-fa" icon={ICONS[feature.icon]} aria-hidden />
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
