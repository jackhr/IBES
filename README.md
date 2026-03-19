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
- Existing PHP backend endpoints remain active through the API front controller:
  - `POST /api/contact`
  - `POST /api/taxi-request`
  - `POST /api/vehicle-request`
  - `POST /api/reservation`
- Backend is organized as MVC-style layers under `server/App`:
  - `Controllers`: endpoint entry handlers
  - `Services`: business logic
  - `Repositories`: database access
  - `Support/Core`: shared infrastructure (settings, request/session/response, mail helpers)

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
