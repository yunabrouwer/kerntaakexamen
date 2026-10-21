<?php
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Feedbackdetails';
$manager = null;
$feedbackItem = null;
$error = '';
$databaseUnavailable = false;
$feedbackId = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
$feedbackId = $feedbackId !== false && $feedbackId !== null && $feedbackId > 0 ? $feedbackId : 0;

try {
    $manager = currentManager();
    if ($manager === null) {
        header('Location: login.php', true, 303);
        exit;
    }

    $feedbackItem = getManagerFeedbackItem($feedbackId, (int)$manager['vestiging_id']);
    if ($feedbackItem === null) {
        http_response_code(404);
    } elseif (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
        $status = isset($_POST['nieuwe_status']) && is_string($_POST['nieuwe_status'])
            ? $_POST['nieuwe_status']
            : '';
        if (!isValidCsrfToken($_POST['csrf_token'] ?? null)) {
            $error = 'De sessie is verlopen. Vernieuw de pagina en probeer opnieuw.';
        } elseif (!in_array($status, ['Gelezen', 'Actie ondernomen'], true)) {
            $error = 'Kies een geldige status.';
        } elseif (!updateFeedbackStatus($feedbackId, (int)$manager['vestiging_id'], $status)) {
            $feedbackItem = null;
            http_response_code(404);
        } else {
            header('Location: dashboard.php', true, 303);
            exit;
        }
    }
} catch (PDOException $exception) {
    $databaseUnavailable = true;
}

$establishment = null;
require __DIR__ . '/includes/header.php';
?>
<main>
    <?php if ($databaseUnavailable): ?>
        <div class="card detail-card">
            <h1>Feedbackdetails tijdelijk niet beschikbaar</h1>
            <p class="error" role="alert">De gegevens konden niet worden opgehaald of opgeslagen. Probeer het opnieuw.</p>
            <p><a href="detail.php?id=<?= (int)$feedbackId ?>">Opnieuw proberen</a> · <a href="dashboard.php">Terug naar overzicht</a></p>
        </div>
    <?php elseif ($feedbackItem === null): ?>
        <div class="card">
            <h1>Feedback niet gevonden</h1>
            <p class="error" role="alert">Dit feedbackrecord bestaat niet meer of is niet beschikbaar voor uw vestiging.</p>
            <p><a class="button" href="dashboard.php">Terug naar het overzicht</a></p>
        </div>
    <?php else: ?>
        <p><a href="dashboard.php">← Terug naar overzicht</a></p>
        <div class="card">
            <h1>Feedbackdetails</h1>
            <?php if ($error !== ''): ?>
                <p class="error alert" role="alert"><?= escapeHtml($error) ?></p>
            <?php endif; ?>
            <div class="detail-layout">
                <section>
                    <h2>Toelichting</h2>
                    <p class="feedback-copy"><?= escapeHtml($feedbackItem['beschrijving']) ?></p>
                </section>
                <aside>
                    <h2>Metadata</h2>
                    <dl class="metadata">
                        <dt>Score</dt>
                        <dd><?= (int)$feedbackItem['score'] ?> / 5 sterren</dd>
                        <dt>Categorie</dt>
                        <dd><?= escapeHtml($feedbackItem['categorie']) ?></dd>
                        <dt>Datum/tijd</dt>
                        <dd><?= escapeHtml($feedbackItem['aangemaakt_op']) ?></dd>
                        <dt>Vestiging</dt>
                        <dd><?= escapeHtml($feedbackItem['vestiging_naam']) ?></dd>
                        <dt>Huidige status</dt>
                        <dd><?= escapeHtml($feedbackItem['status']) ?></dd>
                    </dl>
                </aside>
            </div>

            <section class="detail-actions">
                <h2>Status aanpassen</h2>
                <form action="detail.php?id=<?= (int)$feedbackItem['id'] ?>" method="post">
                    <input type="hidden" name="csrf_token" value="<?= escapeHtml(csrfToken()) ?>">
                    <button type="submit" name="nieuwe_status" value="Gelezen">Gelezen</button>
                    <button class="button-secondary" type="submit" name="nieuwe_status" value="Actie ondernomen">Actie ondernomen</button>
                </form>
            </section>
        </div>
    <?php endif; ?>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
