# IBES Frontend Migration

This project now uses:

- `React + Vite + TypeScript` for the full frontend app (single SPA entrypoint)
- `PHP` for backend endpoints (`/includes/*.php`)

## Current architecture

- Apache rewrites all non-file requests to `dist/index.html` (React Router handles routes).
- Existing PHP backend endpoints remain active:
  - `/includes/contact-send.php`
  - `/includes/taxi-request-send.php`
  - `/includes/vehicle-request-send.php`
  - `/includes/reservation.php`

## Local development

Install dependencies:

```bash
npm install
```

Run Vite frontend:

```bash
npm run dev
```

Optional proxy for PHP endpoints during Vite development:

```bash
VITE_BACKEND_PROXY_TARGET=http://localhost:8000 npm run dev
```

## Build for production

```bash
npm run build
```

This generates `dist/index.html` and `dist/assets/*`, which are served by `.htaccess` SPA fallback rules.
