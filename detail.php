<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/database.php';

startSecureSession();
$pdo = getDatabaseConnection();
$manager = requireManager($pdo);
$establishment = ['vestiging_naam' => $manager['vestiging_naam']];
$feedbackId = filter_var($_GET['id'] ?? $_POST['id'] ?? null, FILTER_VALIDATE_INT);

if ($feedbackId === false || $feedbackId === null || $feedbackId < 1) {
    http_response_code(404);
    exit('Deze feedback is niet gevonden.');
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        exit('Je aanvraag kon niet worden gecontroleerd. Vernieuw de pagina en probeer opnieuw.');
    }

    $status = sanitizeInput($_POST['status'] ?? '');
    $allowedStatuses = ['Gelezen', 'Actie ondernomen'];
    if (!in_array($status, $allowedStatuses, true)) {
        http_response_code(400);
        exit('Ongeldige status.');
    }

    dbQuery(
        $pdo,
        'UPDATE feedback SET status = ? WHERE id = ? AND vestiging_id = ?',
        [$status, $feedbackId, (int) $manager['vestiging_id']]
    );
    header('Location: detail.php?id=' . $feedbackId, true, 303);
    exit;
}

$statement = dbQuery(
    $pdo,
    'SELECT id, score, categorie, beschrijving, status, aangemaakt_op
     FROM feedback WHERE id = ? AND vestiging_id = ?',
    [$feedbackId, (int) $manager['vestiging_id']]
);
$item = $statement->fetch();
if (!$item) {
    http_response_code(404);
    exit('Deze feedback is niet gevonden.');
}

$pageTitle = 'Feedback bekijken';
require __DIR__ . '/includes/header.php';
?>
<main>
    <a href="dashboard.php">&larr; Terug naar overzicht</a>
    <section class="detail-layout">
        <article class="card feedback-text">
            <p class="muted">Feedback #<?= (int) $item['id'] ?></p>
            <h1>Ervaring van een gast</h1>
            <p class="feedback-description"><?= nl2br(escapeHtml($item['beschrijving'])) ?></p>
        </article>
        <aside class="card metadata">
            <h2>Gegevens</h2>
            <dl>
                <dt>Score</dt><dd><?= escapeHtml($item['score']) ?>/5</dd>
                <dt>Categorie</dt><dd><?= escapeHtml($item['categorie']) ?></dd>
                <dt>Datum</dt><dd><?= escapeHtml(date('d-m-Y H:i', strtotime($item['aangemaakt_op']))) ?></dd>
                <dt>Status</dt><dd><?= escapeHtml($item['status']) ?></dd>
            </dl>
            <form method="post" action="detail.php?id=<?= (int) $item['id'] ?>">
                <input type="hidden" name="csrf_token" value="<?= escapeHtml(csrfToken()) ?>">
                <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                <div class="field">
                    <label for="status">Status bijwerken</label>
                    <select id="status" name="status" required>
                        <option value="Gelezen">Gelezen</option>
                        <option value="Actie ondernomen">Actie ondernomen</option>
                    </select>
                </div>
                <button type="submit">Status opslaan</button>
            </form>
        </aside>
    </section>
</main>
<style>
    .detail-layout { display: grid; grid-template-columns: 7fr 3fr; gap: 18px; margin-top: 18px; }
    .feedback-text { min-height: 320px; }
    .feedback-description { line-height: 1.7; overflow-wrap: anywhere; }
    .metadata h2 { margin-top: 0; font-size: 1.2rem; }
    dl { display: grid; grid-template-columns: 1fr; gap: 5px; }
    dt { color: #61717b; font-size: .9rem; }
    dd { margin: 0 0 12px; font-weight: 600; }
    @media (max-width: 720px) { .detail-layout { grid-template-columns: 1fr; } }
</style>
<?php require __DIR__ . '/includes/footer.php'; ?>
