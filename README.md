# IBES Frontend Migration

This project now uses:

- `PHP` for backend pages and form endpoints (`/includes/*.php`)
- `React + Vite + TypeScript` for frontend rendering

## Current architecture

- Public routes (`/`, `/about/`, `/faq/`, `/contact/`, `/taxi/`, `/reservation/`) are thin PHP wrappers that load `includes/react-shell.php`.
- `includes/react-shell.php` injects SEO meta tags and loads either:
  - built Vite assets from `dist/.vite/manifest.json`, or
  - Vite dev server (when `VITE_USE_DEV_SERVER=true`, or when not production and no manifest exists).
- Legacy PHP backend endpoints remain active:
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

## Build for PHP runtime

```bash
npm run build
```

This generates `dist/` and `dist/.vite/manifest.json`, which the PHP shell uses to load hashed assets.

## Useful environment flags

Add these to `.env` when needed:

```bash
# Force PHP pages to load from Vite dev server
VITE_USE_DEV_SERVER=true

# Dev server URL loaded by PHP shell
VITE_DEV_SERVER=http://localhost:5173
```
