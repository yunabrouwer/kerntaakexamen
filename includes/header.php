<?php
declare(strict_types=1);

$pageTitle = $pageTitle ?? 'TableTalk';
$establishmentName = $establishment['naam'] ?? $establishment['vestiging_naam'] ?? '';
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= escapeHtml($pageTitle) ?> | TableTalk</title>
    <link rel="icon" type="image/svg+xml" href="assets/favicon.svg">
    <style>
        :root {
            color-scheme: light;
            font-family: Arial, Helvetica, sans-serif;
            color: #202a35;
            background: #f3f6f8;
        }
        * { box-sizing: border-box; }
        body { margin: 0; min-width: 320px; }
        a { color: #165d56; }
        .site-header {
            padding: 20px 16px 16px;
            background: #fff;
            text-align: center;
            border-bottom: 1px solid #e1e7eb;
        }
        .brand {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            color: #173a37;
            font-size: 1.35rem;
            font-weight: 700;
            text-decoration: none;
        }
        .brand-mark {
            display: grid;
            width: 34px;
            height: 34px;
            place-items: center;
            border-radius: 50%;
            background: #2e7d32;
            color: white;
            font-size: 1rem;
        }
        .establishment-name { margin: 8px 0 0; color: #5a6872; }
        main { width: min(100% - 32px, 1080px); margin: 28px auto 48px; }
        .card {
            padding: 24px;
            background: #fff;
            border: 1px solid #e1e7eb;
            border-radius: 12px;
            box-shadow: 0 5px 18px rgb(21 42 54 / 5%);
        }
        h1 { margin-top: 0; font-size: clamp(1.5rem, 4vw, 2rem); }
        .muted { color: #61717b; }
        .button, button {
            display: inline-block;
            padding: 11px 16px;
            border: 0;
            border-radius: 7px;
            background: #236b42;
            color: #fff;
            font: inherit;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
        }
        .button:hover, button:hover { background: #185533; }
        input, textarea, select {
            width: 100%;
            padding: 11px 12px;
            border: 1px solid #c8d2d9;
            border-radius: 7px;
            background: #fff;
            color: inherit;
            font: inherit;
        }
        input:focus, textarea:focus, select:focus, button:focus-visible, a:focus-visible {
            outline: 3px solid #9ad0a1;
            outline-offset: 2px;
        }
        label { font-weight: 600; }
        .field { margin: 18px 0; }
        .field > label { display: block; margin-bottom: 7px; }
        fieldset.field { padding: 0; border: 0; }
        fieldset.field legend { margin-bottom: 7px; font-weight: 600; }
        .error { color: #a42121; }
        .success { color: #236b42; }
        .rating { display: flex; flex-direction: row-reverse; justify-content: flex-end; gap: 6px; }
        .rating input { position: absolute; width: 1px; height: 1px; opacity: 0; }
        .rating label { color: #9aa6ad; font-size: 2rem; cursor: pointer; }
        .rating input:checked ~ label, .rating label:hover, .rating label:hover ~ label { color: #236b42; }
        .rating input:focus-visible + label { outline: 3px solid #9ad0a1; outline-offset: 2px; }
        button:disabled { cursor: wait; opacity: .7; }
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px 10px; border-bottom: 1px solid #e7ecef; text-align: left; }
        th { color: #53636d; font-size: .9rem; }
        .site-footer { padding: 18px; color: #61717b; text-align: center; font-size: .9rem; }
        @media (max-width: 600px) {
            main { width: min(100% - 20px, 1080px); margin-top: 16px; }
            .card { padding: 18px; }
        }
    </style>
</head>
<body>
<header class="site-header">
    <a class="brand" href="index.php" aria-label="TableTalk startpagina">
        <span class="brand-mark" aria-hidden="true">T</span>
        <span>TableTalk</span>
    </a>
    <?php if ($establishmentName !== ''): ?>
        <p class="establishment-name"><?= escapeHtml($establishmentName) ?></p>
    <?php endif; ?>
</header>
