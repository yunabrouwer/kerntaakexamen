<?php
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Manager dashboard';
$manager = null;
$feedback = [];
$filter = isset($_GET['filter']) && is_string($_GET['filter']) ? $_GET['filter'] : 'all';
if (!in_array($filter, ['all', 'negative', 'open'], true)) {
    $filter = 'all';
}
$average = '0.0';
$databaseUnavailable = false;

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
        <section class="card">
            <h1>Dashboard - <?= escapeHtml($manager['vestiging_naam']) ?></h1>
            <p>Gemiddelde score: <strong><?= escapeHtml($average) ?> / 5</strong></p>
            <p>
                <a href="dashboard.php?filter=all" <?= $filter === 'all' ? 'aria-current="page"' : '' ?>>Alle feedback</a> |
                <a href="dashboard.php?filter=negative" <?= $filter === 'negative' ? 'aria-current="page"' : '' ?>>Negatieve scores (1-2)</a> |
                <a href="dashboard.php?filter=open" <?= $filter === 'open' ? 'aria-current="page"' : '' ?>>Nieuw</a>
            </p>
            <p><a class="button" href="qr-codes.php">QR-code voor vragenlijst</a></p>
            <form action="logout.php" method="post">
                <input type="hidden" name="csrf_token" value="<?= escapeHtml(csrfToken()) ?>">
                <button type="submit">Uitloggen</button>
            </form>
        </section>

        <section class="card" style="margin-top: 20px;">
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
