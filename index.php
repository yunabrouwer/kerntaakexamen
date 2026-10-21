<?php
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Feedback';
$categories = ['Eten', 'Bediening', 'Sfeer'];
$errors = [];
$generalError = '';
$databaseUnavailable = false;
$establishment = null;
$establishments = [];
$isPost = ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
$score = isset($_POST['score']) && is_scalar($_POST['score']) ? (string)$_POST['score'] : '';
$category = isset($_POST['categorie']) && is_string($_POST['categorie']) ? trim($_POST['categorie']) : '';
$description = isset($_POST['toelichting']) && is_string($_POST['toelichting']) ? trim($_POST['toelichting']) : '';
$requestedGetId = filter_var($_GET['vestiging_id'] ?? null, FILTER_VALIDATE_INT);
$retryUrl = $requestedGetId !== false && $requestedGetId !== null && $requestedGetId > 0
    ? 'index.php?vestiging_id=' . $requestedGetId
    : 'index.php';

try {
    $establishments = getActiveEstablishments();
    $requestedId = filter_var(
        $isPost ? ($_POST['vestiging_id'] ?? null) : ($_GET['vestiging_id'] ?? null),
        FILTER_VALIDATE_INT
    );
    $establishment = getActiveEstablishment(
        $requestedId !== false && $requestedId !== null && $requestedId > 0 ? $requestedId : null
    );

    if ($establishment !== null) {
        $pageTitle = 'Feedback - ' . $establishment['naam'];
        $retryUrl = 'index.php?vestiging_id=' . (int)$establishment['id'];
    } elseif (isset($_GET['vestiging_id']) || isset($_POST['vestiging_id']) || isset($_GET['vestiging'])) {
        $generalError = 'Deze survey is niet beschikbaar. Controleer de QR-code of neem contact op met de vestiging.';
    }

    if ($isPost && $establishment !== null) {
        if (!isValidCsrfToken($_POST['csrf_token'] ?? null)) {
            $generalError = 'Het formulier is verlopen. Controleer uw invoer en probeer het opnieuw.';
        }

        $scoreNumber = filter_var($score, FILTER_VALIDATE_INT);
        if ($scoreNumber === false || $scoreNumber < 1 || $scoreNumber > 5) {
            $errors['score'] = 'Kies een score van 1 tot en met 5.';
        }
        if (!in_array($category, $categories, true)) {
            $errors['categorie'] = 'Kies een categorie.';
        }
        if ($description === '') {
            $errors['toelichting'] = 'Vul een toelichting in.';
        }
        if (mb_strlen($description) > 5000) {
            $errors['toelichting'] = 'De toelichting mag maximaal 5000 tekens bevatten.';
        }

        $submissionToken = $_SESSION['feedback_submission_token'] ?? '';
        $submittedToken = $_POST['submission_token'] ?? '';
        if (!is_string($submissionToken) || $submissionToken === ''
            || !is_string($submittedToken) || !hash_equals($submissionToken, $submittedToken)) {
            $generalError = 'Dit formulier is al verstuurd of verlopen. Laad de survey opnieuw om feedback te versturen.';
        }

        if ($generalError === '' && $errors === []) {
            try {
                createFeedback(
                    (int)$establishment['id'],
                    (int)$scoreNumber,
                    $category,
                    $description,
                    $submissionToken
                );
                unset($_SESSION['feedback_submission_token']);
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                header('Location: thanks.php', true, 303);
                exit;
            } catch (RuntimeException $exception) {
                $generalError = 'Deze survey is niet meer beschikbaar. Uw feedback is niet verstuurd.';
            }
        }
    }
} catch (PDOException $exception) {
    $databaseUnavailable = true;
    $generalError = 'Er is tijdelijk een probleem met het opslaan van uw feedback. Controleer uw invoer en probeer het opnieuw.';
    $establishments = [];
    $establishment = null;
}

if ($establishment !== null && empty($_SESSION['feedback_submission_token'])) {
    $_SESSION['feedback_submission_token'] = bin2hex(random_bytes(32));
}

