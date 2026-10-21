<?php
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Manager dashboard';
$manager = null;
$feedback = [];
$filter = isset($_GET['filter']) && is_string($_GET['filter']) ? $_GET['filter'] : 'all';
if (!in_array($filter, ['all', 'negative', 'open', 'week'], true)) {
    $filter = 'all';
}
$average = '0.0';
$databaseUnavailable = false;
$showManagerLogout = true;

try {
    $manager = currentManager();
    if ($manager === null) {
        header('Location: login.php', true, 303);
        exit;
    }

    $feedback = getManagerFeedback((int)$manager['vestiging_id'], $filter);
    $average = getManagerAverageScore((int)$manager['vestiging_id']);
} catch (PDOException $exception) {
    $databaseUnavailable = true;
}

require __DIR__ . '/includes/header.php';
?>
<main>
    <?php if ($databaseUnavailable): ?>
        <div class="card">
            <h1>Dashboard tijdelijk niet beschikbaar</h1>
            <p class="error" role="alert">De gegevens konden niet worden opgehaald. Probeer het opnieuw.</p>
            <p><a href="dashboard.php?filter=<?= escapeHtml(rawurlencode($filter)) ?>">Opnieuw proberen</a></p>
        </div>
    <?php else: ?>
        <section class="card dashboard-summary">
            <div>
                <h1 class="dashboard-heading">Dashboard</h1>
                <p class="muted"><?= escapeHtml($manager['vestiging_naam']) ?></p>
                <nav class="filter-nav" aria-label="Feedbackfilter">
                    <a href="dashboard.php?filter=all" <?= $filter === 'all' ? 'aria-current="page"' : '' ?>>Alle feedback</a>
                    <a href="dashboard.php?filter=negative" <?= $filter === 'negative' ? 'aria-current="page"' : '' ?>>Negatieve scores</a>
                    <a href="dashboard.php?filter=open" <?= $filter === 'open' ? 'aria-current="page"' : '' ?>>Nieuw</a>
                    <a href="dashboard.php?filter=week" <?= $filter === 'week' ? 'aria-current="page"' : '' ?>>Deze week</a>
                </nav>
                <div class="dashboard-actions">
                    <a class="button" href="qr-codes.php">QR-code voor vragenlijst</a>
                </div>
            </div>
            <div class="score-stack">
                <div class="score-card" aria-label="Gemiddelde score <?= escapeHtml($average) ?> uit 5">
                    <strong><?= escapeHtml($average) ?></strong>
                    <span>Gemiddelde score / 5</span>
                </div>
                <a class="button button-secondary comparison-link" href="comparison.php">Vergelijk vestigingen</a>
            </div>
        </section>

        <section class="card dashboard-list">
            <h2>Feedback</h2>
            <?php if ($feedback === []): ?>
                <p class="muted">Geen feedback gevonden voor dit filter.</p>
            <?php else: ?>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Datum/tijd</th>
                                <th>Score</th>
                                <th>Categorie</th>
                                <th>Toelichting</th>
                                <th>Status</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($feedback as $item): ?>
                                <tr>
                                    <td><?= escapeHtml($item['aangemaakt_op']) ?></td>
                                    <td><?= (int)$item['score'] ?> / 5</td>
                                    <td><?= escapeHtml($item['categorie']) ?></td>
                                    <td><?= escapeHtml(mb_strimwidth($item['beschrijving'], 0, 120, '…')) ?></td>
                                    <td><?= escapeHtml($item['status']) ?></td>
                                    <td><a href="detail.php?id=<?= (int)$item['id'] ?>">Open feedback</a></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    <?php endif; ?>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
