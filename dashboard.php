<?php
require_once 'includes/functions.php';

$all_feedback = get_all_feedback();
$visible_feedback = $all_feedback;
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

// Filter de feedback op basis van de GET-parameter
if ($filter === 'negative') {
    $visible_feedback = array_filter($all_feedback, function($item) {
        return $item['score'] <= 2;
    });
}

$feedback_per_vestiging = [];
foreach (get_vestigingen() as $vestiging_id => $vestiging_naam) {
    $feedback_per_vestiging[$vestiging_id] = [
        'naam' => $vestiging_naam,
        'feedback' => [],
        'totaal_score' => 0,
    ];
}

foreach ($all_feedback as $item) {
    $vestiging_id = (int)($item['vestiging_id'] ?? 0);
    if (!isset($feedback_per_vestiging[$vestiging_id])) {
        $feedback_per_vestiging[$vestiging_id] = [
            'naam' => get_vestiging_naam($vestiging_id),
            'feedback' => [],
            'totaal_score' => 0,
        ];
    }

    $feedback_per_vestiging[$vestiging_id]['totaal_score'] += (int)$item['score'];
}

foreach ($visible_feedback as $item) {
    $vestiging_id = (int)($item['vestiging_id'] ?? 0);
    $feedback_per_vestiging[$vestiging_id]['feedback'][] = $item;
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>TableTalk - Manager Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 30px; background: #f4f4f4; }
        .dashboard-header { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); margin-bottom: 20px; }
        .score-box { font-size: 24px; color: #2E7D32; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #2E7D32; color: white; }
        .filter-links { margin-bottom: 15px; }
        .filter-links a { margin-right: 15px; text-decoration: none; color: #2E7D32; font-weight: bold; }
        .badge { padding: 5px 10px; border-radius: 4px; color: white; font-weight: bold; }
        .badge-green { background: #2E7D32; }
        .badge-orange { background: #FFA000; }
        .badge-red { background: #D32F2F; }
        section { margin: 24px 0; }
        section h2 { margin-bottom: 8px; }
    </style>
</head>
<body>

<div class="dashboard-header">
    <h1>TableTalk Dashboard</h1>
    <div class="score-box">Gemiddelde Score: <?php echo get_average_score(); ?> / 5.0</div>
</div>

<div class="filter-links">
    <a href="dashboard.php?filter=all">Toon alle scores</a>
    <a href="dashboard.php?filter=negative">Alleen Negatief (1-2 sterren)</a>
</div>

<?php foreach ($feedback_per_vestiging as $vestiging): ?>
    <?php
        $vestiging_feedback = $vestiging['feedback'];
        $gemiddelde = empty($vestiging_feedback)
            ? 'n.v.t.'
            : number_format($vestiging['totaal_score'] / count($vestiging_feedback), 1);
    ?>
    <section>
        <h2><?php echo sanitize($vestiging['naam']); ?></h2>
        <div class="score-box">Gemiddelde score: <?php echo $gemiddelde; ?> / 5.0</div>

        <table>
            <thead>
                <tr>
                    <th>Datum/Tijd</th>
                    <th>Score</th>
                    <th>Categorie</th>
                    <th>Toelichting</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($vestiging_feedback)): ?>
                    <tr><td colspan="4">Nog geen feedback voor deze vestiging.</td></tr>
                <?php else: ?>
                    <?php foreach ($vestiging_feedback as $item):
                        $bg = 'badge-green';
                        if ($item['score'] == 3) $bg = 'badge-orange';
                        if ($item['score'] <= 2) $bg = 'badge-red';
                    ?>
                        <tr>
                            <td><?php echo sanitize($item['aangemaakt_op']); ?></td>
                            <td><span class="badge <?php echo $bg; ?>"><?php echo (int)$item['score']; ?> Sterren</span></td>
                            <td><?php echo sanitize($item['categorie']); ?></td>
                            <td><?php echo sanitize($item['toelichting']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </section>
<?php endforeach; ?>

</body>
</html>
