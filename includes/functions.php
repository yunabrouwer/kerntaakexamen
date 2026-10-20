<?php
declare(strict_types=1);

function startSecureSession(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $isSecure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    session_set_cookie_params([
        'httponly' => true,
        'secure' => $isSecure,
        'samesite' => 'Lax',
    ]);
    ini_set('session.use_strict_mode', '1');
    session_start();
}

function sanitizeInput(mixed $value): string
{
    if (!is_string($value)) {
        return '';
    }

    return trim(strip_tags($value));
}

function escapeHtml(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function csrfToken(): string
{
    if (!isset($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function validateCsrfToken(mixed $token): bool
{
    return is_string($token)
        && isset($_SESSION['csrf_token'])
        && is_string($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

function dbQuery(PDO $pdo, string $sql, array $parameters = []): PDOStatement
{
    try {
        $statement = $pdo->prepare($sql);
        $statement->execute($parameters);
        return $statement;
    } catch (PDOException $exception) {
        error_log('TableTalk database query failed: ' . $exception->getMessage());
        http_response_code(500);
        exit('Er is een fout opgetreden bij het verwerken van de aanvraag.');
    }
}

function calculateEstablishmentAverage(PDO $pdo, int $establishmentId): float
{
    $statement = dbQuery(
        $pdo,
        'SELECT AVG(score) AS average_score FROM feedback WHERE vestiging_id = ?',
        [$establishmentId]
    );
    $result = $statement->fetch();

    return isset($result['average_score']) ? (float) $result['average_score'] : 0.0;
}

function requireManager(PDO $pdo): array
{
    $adminId = filter_var($_SESSION['admin_id'] ?? null, FILTER_VALIDATE_INT);
    if ($adminId === false || $adminId === null || $adminId < 1) {
        header('Location: login.php');
        exit;
    }

    $statement = dbQuery(
        $pdo,
        'SELECT admins.id, admins.vestiging_id, admins.gebruikersnaam, vestigingen.naam AS vestiging_naam
         FROM admins
         INNER JOIN vestigingen ON vestigingen.id = admins.vestiging_id
         WHERE admins.id = ?',
        [$adminId]
    );
    $manager = $statement->fetch();

    if (!$manager) {
        unset($_SESSION['admin_id']);
        header('Location: login.php');
        exit;
    }

    return $manager;
}
