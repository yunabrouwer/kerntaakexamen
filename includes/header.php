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
    <style>
        :root {
            color-scheme: light;
            font-family: Inter, "Segoe UI", Arial, sans-serif;
            color: #26352d;
            background: #f5f7f3;
            font-synthesis: none;
            text-rendering: optimizeLegibility;
            --green: #2e7d32;
            --green-dark: #1d5129;
            --green-soft: #eaf3e9;
            --muted: #69766e;
            --line: #e3e9e1;
            --surface: #fff;
        }
        * { box-sizing: border-box; }
        body { display: flex; min-width: 320px; min-height: 100vh; flex-direction: column; margin: 0; }
        a { color: var(--green-dark); text-underline-offset: 3px; }
        a:hover { color: var(--green); }
        .site-header { padding: 22px 16px 18px; background: var(--surface); border-bottom: 1px solid var(--line); }
        .site-header-inner { position: relative; display: flex; max-width: 1080px; min-height: 46px; align-items: center; justify-content: center; margin: 0 auto; }
        .brand { display: inline-flex; align-items: center; gap: 11px; color: var(--green-dark); text-align: left; text-decoration: none; }
        .brand-mark { display: grid; width: 42px; height: 42px; place-items: center; border-radius: 14px; background: var(--green); color: #fff; font-family: Georgia, serif; font-size: 1.45rem; font-weight: 700; }
        .brand-name { display: block; font-size: 1.05rem; font-weight: 800; letter-spacing: .04em; }
        .brand-caption { display: block; margin-top: 2px; color: var(--muted); font-size: .72rem; letter-spacing: .08em; }
        .establishment-name { margin: 10px 0 0; color: var(--muted); font-size: .95rem; text-align: center; }
        .header-logout { position: absolute; top: 50%; right: 0; transform: translateY(-50%); }
        .header-logout button { min-height: 40px; padding: 8px 14px; }
        main { width: min(100% - 36px, 1080px); flex: 1; margin: 36px auto 56px; }
        .card { padding: clamp(22px, 4vw, 36px); background: var(--surface); border: 1px solid var(--line); border-radius: 18px; box-shadow: 0 12px 34px rgb(32 57 38 / 6%); }
        h1, h2, h3 { color: var(--green-dark); line-height: 1.2; }
        h1 { margin: 0 0 18px; font-size: clamp(1.65rem, 4vw, 2.2rem); letter-spacing: -.035em; }
        h2 { margin-top: 0; font-size: 1.25rem; }
        p { line-height: 1.65; }
        .muted { color: var(--muted); }
        .button, button { display: inline-flex; min-height: 46px; align-items: center; justify-content: center; padding: 11px 18px; border: 1px solid var(--green); border-radius: 10px; background: var(--green); color: #fff; font: inherit; font-weight: 700; text-align: center; text-decoration: none; cursor: pointer; transition: background-color .15s ease, border-color .15s ease, transform .15s ease; }
        .button:hover, button:hover { transform: translateY(-1px); border-color: var(--green-dark); background: var(--green-dark); color: #fff; }
        .button-secondary { border-color: var(--line); background: #fff; color: var(--green-dark); }
        .button-secondary:hover { border-color: var(--green); background: var(--green-soft); color: var(--green-dark); }
        input, textarea, select { width: 100%; min-height: 48px; padding: 12px 14px; border: 1px solid #ccd7cd; border-radius: 10px; background: #fff; color: #26352d; font: inherit; }
        textarea { min-height: 140px; resize: vertical; }
        input:focus, textarea:focus, select:focus, button:focus-visible, a:focus-visible { outline: 3px solid #b6d8b8; outline-offset: 3px; }
        label { font-weight: 650; }
        .field { margin: 22px 0; }
        .field > label { display: block; margin-bottom: 8px; }
        fieldset.field { padding: 0; border: 0; }
        fieldset.field legend { margin-bottom: 10px; font-weight: 650; }
        .error { color: #a12f2a; }
        .success { color: var(--green-dark); }
        .alert { padding: 12px 15px; border-radius: 10px; background: #fff1ef; }
        .rating { display: flex; flex-direction: row-reverse; justify-content: flex-end; gap: 8px; width: fit-content; }
        .rating input { position: absolute; width: 1px; height: 1px; overflow: hidden; opacity: 0; }
        .rating label { color: #c4cfc3; font-size: clamp(2.5rem, 12vw, 3.4rem); line-height: 1; cursor: pointer; transition: color .15s ease, transform .15s ease; }
        .rating input:checked ~ label, .rating label:hover, .rating label:hover ~ label { color: var(--green); }
        .rating:has(input[value="3"]:checked) input:checked ~ label { color: #d28a18; }
        .rating:has(input[value="1"]:checked) input:checked ~ label, .rating:has(input[value="2"]:checked) input:checked ~ label { color: #bd443b; }
        .rating label:hover { transform: scale(1.08); }
        .rating input:focus-visible + label { outline: 3px solid #b6d8b8; outline-offset: 4px; border-radius: 4px; }
        button:disabled { cursor: wait; opacity: .7; transform: none; }
        .survey-card { max-width: 650px; margin: 0 auto; }
        .survey-intro { margin: -4px 0 28px; color: var(--muted); }
        .login-card { max-width: 460px; margin: 0 auto; }
        .login-card button, .login-card .button { width: 100%; }
        .dashboard-summary { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 22px; align-items: center; }
        .dashboard-heading { margin-bottom: 8px; }
        .score-card { min-width: 150px; padding: 18px 22px; border: 1px solid #d8e8d7; border-radius: 14px; background: var(--green-soft); text-align: center; }
        .score-card strong { display: block; color: var(--green-dark); font-size: 2rem; line-height: 1.1; }
        .score-card span { color: var(--muted); font-size: .85rem; }
        .score-stack { display: grid; gap: 12px; }
        .comparison-link { display: flex; font-size: .85rem; }
        .dashboard-actions { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 24px; }
        .filter-nav { display: flex; flex-wrap: wrap; gap: 8px; margin: 0 0 22px; }
        .filter-nav a { padding: 9px 13px; border: 1px solid var(--line); border-radius: 999px; color: var(--green-dark); text-decoration: none; }
        .filter-nav a[aria-current="page"] { border-color: var(--green); background: var(--green-soft); font-weight: 700; }
        .dashboard-list { margin-top: 20px; }
        .table-wrap { overflow-x: auto; border: 1px solid var(--line); border-radius: 12px; }
        table { width: 100%; min-width: 680px; border-collapse: collapse; }
        th, td { padding: 14px 15px; border-bottom: 1px solid var(--line); text-align: left; vertical-align: top; }
        th { background: #f7f9f6; color: var(--muted); font-size: .78rem; letter-spacing: .06em; text-transform: uppercase; }
        tbody tr:last-child td { border-bottom: 0; }
        tbody tr:hover { background: #fbfcfa; }
        .detail-card { max-width: 980px; margin: 0 auto; }
        .detail-layout { display: grid; grid-template-columns: minmax(0, 7fr) minmax(220px, 3fr); gap: 32px; }
        .feedback-copy { white-space: pre-line; overflow-wrap: anywhere; }
        .metadata { margin: 0; }
        .metadata dt { margin-top: 16px; color: var(--muted); font-size: .84rem; }
        .metadata dd { margin: 4px 0 0; font-weight: 650; }
        .detail-actions { margin-top: 28px; padding-top: 22px; border-top: 1px solid var(--line); }
        .detail-actions form { display: flex; flex-wrap: wrap; gap: 10px; }
        .comparison-card { max-width: 1080px; margin: 0 auto; }
        .comparison-table { min-width: 780px; }
        .comparison-bar { width: 120px; height: 8px; margin-top: 8px; overflow: hidden; border-radius: 999px; background: var(--green-soft); }
        .comparison-bar span { display: block; height: 100%; border-radius: inherit; background: var(--green); }
        .score-distribution { display: flex; flex-wrap: wrap; gap: 8px; }
        .score-distribution span { white-space: nowrap; }
        .qr-card, .thank-you-card { max-width: 620px; margin: 0 auto; text-align: center; }
        .qr-image { display: block; width: min(100%, 320px); height: auto; margin: 24px auto; image-rendering: pixelated; }
        .survey-url { overflow-wrap: anywhere; }
        .thank-you-mark { display: grid; width: 68px; height: 68px; margin: 0 auto 20px; place-items: center; border-radius: 50%; background: var(--green-soft); color: var(--green); font-size: 2rem; font-weight: 700; }
        .site-footer { padding: 22px 16px; color: var(--muted); text-align: center; font-size: .88rem; }
        .site-footer p { margin: 0; }
        @media (max-width: 640px) {
            .site-header { padding: 14px 12px; }
            .site-header-inner { justify-content: space-between; gap: 10px; }
            .header-logout { position: static; flex: 0 0 auto; transform: none; }
            .header-logout button { min-height: 40px; padding: 8px 11px; font-size: .9rem; }
            main { width: min(100% - 24px, 1080px); margin: 22px auto 36px; }
            .card { border-radius: 15px; }
            .dashboard-summary { grid-template-columns: 1fr; }
            .score-card { justify-self: start; }
            .comparison-link { width: 100%; }
            .detail-layout { grid-template-columns: 1fr; gap: 20px; }
            .dashboard-actions > * { flex: 1 1 100%; }
            .detail-actions form > * { flex: 1 1 100%; }
        }
        @media print {
            .site-header, .site-footer, .no-print { display: none !important; }
            body { background: #fff; }
            main { width: 100%; margin: 0; }
            .card { border: 0; box-shadow: none; }
        }
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { scroll-behavior: auto !important; transition-duration: .01ms !important; }
        }
    </style>
</head>
<body>
<header class="site-header">
    <div class="site-header-inner">
        <a class="brand" href="index.php" aria-label="TableTalk startpagina">
            <span class="brand-mark" aria-hidden="true">T</span>
            <span>
                <span class="brand-name">TableTalk</span>
                <span class="brand-caption">FEEDBACK</span>
            </span>
        </a>
        <?php if (!empty($showManagerLogout)): ?>
            <form class="header-logout" action="logout.php" method="post">
                <input type="hidden" name="csrf_token" value="<?= escapeHtml(csrfToken()) ?>">
                <button class="button-secondary" type="submit">Uitloggen</button>
            </form>
        <?php endif; ?>
    </div>
    <?php if ($establishmentName !== ''): ?>
        <p class="establishment-name"><?= escapeHtml($establishmentName) ?></p>
    <?php endif; ?>
</header>
