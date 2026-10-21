<?php
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Manager login';
$error = '';
$email = isset($_POST['email']) && is_string($_POST['email'])
    ? trim($_POST['email'])
    : '';
$databaseUnavailable = false;

try {
    if (currentManager() !== null) {
        header('Location: dashboard.php', true, 303);
        exit;
    }

    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
        $password = isset($_POST['password']) && is_string($_POST['password'])
            ? $_POST['password']
            : '';

        if (!isValidCsrfToken($_POST['csrf_token'] ?? null)) {
            $error = 'De sessie is verlopen. Probeer opnieuw in te loggen.';
        } else {
            $manager = findManager($email);
            if ($manager !== null
                && (int)$manager['actief'] === 1
                && (int)$manager['vestiging_actief'] === 1
                && password_verify($password, $manager['wachtwoord_hash'])) {
                session_regenerate_id(true);
                $_SESSION['manager_id'] = (int)$manager['id'];
                $_SESSION['manager_last_activity'] = time();
                unset($_SESSION['manager_session_expired']);
                header('Location: dashboard.php', true, 303);
                exit;
            }

            $error = 'Onjuiste inloggegevens.';
        }
    }
} catch (PDOException $exception) {
    $databaseUnavailable = true;
}

require __DIR__ . '/includes/header.php';
?>
<main>
    <div class="card" style="max-width: 440px; margin: 0 auto;">
        <h1>Manager login</h1>
        <?php if (isset($_SESSION['manager_session_expired'])): ?>
            <p class="error" role="alert">Uw sessie is verlopen. Log opnieuw in om verder te gaan.</p>
            <?php unset($_SESSION['manager_session_expired']); ?>
        <?php endif; ?>
        <?php if ($databaseUnavailable): ?>
            <p class="error" role="alert">Inloggen is tijdelijk niet mogelijk. Probeer het opnieuw.</p>
            <p><a href="login.php">Opnieuw proberen</a></p>
        <?php else: ?>
            <?php if ($error !== ''): ?>
                <p class="error" role="alert"><?= escapeHtml($error) ?></p>
            <?php endif; ?>
            <form action="login.php" method="post">
                <input type="hidden" name="csrf_token" value="<?= escapeHtml(csrfToken()) ?>">
                <div class="field">
                    <label for="email">E-mailadres</label>
                    <input type="email" id="email" name="email" value="<?= escapeHtml($email) ?>" autocomplete="username" required>
                </div>
                <div class="field">
                    <label for="password">Wachtwoord</label>
                    <input type="password" id="password" name="password" autocomplete="current-password" required>
                </div>
                <button type="submit">Inloggen</button>
            </form>
        <?php endif; ?>
    </div>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
