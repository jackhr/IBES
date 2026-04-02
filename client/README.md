# IBES Client

This client now uses a normal Laravel application structure.

## Stack

- `Laravel` for routing, config, sessions, and backend endpoints
- `React + Vite + TypeScript` for the SPA frontend
- `public/` as the web document root

## Important paths

- Laravel entrypoint: `public/index.php`
- SPA shell: `resources/views/app.blade.php`
- API routes: `routes/api.php`
- SPA/page routes: `routes/web.php`
- Frontend source: `src/`
- Built frontend assets: `public/build/`
- Static site assets: `public/assets/`
- Shared vehicle images: `public/gallery -> ../../gallery`

## Local development

Install dependencies:

```bash
composer install
npm install
```

Run the app:

```bash
composer run dev
```

Or run them separately:

```bash
php artisan serve
npm run dev
```

## Production build

```bash
npm run build
```

This writes the Vite build to `public/build/`.

## Production deployment

The production document root should be:

```text
client/public
```

After uploading code, run:

```bash
./deploy-production.sh
```

That script:

- installs Composer dependencies
- installs npm dependencies
- runs `npm run build`
- clears Laravel caches
- runs migrations when needed
- caches config, routes, and views

## Notes

- `public/assets` is a real directory and should be deployed normally.
- `public/gallery` should remain a symlink to the shared gallery.
- The old custom backend under `server/` and the old `dist/` deployment flow are no longer used.
