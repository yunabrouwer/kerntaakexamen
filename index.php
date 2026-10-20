<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/database.php';

startSecureSession();
$pdo = getDatabaseConnection();
$isPost = ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
$slug = sanitizeInput($isPost ? ($_POST['vestiging'] ?? '') : ($_GET['vestiging'] ?? ''));

if ($slug === '' || !preg_match('/\A[a-z0-9-]+\z/', $slug)) {
    http_response_code(404);
    exit('Deze vestiging is niet gevonden.');
}

$statement = dbQuery($pdo, 'SELECT id, naam, slug FROM vestigingen WHERE slug = ?', [$slug]);
$establishment = $statement->fetch();
if (!$establishment) {
    http_response_code(404);
    exit('Deze vestiging is niet gevonden.');
}

$categories = ['Service', 'Kwaliteit', 'Sfeer', 'Prijs-kwaliteit', 'Overig'];
$score = sanitizeInput($isPost ? ($_POST['score'] ?? '') : '');
$category = sanitizeInput($isPost ? ($_POST['categorie'] ?? '') : '');
$description = sanitizeInput($isPost ? ($_POST['beschrijving'] ?? '') : '');
$errors = [];

if ($isPost) {
    if (!validateCsrfToken($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        exit('Je aanvraag kon niet worden gecontroleerd. Vernieuw de pagina en probeer opnieuw.');
    }

    if (!preg_match('/\A[1-5]\z/', $score)) {
        $errors[] = 'Kies een score van 1 tot en met 5.';
    }
    if (!in_array($category, $categories, true)) {
        $errors[] = 'Kies een categorie uit de lijst.';
    }
    if ($description === '' || mb_strlen($description) > 5000) {
        $errors[] = 'Vul een toelichting in van maximaal 5000 tekens.';
    }

    if ($errors === []) {
        dbQuery(
            $pdo,
            'INSERT INTO feedback (vestiging_id, score, categorie, beschrijving)
             VALUES (?, ?, ?, ?)',
            [(int) $establishment['id'], (int) $score, $category, $description]
        );
        header('Location: thanks.php', true, 303);
        exit;
    }
}

$pageTitle = 'Deel je ervaring';
require __DIR__ . '/includes/header.php';
?>
<main>
    <section class="card survey-card">
        <h1>Hoe was je ervaring?</h1>
        <p class="muted">Deel je feedback met <?= escapeHtml($establishment['naam']) ?>. Je reactie helpt ons verbeteren.</p>

        <?php if ($errors !== []): ?>
            <div class="error" role="alert">
                <p>Controleer de volgende punten:</p>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= escapeHtml($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" action="index.php?vestiging=<?= rawurlencode($slug) ?>">
            <input type="hidden" name="csrf_token" value="<?= escapeHtml(csrfToken()) ?>">
            <input type="hidden" name="vestiging" value="<?= escapeHtml($slug) ?>">

            <fieldset class="field rating-field">
                <legend>Je score <span aria-hidden="true">*</span></legend>
                <div class="rating-options">
                    <?php for ($rating = 1; $rating <= 5; $rating++): ?>
                        <label class="rating-option" data-score="<?= $rating ?>">
                            <input type="radio" name="score" value="<?= $rating ?>" required
                                <?= $score === (string) $rating ? 'checked' : '' ?>>
                            <span><?= $rating ?></span>
                        </label>
                    <?php endfor; ?>
                </div>
                <small class="muted">1 = zeer ontevreden, 5 = zeer tevreden</small>
            </fieldset>

            <div class="field">
                <label for="categorie">Categorie <span aria-hidden="true">*</span></label>
                <select id="categorie" name="categorie" required>
                    <option value="">Kies een categorie</option>
                    <?php foreach ($categories as $option): ?>
                        <option value="<?= escapeHtml($option) ?>" <?= $category === $option ? 'selected' : '' ?>>
                            <?= escapeHtml($option) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="field">
                <label for="beschrijving">Je toelichting <span aria-hidden="true">*</span></label>
                <textarea id="beschrijving" name="beschrijving" rows="6" maxlength="5000"
                    required><?= escapeHtml($description) ?></textarea>
            </div>

            <button type="submit">Feedback versturen</button>
        </form>
    </section>
</main>
<style>
    .survey-card { max-width: 650px; margin: 0 auto; }
    .rating-field { padding: 0; border: 0; }
    .rating-field legend { margin-bottom: 10px; font-weight: 700; }
    .rating-options { display: flex; gap: 10px; margin: 12px 0; }
    .rating-option { flex: 1; cursor: pointer; }
    .rating-option input { position: absolute; width: 1px; height: 1px; opacity: 0; }
    .rating-option span {
        display: grid; min-height: 48px; place-items: center; border: 2px solid currentColor;
        border-radius: 8px; font-size: 1.15rem; font-weight: 700;
    }
    .rating-option[data-score="1"], .rating-option[data-score="2"] { color: #c62828; }
    .rating-option[data-score="3"] { color: #ef8c00; }
    .rating-option[data-score="4"], .rating-option[data-score="5"] { color: #2E7D32; }
    .rating-option input:checked + span { background: #173a37; border-color: #173a37; color: #fff; }
    .rating-option input:focus-visible + span { outline: 3px solid #173a37; outline-offset: 2px; }
    textarea { resize: vertical; }
</style>
<?php require __DIR__ . '/includes/footer.php'; ?>
