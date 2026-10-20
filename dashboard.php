<?php
require_once 'includes/functions.php';

$all_feedback = get_all_feedback();
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

// Filter de feedback op basis van de GET-parameter
if ($filter === 'negative') {
    $all_feedback = array_filter($all_feedback, function($item) {
        return $item['score'] <= 2;
    });
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

<!-- Vervang de bestaande tabel in dashboard.php door deze versie -->
<table>
    <thead>
        <tr>
            <th>Datum/Tijd</th>
            <th>Score</th>
            <th>Categorie</th>
            <th>Toelichting</th>
            <th>Actie</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($all_feedback)): ?>
            <tr><td colspan="5">Nog geen feedback ontvangen.</td></tr>
        <?php else: ?>
            <?php foreach ($all_feedback as $item): 
                $bg = 'badge-green';
                if ($item['score'] == 3) $bg = 'badge-orange';
                if ($item['score'] <= 2) $bg = 'badge-red';
            ?>
                <tr>
                    <td><?php echo $item['aangemaakt_op']; ?></td>
                    <td><span class="badge <?php echo $bg; ?>"><?php echo $item['score']; ?> Sterren</span></td>
                    <td><?php echo sanitize($item['categorie']); ?></td>
                    <td><?php echo sanitize($item['toelichting']); ?></td>
                    <td><a href="detail.php?id=<?php echo $item['id']; ?>" style="color: #2E7D32; text-decoration: none; font-weight: bold;">Bekijken →</a></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>


</body>
</html>