require __DIR__ . '/includes/header.php';
?>
<main>
    <div class="card survey-card">
        <?php if ($establishment === null): ?>
            <h1>Feedback</h1>
            <?php if ($generalError !== ''): ?>
                <p class="error" role="alert"><?= escapeHtml($generalError) ?></p>
                <?php if ($databaseUnavailable): ?>
                    <p><a class="button" href="<?= escapeHtml($retryUrl) ?>">Opnieuw proberen</a></p>
                <?php endif; ?>
            <?php elseif ($establishments === []): ?>
                <p class="muted">Er zijn momenteel geen actieve vestigingen.</p>
            <?php else: ?>
                <form action="index.php" method="get">
                    <div class="field">
                        <label for="vestiging_id">Vestiging</label>
                        <select name="vestiging_id" id="vestiging_id" required>
                            <option value="">Kies een vestiging...</option>
                            <?php foreach ($establishments as $item): ?>
                                <option value="<?= (int)$item['id'] ?>"><?= escapeHtml($item['naam']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit">Bekijk survey</button>
                </form>
            <?php endif; ?>
        <?php else: ?>
            <h1>Deel uw ervaring</h1>
            <p class="survey-intro">Uw mening telt bij <?= escapeHtml($establishment['naam']) ?>.</p>

            <?php if ($generalError !== ''): ?>
                <p class="error" role="alert"><?= escapeHtml($generalError) ?></p>
                <?php if (str_contains($generalError, 'tijdelijk')): ?>
                    <p><a href="<?= escapeHtml($retryUrl) ?>">Opnieuw proberen</a></p>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ($errors !== []): ?>
                <p class="error" role="alert">Controleer de gemarkeerde velden.</p>
            <?php endif; ?>

            <form action="<?= escapeHtml($retryUrl) ?>" method="post" id="feedback-form">
                <input type="hidden" name="vestiging_id" value="<?= (int)$establishment['id'] ?>">
                <input type="hidden" name="csrf_token" value="<?= escapeHtml(csrfToken()) ?>">
                <input type="hidden" name="submission_token" value="<?= escapeHtml($_SESSION['feedback_submission_token'] ?? '') ?>">

                <fieldset class="field" aria-describedby="<?= isset($errors['score']) ? 'score-error' : '' ?>">
                    <legend>Hoe was uw ervaring? (1-5 sterren)</legend>
                    <div class="rating">
                        <?php for ($value = 5; $value >= 1; $value--): ?>
                            <input type="radio" id="star<?= $value ?>" name="score" value="<?= $value ?>" <?= $value === 5 ? 'required' : '' ?> <?= $score === (string)$value ? 'checked' : '' ?>>
                            <label for="star<?= $value ?>" aria-label="<?= $value ?> sterren">★</label>
                        <?php endfor; ?>
                    </div>
                    <?php if (isset($errors['score'])): ?>
                        <p class="error" id="score-error"><?= escapeHtml($errors['score']) ?></p>
                    <?php endif; ?>
                </fieldset>

                <div class="field">
                    <label for="categorie">Categorie</label>
                    <select name="categorie" id="categorie" required aria-invalid="<?= isset($errors['categorie']) ? 'true' : 'false' ?>">
                        <option value="">Kies een categorie...</option>
                        <?php foreach ($categories as $option): ?>
                            <option value="<?= escapeHtml($option) ?>" <?= $category === $option ? 'selected' : '' ?>><?= escapeHtml($option) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['categorie'])): ?>
                        <p class="error"><?= escapeHtml($errors['categorie']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="field">
                    <label for="toelichting">Toelichting</label>
                    <textarea name="toelichting" id="toelichting" rows="5" maxlength="5000" required aria-invalid="<?= isset($errors['toelichting']) ? 'true' : 'false' ?>"><?= escapeHtml($description) ?></textarea>
                    <?php if (isset($errors['toelichting'])): ?>
                        <p class="error"><?= escapeHtml($errors['toelichting']) ?></p>
                    <?php endif; ?>
                </div>

                <button type="submit" id="submit-feedback">Feedback versturen</button>
            </form>
        <?php endif; ?>
    </div>
</main>
<script>
    document.getElementById('feedback-form')?.addEventListener('submit', function () {
        const button = document.getElementById('submit-feedback');
        button.disabled = true;
        button.textContent = 'Versturen...';
    });
</script>
<?php require __DIR__ . '/includes/footer.php'; ?>
