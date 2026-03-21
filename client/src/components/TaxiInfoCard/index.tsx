import "./TaxiInfoCard.scss";

type TaxiInfoCardProps = {
  title: string;
  details: string[];
};

export default function TaxiInfoCard({ title, details }: TaxiInfoCardProps) {
  return (
    <div className="taxi-info-card">
      <h3>{title}</h3>
      <div>
        {details.map((detail, index) => (
          <span key={`${title}-${index}`}>{detail}</span>
        ))}
      </div>
    </div>
  );
}
