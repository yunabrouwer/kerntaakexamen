<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/database.php';

startSecureSession();
$pdo = getDatabaseConnection();
$manager = requireManager($pdo);
$establishment = ['vestiging_naam' => $manager['vestiging_naam']];
$filter = sanitizeInput($_GET['filter'] ?? 'all');
$allowedFilters = ['all', 'negative', 'week'];
if (!in_array($filter, $allowedFilters, true)) {
    $filter = 'all';
}

$where = 'vestiging_id = ?';
$parameters = [(int) $manager['vestiging_id']];
if ($filter === 'negative') {
    $where .= ' AND score <= ?';
    $parameters[] = 2;
} elseif ($filter === 'week') {
    $where .= ' AND aangemaakt_op >= DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 7 DAY)';
}

$average = calculateEstablishmentAverage($pdo, (int) $manager['vestiging_id']);
$feedback = dbQuery(
    $pdo,
    "SELECT id, score, categorie, beschrijving, status, aangemaakt_op
     FROM feedback WHERE {$where} ORDER BY aangemaakt_op DESC",
    $parameters
)->fetchAll();

$pageTitle = 'Dashboard';
require __DIR__ . '/includes/header.php';
?>
<main>
    <section class="card">
        <h1>Feedbackoverzicht</h1>
        <p class="muted">Gemiddelde score: <strong><?= number_format($average, 1, ',', '.') ?>/5</strong></p>

        <nav class="filter-nav" aria-label="Feedback filter">
            <a class="button" href="dashboard.php?filter=all" <?= $filter === 'all' ? 'aria-current="page"' : '' ?>>Alles</a>
            <a class="button" href="dashboard.php?filter=negative" <?= $filter === 'negative' ? 'aria-current="page"' : '' ?>>Negatief (1-2)</a>
            <a class="button" href="dashboard.php?filter=week" <?= $filter === 'week' ? 'aria-current="page"' : '' ?>>Afgelopen week</a>
        </nav>

        <?php if ($feedback === []): ?>
            <p class="muted">Er is nog geen feedback voor deze selectie.</p>
        <?php else: ?>
            <div class="table-wrap">
                <table>
                    <thead>
                    <tr>
                        <th>Datum</th>
                        <th>Score</th>
                        <th>Categorie</th>
                        <th>Toelichting</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($feedback as $item): ?>
                        <tr>
                            <td><?= escapeHtml(date('d-m-Y H:i', strtotime($item['aangemaakt_op']))) ?></td>
                            <td><?= escapeHtml($item['score']) ?>/5</td>
                            <td><?= escapeHtml($item['categorie']) ?></td>
                            <td><?= escapeHtml(mb_strimwidth($item['beschrijving'], 0, 90, '…')) ?></td>
                            <td><?= escapeHtml($item['status']) ?></td>
                            <td><a href="detail.php?id=<?= (int) $item['id'] ?>">Bekijken</a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</main>
<style>
    .filter-nav { display: flex; flex-wrap: wrap; gap: 9px; margin: 20px 0; }
    .filter-nav .button { font-size: .92rem; }
    .filter-nav [aria-current="page"] { background: #173a37; }
</style>
<?php require __DIR__ . '/includes/footer.php'; ?>
