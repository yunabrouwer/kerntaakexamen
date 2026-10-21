<?php
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Vestigingen vergelijken';
$manager = null;
$comparison = [];
$databaseUnavailable = false;
$showManagerLogout = true;

try {
    $manager = currentManager();
    if ($manager === null) {
        header('Location: login.php', true, 303);
        exit;
    }

    $comparison = getEstablishmentFeedbackComparison();
} catch (PDOException $exception) {
    $databaseUnavailable = true;
}

$establishment = null;
require __DIR__ . '/includes/header.php';
?>
<main>
    <p><a href="dashboard.php">← Terug naar het dashboard</a></p>
    <section class="card comparison-card">
        <h1>Vestigingen vergelijken</h1>
        <p class="muted">Vergelijk gemiddelde scores en geaggregeerde feedback van alle actieve vestigingen. Individuele feedback en toelichtingen zijn hier niet zichtbaar.</p>
        <?php if ($databaseUnavailable): ?>
            <p class="error alert" role="alert">De vergelijkingsgegevens konden niet worden opgehaald. Probeer het opnieuw.</p>
            <p><a class="button button-secondary" href="comparison.php">Opnieuw proberen</a></p>
        <?php elseif ($comparison === []): ?>
            <p class="muted">Er zijn momenteel geen actieve vestigingen om te vergelijken.</p>
        <?php else: ?>
            <div class="table-wrap">
                <table class="comparison-table">
                    <thead>
                        <tr>
                            <th scope="col">Vestiging</th>
                            <th scope="col">Gemiddelde</th>
                            <th scope="col">Beoordelingen</th>
                            <th scope="col">Scoreverdeling (1–5)</th>
                            <th scope="col">Feedback per categorie</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($comparison as $item): ?>
                            <tr>
                                <th scope="row"><?= escapeHtml($item['naam']) ?></th>
                                <td>
                                    <strong><?= number_format((float)$item['gemiddelde_score'], 1) ?> / 5</strong>
                                    <div
                                        class="comparison-bar"
                                        role="progressbar"
                                        aria-label="Gemiddelde score <?= escapeHtml($item['naam']) ?>"
                                        aria-valuemin="0"
                                        aria-valuemax="5"
                                        aria-valuenow="<?= escapeHtml($item['gemiddelde_score']) ?>"
                                    >
                                        <span style="width: <?= min(100, max(0, (float)$item['gemiddelde_score'] * 20)) ?>%"></span>
                                    </div>
                                </td>
                                <td><?= (int)$item['feedback_aantal'] ?></td>
                                <td>
                                    <div class="score-distribution" aria-label="Scores 1 tot 5 sterren">
                                        <?php for ($score = 1; $score <= 5; $score++): ?>
                                            <span><strong><?= $score ?>★</strong> <?= (int)$item['score_' . $score] ?></span>
                                        <?php endfor; ?>
                                    </div>
                                </td>
                                <td>
                                    Eten: <?= (int)$item['categorie_eten'] ?><br>
                                    Bediening: <?= (int)$item['categorie_bediening'] ?><br>
                                    Sfeer: <?= (int)$item['categorie_sfeer'] ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
