<?php
require_once 'includes/functions.php';

$id = isset($_GET['id']) ? $_GET['id'] : '';
$all_feedback = get_all_feedback();
$feedback_item = null;
$item_index = -1;

// Zoek het juiste feedback-item en onthoud de positie in de lijst
foreach ($all_feedback as $index => $item) {
    if ($item['id'] == $id) {
        $feedback_item = $item;
        $item_index = $index;
        break;
    }
}

if (!$feedback_item) {
    die("<h1>Fout</h1><p>Feedback-item niet gevonden.</p><a href='dashboard.php'>Terug naar dashboard</a>");
}

// MVP FUNCTIE: Status bijwerken zodra de manager op een knop drukt
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nieuwe_status'])) {
    $nieuwe_status = $_POST['nieuwe_status'];
    
    // Alleen geldige statussen toestaan conform het ontwerp
    if (in_array($nieuwe_status, ['Gelezen', 'Actie ondernomen'])) {
        $all_feedback[$item_index]['status'] = $nieuwe_status;
        
        // Sla de gewijzigde lijst direct weer op in het tekstbestand
        $file = __DIR__ . '/feedback.txt';
        file_put_contents($file, json_encode($all_feedback, JSON_PRETTY_PRINT));
        
        // Ververs de pagina zodat de nieuwe status meteen zichtbaar is
        header("Location: detail.php?id=" . $id);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>TableTalk - Feedback Detail</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 30px; background: #f4f4f4; }
        .btn-back { display: inline-block; margin-bottom: 20px; color: #2E7D32; text-decoration: none; font-weight: bold; }
        .layout-grid { display: grid; grid-template-columns: 70% 30%; gap: 20px; }
        .panel-left { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .panel-right { background: #e8f5e9; padding: 25px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); border-left: 4px solid #2E7D32; }
        h3 { margin-top: 0; color: #2E7D32; }
        .meta-label { font-weight: bold; margin-top: 15px; display: block; }
        .status-btn { background: #2E7D32; color: white; border: none; padding: 8px 12px; border-radius: 4px; cursor: pointer; font-weight: bold; margin-right: 5px; margin-top: 10px; }
        .status-btn.action { background: #FFA000; }
        @media (max-width: 768px) { .layout-grid { grid-template-columns: 100%; } }
    </style>
</head>
<body>

<a href="dashboard.php" class="btn-back">← Terug naar dashboard</a>

<div class="layout-grid">
    <!-- Links (70%): Volledige toelichting -->
    <div class="panel-left">
        <h3>Toelichting van de gast</h3>
        <p style="font-size: 16px; line-height: 1.6; white-space: pre-line;">
            <?php echo sanitize($feedback_item['toelichting']); ?>
        </p>
    </div>

    <!-- Rechts (30%): Metadata & Status Formulier -->
    <div class="panel-right">
        <h3>Details & Metadata</h3>
        
        <span class="meta-label">Score:</span>
        <p><?php echo $feedback_item['score']; ?> / 5 sterren</p>
        
        <span class="meta-label">Categorie:</span>
        <p><?php echo sanitize($feedback_item['categorie']); ?></p>
        
        <span class="meta-label">Datum / Tijd:</span>
        <p><?php echo $feedback_item['aangemaakt_op']; ?></p>
        
        <span class="meta-label">Vestiging:</span>
        <p><?php echo sanitize(get_vestiging_naam($feedback_item['vestiging_id'])); ?></p>
        
        <span class="meta-label">Huidige Status:</span>
        <p><span style="background:#2E7D32; color:white; padding:4px 8px; border-radius:4px; font-weight:bold;"><?php echo $feedback_item['status']; ?></span></p>
        
        <!-- Knoppen om de status live aan te passen -->
        <span class="meta-label">Status aanpassen:</span>
        <form action="" method="POST">
            <button type="submit" name="nieuwe_status" value="Gelezen" class="status-btn">Markeer als Gelezen</button>
            <button type="submit" name="nieuwe_status" value="Actie ondernomen" class="status-btn action">Actie ondernomen</button>
        </form>
    </div>
</div>

</body>
</html>
