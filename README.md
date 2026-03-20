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

## Under construction mode

Set this in `.env`:

```bash
UNDER_CONSTRUCTION=true
```

The React app checks this flag in `src/App.tsx` and routes every path to the React under-construction page when enabled.
Set it back to `false` to restore normal routing.
