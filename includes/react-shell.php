<?php

declare(strict_types=1);

include_once __DIR__ . '/app-config.php';

$page = isset($page) ? (string) $page : 'index';
$description = isset($description) ? (string) $description : "$company_name offers affordable, well-maintained vehicles. Enjoy online booking and exceptional customer service. Rent a car in Antigua today!";

$base_title = $company_name;
$title = isset($title_override)
    ? (string) $title_override
    : (isset($title_suffix) ? "$base_title | $title_suffix" : $base_title);

$canonical_dir = $page === 'index' ? '' : trim($page, '/') . '/';
$canonical_url = isset($canonical_url) ? (string) $canonical_url : "https://$www_domain/{$canonical_dir}";
$og_image = isset($og_image) ? (string) $og_image : "https://$www_domain/assets/images/logo.avif";

$structured_data = isset($structured_data) && is_array($structured_data) ? $structured_data : [];
$react_route = isset($react_route) ? (string) $react_route : ($page === 'index' ? '/' : '/' . trim($page, '/'));

$app_env = strtolower((string) ($app_env ?? Config::get('APP_ENV', 'production')));
$is_production = $app_env === 'production';
$use_vite_dev_server = !$is_production && Config::bool('VITE_USE_DEV_SERVER', false);
$vite_dev_server = rtrim((string) Config::get('VITE_DEV_SERVER', 'http://localhost:5173'), '/');

$manifest_path = dirname(__DIR__) . '/dist/.vite/manifest.json';
$manifest_entry = null;

if (!$use_vite_dev_server && is_file($manifest_path)) {
    $manifest_contents = file_get_contents($manifest_path);
    $manifest = is_string($manifest_contents) ? json_decode($manifest_contents, true) : null;

    if (is_array($manifest)) {
        foreach (['src/main.tsx', 'index.html'] as $candidate_key) {
            if (isset($manifest[$candidate_key]) && is_array($manifest[$candidate_key])) {
                $manifest_entry = $manifest[$candidate_key];
                break;
            }
        }
    }
}

$entry_file = is_array($manifest_entry) && isset($manifest_entry['file']) ? $manifest_entry['file'] : null;
$entry_css_files = is_array($manifest_entry) && isset($manifest_entry['css']) && is_array($manifest_entry['css'])
    ? $manifest_entry['css']
    : [];

$html_attrs = fn(string $value): string => htmlspecialchars($value, ENT_QUOTES, 'UTF-8');

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <script async src="https://www.googletagmanager.com/gtag/js?id=AW-11524221249"></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }

        gtag('js', new Date());
        gtag('config', 'AW-11524221249');
    </script>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">

    <meta name="keywords" content="antigua car rental, affordable car rentals, car hire, vehicle rental, antigua rent a car, rent a car, car rental near me, airport car rental, luxury car hire, cheap car rental, car booking, st. john's, online car rental, city car rental, car rental services, weekend car rental, business car hire, caribbean rentals, antigua, antigua and barbuda, antigua rentals">
    <meta name="description" content="<?php echo $html_attrs($description); ?>">
    <meta property="og:title" content="<?php echo $html_attrs($base_title); ?>">
    <meta property="og:description" content="<?php echo $html_attrs($description); ?>">
    <meta property="og:type" content="website">
    <meta property="og:image" content="<?php echo $html_attrs($og_image); ?>">
    <meta property="og:url" content="<?php echo $html_attrs($canonical_url); ?>">
    <link rel="canonical" href="<?php echo $html_attrs($canonical_url); ?>">

    <link rel="apple-touch-icon" sizes="180x180" href="/assets/images/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/assets/images/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/assets/images/favicon/favicon-16x16.png">
    <link rel="manifest" href="/assets/images/favicon/site.webmanifest">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">

    <title><?php echo $html_attrs($title); ?></title>

    <?php foreach ($entry_css_files as $css_file) { ?>
        <link rel="stylesheet" href="/dist/<?php echo $html_attrs((string) $css_file); ?>">
    <?php } ?>

    <?php
    foreach ($structured_data as $data) {
        if (!is_array($data)) {
            continue;
        }
        echo '<script type="application/ld+json">';
        echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        echo '</script>';
    }
    ?>
</head>

<body id="<?php echo $html_attrs($page . '-page'); ?>">
    <noscript>This website requires JavaScript to render correctly.</noscript>
    <div id="root" data-route="<?php echo $html_attrs($react_route); ?>"></div>

    <?php if ($use_vite_dev_server) { ?>
        <script type="module" src="<?php echo $html_attrs($vite_dev_server); ?>/@vite/client"></script>
        <script type="module" src="<?php echo $html_attrs($vite_dev_server); ?>/src/main.tsx"></script>
    <?php } elseif (is_string($entry_file)) { ?>
        <script type="module" src="/dist/<?php echo $html_attrs($entry_file); ?>"></script>
    <?php } else { ?>
        <script>
            console.error("React build manifest not found. Run `npm run build` to generate /dist assets.");
        </script>
    <?php } ?>
</body>

</html>
