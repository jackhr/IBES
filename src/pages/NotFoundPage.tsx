import { Link } from "react-router-dom";

export default function NotFoundPage() {
  return (
    <section className="page section">
      <div className="container narrow">
        <h1>Page Not Found</h1>
        <p>The page you requested does not exist.</p>
        <Link to="/" className="btn btn-primary">
          Go Home
        </Link>
      </div>
    </section>
  );
}
