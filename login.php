<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/database.php';

startSecureSession();
if (isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$username = '';
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        exit('Je aanvraag kon niet worden gecontroleerd. Vernieuw de pagina en probeer opnieuw.');
    }

    $username = sanitizeInput($_POST['gebruikersnaam'] ?? '');
    $password = $_POST['wachtwoord'] ?? '';
    if (!is_string($password) || $username === '' || $password === '') {
        $error = 'Gebruikersnaam of wachtwoord is onjuist.';
    } else {
        $pdo = getDatabaseConnection();
        $statement = dbQuery(
            $pdo,
            'SELECT id, password_hash FROM admins WHERE gebruikersnaam = ?',
            [$username]
        );
        $admin = $statement->fetch();

        if ($admin && password_verify($password, $admin['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = (int) $admin['id'];
            unset($_SESSION['csrf_token']);
            header('Location: dashboard.php');
            exit;
        }

        $error = 'Gebruikersnaam of wachtwoord is onjuist.';
    }
}

$pageTitle = 'Inloggen';
require __DIR__ . '/includes/header.php';
?>
<main>
    <section class="card" style="max-width: 460px; margin: 0 auto;">
        <h1>Beheerder inloggen</h1>
        <?php if ($error !== ''): ?>
            <p class="error" role="alert"><?= escapeHtml($error) ?></p>
        <?php endif; ?>
        <form method="post" action="login.php">
            <input type="hidden" name="csrf_token" value="<?= escapeHtml(csrfToken()) ?>">
            <div class="field">
                <label for="gebruikersnaam">Gebruikersnaam</label>
                <input id="gebruikersnaam" name="gebruikersnaam" autocomplete="username"
                    value="<?= escapeHtml($username) ?>" required>
            </div>
            <div class="field">
                <label for="wachtwoord">Wachtwoord</label>
                <input id="wachtwoord" name="wachtwoord" type="password"
                    autocomplete="current-password" required>
            </div>
            <button type="submit">Inloggen</button>
        </form>
    </section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
