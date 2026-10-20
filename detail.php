<?php
require_once 'includes/functions.php';

$id = isset($_GET['id']) ? $_GET['id'] : '';
$all_feedback = get_all_feedback();
$feedback_item = null;

// Zoek het juiste feedback-item op basis van het ID uit de URL
foreach ($all_feedback as $item) {
    if ($item['id'] == $id) {
        $feedback_item = $item;
        break;
    }
}

if (!$feedback_item) {
    die("<h1>Fout</h1><p>Feedback-item niet gevonden.</p><a href='dashboard.php'>Terug naar dashboard</a>");
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TableTalk - Feedback Detail</title>
    <style>
        :root {
            color-scheme: light;
            --green: #267342;
            --green-dark: #1d5933;
            --green-soft: #edf7ef;
            --ink: #20312a;
            --muted: #68766e;
            --surface: #ffffff;
            --page: #f4f7f4;
            --border: #e3ebe4;
            --shadow: 0 12px 32px rgba(31, 67, 43, 0.08);
        }

        * { box-sizing: border-box; }

        body {
            min-height: 100vh;
            margin: 0;
            padding: clamp(20px, 5vw, 64px);
            background:
                radial-gradient(ellipse at top left, rgba(214, 237, 218, 0.55), transparent 38%),
                var(--page);
            color: var(--ink);
            font-family: Arial, Helvetica, sans-serif;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }

        .page-container {
            width: min(1120px, 100%);
            margin: 0 auto;
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            margin-bottom: 24px;
            padding: 9px 14px;
            border: 1px solid var(--border);
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.78);
            color: var(--green-dark);
            font-size: 0.95rem;
            font-weight: 700;
            text-decoration: none;
            transition: background-color 180ms ease, border-color 180ms ease, transform 180ms ease;
        }

        .btn-back:hover {
            transform: translateX(-3px);
            border-color: #b8d7bf;
            background: var(--surface);
        }

        .btn-back:focus-visible {
            outline: 3px solid rgba(38, 115, 66, 0.28);
            outline-offset: 3px;
        }

        .layout-grid {
            display: grid;
            grid-template-columns: minmax(0, 7fr) minmax(250px, 3fr);
            gap: clamp(16px, 2.5vw, 28px);
            align-items: start;
        }

        .panel-left,
        .panel-right {
            min-width: 0;
            padding: clamp(22px, 3.5vw, 36px);
            border: 1px solid var(--border);
            border-radius: 20px;
            box-shadow: var(--shadow);
            animation: rise-in 500ms ease both;
        }

        .panel-left {
            min-height: 320px;
            background: var(--surface);
        }

        .panel-right {
            border-top: 4px solid var(--green);
            background: linear-gradient(160deg, var(--green-soft), #ffffff 85%);
            animation-delay: 80ms;
        }

        h3 {
            margin: 0 0 22px;
            color: var(--green-dark);
            font-size: clamp(1.25rem, 2vw, 1.55rem);
            line-height: 1.3;
            letter-spacing: -0.025em;
        }

        .feedback-text {
            margin: 0;
            color: #3c4b42;
            font-size: 1.05rem;
            line-height: 1.85;
            overflow-wrap: anywhere;
            white-space: pre-line;
        }

        .meta-label {
            display: block;
            margin-top: 20px;
            color: var(--muted);
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.07em;
            text-transform: uppercase;
        }

        .meta-label:first-of-type { margin-top: 0; }

        .meta-value {
            margin: 4px 0 0;
            color: var(--ink);
            font-size: 1rem;
            font-weight: 600;
            overflow-wrap: anywhere;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            padding: 5px 12px;
            border-radius: 999px;
            background: var(--green);
            color: #fff;
            font-size: 0.88rem;
            font-weight: 700;
        }

        @keyframes rise-in {
            from { opacity: 0; transform: translateY(12px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 760px) {
            body { padding: 24px 16px; }
            .layout-grid { grid-template-columns: minmax(0, 1fr); }
            .panel-left { min-height: 0; }
            .btn-back { margin-bottom: 18px; }
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                scroll-behavior: auto !important;
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }
    </style>
</head>
<body>

<main class="page-container">
<a href="dashboard.php" class="btn-back">← Terug naar dashboard</a>

<div class="layout-grid">
    <!-- Links (70%): Volledige toelichting van de gast -->
    <div class="panel-left">
        <h3>Toelichting van de gast</h3>
        <p class="feedback-text">
            <?php echo sanitize($feedback_item['toelichting']); ?>
        </p>
    </div>

    <!-- Rechts (30%): Metadata en status -->
    <div class="panel-right">
        <h3>Details & Metadata</h3>
        
        <span class="meta-label">Score:</span>
        <p class="meta-value"><?php echo $feedback_item['score']; ?> / 5 sterren</p>
        
        <span class="meta-label">Categorie:</span>
        <p class="meta-value"><?php echo sanitize($feedback_item['categorie']); ?></p>
        
        <span class="meta-label">Datum / Tijd:</span>
        <p class="meta-value"><?php echo $feedback_item['aangemaakt_op']; ?></p>
        
        <span class="meta-label">Vestiging Nummer:</span>
        <p class="meta-value">Vestiging ID: <?php echo $feedback_item['vestiging_id']; ?></p>
        
        <span class="meta-label">Status:</span>
        <p class="meta-value"><span class="badge"><?php echo $feedback_item['status']; ?></span></p>
    </div>
</div>
</main>

</body>
</html>
