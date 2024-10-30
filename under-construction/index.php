<?php include_once '../includes/env.php'; ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $company_name; ?></title>
    <link type="text/css" rel="stylesheet" href="/styles/main.css">

    <style>
        body {
            background: var(--black);
            color: var(--white);
        }

        #main-container {
            width: 100vw;
            height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 24px;
            text-align: center;
        }

        img {
            max-width: 200px;
            margin-bottom: 48px;
        }

        p {
            line-height: 1.5;
            max-width: 800px;
        }
    </style>
</head>

<body>
    <div id="main-container">
        <img src="/assets/images/misc/hardhat.avif" alt="Hardhat">
        <h1>Under Construction</h1>
        <p>The site is getting an upgrade! We should be finished soon, but in case you have any inquiries, please feel free to us a call at <a href="tel:+1 (268) 773-2900">+1 (268) 773-2900</a> or send us an email at <a href="mailto:info@<?php echo $domain; ?>">info@<?php echo $domain; ?></a></p>
    </div>
</body>

</html>