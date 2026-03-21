import { FormEvent, useState } from "react";

type LoginPageProps = {
  onSubmit: (username: string, password: string) => Promise<void>;
  loading: boolean;
};

export default function LoginPage({ onSubmit, loading }: LoginPageProps) {
  const [username, setUsername] = useState("");
  const [password, setPassword] = useState("");
  const [error, setError] = useState<string | null>(null);

  const handleSubmit = async (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    setError(null);

    try {
      await onSubmit(username, password);
    } catch (submitError) {
      if (submitError instanceof Error) {
        setError(submitError.message);
      } else {
        setError("Unable to sign in.");
      }
    }
  };

  return (
    <div className="auth-shell">
      <form className="auth-card" onSubmit={handleSubmit}>
        <h1>IBES Admin</h1>
        <p>Sign in to manage vehicles, rates, requests, and bookings.</p>

        <label>
          Username
          <input
            type="text"
            value={username}
            onChange={(event) => setUsername(event.target.value)}
            autoComplete="username"
            required
          />
        </label>

        <label>
          Password
          <input
            type="password"
            value={password}
            onChange={(event) => setPassword(event.target.value)}
            autoComplete="current-password"
            required
          />
        </label>

        {error ? <div className="form-error">{error}</div> : null}

        <button type="submit" disabled={loading}>
          {loading ? "Signing In..." : "Sign In"}
        </button>
      </form>
    </div>
  );
}
