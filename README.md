# IBES Frontend Migration

This project now uses:

- `React + Vite + TypeScript` for the full frontend app (single SPA entrypoint)
- `PHP` for backend endpoints (`/api/*`)
- npm plugin packages in the React app:
  - `flatpickr`
  - `sweetalert2`
  - `@fortawesome/fontawesome-free`

## Current architecture

- Apache rewrites all non-file requests to `dist/index.html` (React Router handles routes).
- PHP backend endpoints are routed through a single API front controller (`server/api.php`).
- Backend is organized as MVC-style layers under `server/App`:
  - `Controllers`: endpoint entry handlers
  - `Models`: typed domain models (`AddOn`, `ContactInfo`, `OrderRequest`, `TaxiRequest`, `Vehicle`, `VehicleDiscount`)
  - `Services`: business logic
  - `Repositories`: database access
  - `Support/Core`: shared infrastructure (settings, request/session/response, mail helpers)

## API endpoints

Model-oriented endpoints:

- `GET /api/add-ons`
- `GET /api/add-ons/{id}`
- `POST /api/contact-info`
- `GET /api/contact-info/{id}`
- `POST /api/order-requests`
- `GET /api/order-requests/{key}`
- `POST /api/taxi-requests`
- `GET /api/taxi-requests/{id}`
- `GET /api/vehicles`
- `GET /api/vehicles/landing`
- `GET /api/vehicles/{id}`
- `GET /api/vehicle-discounts?vehicleId={id}&days={days}`

Legacy compatibility endpoints (still active):

- `POST /api/contact`
- `POST /api/taxi-request`
- `POST /api/vehicle-request`
- `POST /api/reservation`

## Local development

Install dependencies:

```bash
npm install
```

Run Vite frontend:

```bash
npm run dev
```

Optional proxy for API endpoints during Vite development:

```bash
VITE_BACKEND_PROXY_TARGET=http://localhost:8000 npm run dev
```

## Build for production

```bash
npm run build
```

This generates `dist/index.html` and `dist/assets/*`, which are served by `.htaccess` SPA fallback rules.

## cPanel deployment note

If legacy folders like `about/`, `faq/`, `contact/`, `reservation/`, `taxi/`, or `confirmation/` still exist on the server, direct URL visits can bypass the React SPA.

- Upload the latest root `.htaccess`.
- Remove any leftover legacy page directories/files from prior PHP versions.
- Ensure `dist/index.html` and `dist/assets/*` are both uploaded together from the same build.

## Production hardening

Public POST endpoints now include:

- IP-based rate limiting
- Honeypot checks
- Stricter backend payload validation
- Optional captcha verification (`hCaptcha` or `reCAPTCHA`)

Set these in `.env`:

```bash
# Global/default rate limiting
RATE_LIMIT_MAX=15
RATE_LIMIT_WINDOW=900

# Per-endpoint overrides
CONTACT_RATE_LIMIT_MAX=6
CONTACT_RATE_LIMIT_WINDOW=900
TAXI_RATE_LIMIT_MAX=6
TAXI_RATE_LIMIT_WINDOW=900
RESERVATION_RATE_LIMIT_MAX=8
RESERVATION_RATE_LIMIT_WINDOW=900
RESERVATION_API_RATE_LIMIT_MAX=60
RESERVATION_API_RATE_LIMIT_WINDOW=300

# Captcha backend verification
CAPTCHA_ENABLED=true
CAPTCHA_PROVIDER=hcaptcha # or recaptcha
HCAPTCHA_SECRET_KEY=your-secret
RECAPTCHA_SECRET_KEY=your-secret

# Captcha frontend widget
VITE_CAPTCHA_PROVIDER=hcaptcha # or recaptcha
VITE_HCAPTCHA_SITE_KEY=your-site-key
VITE_RECAPTCHA_SITE_KEY=your-site-key
```

If `CAPTCHA_ENABLED=false` or `CAPTCHA_PROVIDER=none`, captcha verification is bypassed.

## SEO for SPA routes

Route-level SEO metadata is managed in React and updated on client-side navigation:

- `title`
- `meta description`
- `canonical`
- Open Graph + Twitter tags
- route-level `robots` directives (`noindex` for not-found/under-construction views)

Route metadata lives in `src/data/routeSeo.ts`.

Set canonical site origin in `.env`:

```bash
VITE_SITE_URL=https://www.ibescarrental.com
```

## Under construction mode

Set this in `.env`:

```bash
UNDER_CONSTRUCTION=true
```

The React app checks this flag in `src/App.tsx` and routes every path to the React under-construction page when enabled.
Set it back to `false` to restore normal routing.
