import { Link } from "react-router-dom";

export default function ConfirmationPage() {
  return (
    <section className="page section">
      <div className="container narrow">
        <h1>Confirmation</h1>
        <p>If your booking generated a confirmation number, our team will follow up shortly.</p>
        <Link to="/" className="btn btn-primary">
          Back to Home
        </Link>
      </div>
    </section>
  );
}
