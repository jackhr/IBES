<?php

include_once 'connection.php';

$description = isset($description) ? $description : "$company_name offers affordable, well-maintained vehicles. Enjoy online booking and exceptional customer service. Rent a car in Antigua today!";

$base_title = $company_name;

$title = isset($title_override) ? $title_override : (isset($title_suffix) ? $base_title .= " | " . $title_suffix : $title);

$page_lookup = [
    "about" => "../",
    "reservation" => "../",
    "taxi" => "../",
    "confirmation" => "../",
    "contact" => "../",
    "faq" => "../",
    "index" => ""
];
$swal_load_lookup = [
    "index" => 1,
    "reservation" => 1,
    "taxi" => 1,
    "contact" => 1,
];
$flatpick_load_lookup = [
    "index" => 1,
    "reservation" => 1,
    "taxi" => 1,
];

$style_prefix = $page_lookup[$page] ?? "";
$canonical_dir = $page === "index" ? "" : $page . "/";
$canonical_url = "https://$www_domain/{$canonical_dir}";
$load_swal = isset($swal_load_lookup[$page]);
$load_flatpick = isset($flatpick_load_lookup[$page]);

?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1">

    <!-- SEO BEGIN -->
    <meta name="keywords" content="antigua car rental, affordable car rentals, car hire, vehicle rental, antigua rent a car, rent a car, car rental near me, airport car rental, luxury car hire, cheap car rental, car booking, st. john's, online car rental, city car rental, car rental services, weekend car rental, business car hire, caribbean rentals, antigua, antigua and barbuda, antigua rentals">
    <meta name="description" content="<?php echo $description ?>">
    <meta property="og:title" content="<?php echo $base_title ?>">
    <meta property="og:description" content="<?php echo $description; ?>">
    <meta property="og:type" content="Website">
    <meta property="og:image" content="https://<?php echo $www_domain; ?>/assets/images/logo.avif">
    <meta property="og:url" content="<?php echo $canonical_url; ?>">
    <link rel="canonical" href="<?php echo $canonical_url; ?>" />
    <!-- SEO END -->

    <!-- favicon begin -->
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/images/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/assets/images/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/assets/images/favicon/favicon-16x16.png">
    <link rel="manifest" href="/assets/images/favicon/site.webmanifest">
    <!-- favicon end -->
    <title><?php echo $title ?></title>
    <link type="text/css" rel="stylesheet" href="/styles/min/main.min.css">
    <?php if (isset($page) && file_exists("{$style_prefix}styles/min/{$page}.min.css")) { ?>
        <link type="text/css" rel="stylesheet" href="/styles/min/<?php echo $page ?>.min.css">
    <?php }
    if (isset($extra_css)) { ?>
        <link type="text/css" rel="stylesheet" href="/styles/min/<?php echo $extra_css ?>.min.css">
    <?php } ?>

    <!-- BEGIN FONTS -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <!-- END PLUGINS -->

    <!-- BEGIN PLUGINS -->
    <script src="/plugins/jquery/jquery-3.7.1.min.js"></script>
    <?php if ($load_swal) { ?>
        <link type="text/css" rel="stylesheet" href="/plugins/sweetalert2/styles/sweetalert2.min.css">
        <script src="/plugins/sweetalert2/js/sweetalert2.all.min.js" defer></script>
    <?php } ?>
    <?php if ($load_flatpick) { ?>
        <link type="text/css" rel="stylesheet" href="/plugins/flatpickr/styles/flatpickr.min.css">
        <link type="text/css" rel="stylesheet" href="/plugins/flatpickr/styles/theme.min.css">
        <script src="/plugins/flatpickr/js/flatpickr.v4.6.13.min.js" defer></script>
    <?php } ?>
    <!-- END PLUGINS -->

    <!-- BEGIN STRUCTURED DATA -->
    <?php
    if (isset($structured_data) && is_array($structured_data)) {
        foreach ($structured_data as $data) {
            echo '<script type="application/ld+json">';
            echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            echo '</script>';
        }
    }
    ?>
    <!-- END STRUCTURED DATA -->

    <script src="/js/main.min.js" defer></script>
</head>

<body id="<?php echo $page ?>-page">

    <div class="overlay"></div>

    <header>
        <div class="inner">

            <a href="/">
                <img src="/assets/images/logo.jpeg" alt="Website logo">
            </a>

            <nav>
                <a href="/">Home</a>
                <a href="/reservation/">Book Now</a>
                <a href="/taxi/">Taxi</a>
                <a href="/about/">About</a>
                <a href="/faq/">FAQ</a>
                <a href="/contact/">Contact</a>
            </nav>

            <div id="hamburger-button">
                <div id="hamburger-icon">
                    <div class="hamburger-line"></div>
                    <div class="hamburger-line"></div>
                    <div class="hamburger-line"></div>
                </div>
            </div>

            <div id="hamburger-nav">
                <svg id="close-hamburger" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x">
                    <path d="M18 6 6 18" />
                    <path d="m6 6 12 12" />
                </svg>
                <nav>
                    <a href="/">Home</a>
                    <a href="/reservation/">Book Now</a>
                    <a href="/taxi/">Taxi</a>
                    <a href="/about/">About</a>
                    <a href="/faq/">FAQ</a>
                    <a href="/contact/">Contact</a>
                </nav>
            </div>

        </div>
    </header>