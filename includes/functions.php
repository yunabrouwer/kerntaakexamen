<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    ini_set('session.use_strict_mode', '1');
    session_set_cookie_params([
        'httponly' => true,
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'samesite' => 'Lax',
    ]);
    session_start();
}

function escapeHtml(mixed $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function csrfToken(): string
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function isValidCsrfToken(mixed $token): bool
{
    return is_string($token)
        && isset($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

function getActiveEstablishments(): array
{
    $statement = database()->query(
        'SELECT id, naam FROM vestigingen WHERE actief = 1 ORDER BY naam'
    );

    return $statement->fetchAll();
}

function getActiveEstablishment(?int $id): ?array
{
    if ($id !== null) {
        $statement = database()->prepare(
            'SELECT id, naam FROM vestigingen WHERE id = :id AND actief = 1'
        );
        $statement->execute(['id' => $id]);
    } else {
        return null;
    }

    $establishment = $statement->fetch();

    return $establishment ?: null;
}

function createFeedback(int $establishmentId, int $score, string $category, string $description, string $token): void
{
    try {
        $statement = database()->prepare(
            'INSERT INTO feedback (vestiging_id, score, categorie, toelichting, submission_token)
             SELECT v.id, :score, :categorie, :beschrijving, :submission_token
             FROM vestigingen v
             WHERE v.id = :vestiging_id AND v.actief = 1'
        );
        $statement->execute([
            'score' => $score,
            'categorie' => $category,
            'beschrijving' => $description,
            'submission_token' => hash('sha256', $token),
            'vestiging_id' => $establishmentId,
        ]);

        if ($statement->rowCount() !== 1) {
            throw new RuntimeException('De vestiging is niet meer beschikbaar.');
        }
    } catch (PDOException $exception) {
        $driverCode = $exception->errorInfo[1] ?? null;
        if ($exception->getCode() === '23000' && $driverCode === 1062) {
            return;
        }

        throw $exception;
    }
}

function findManager(string $email): ?array
{
    $statement = database()->prepare(
        'SELECT a.id, a.email, a.wachtwoord_hash, a.actief,
                v.id AS vestiging_id, v.naam AS vestiging_naam, v.actief AS vestiging_actief
         FROM admins a
         INNER JOIN vestigingen v ON v.id = a.vestiging_id
         WHERE a.email = :email
         LIMIT 1'
    );
    $statement->execute(['email' => $email]);
    $manager = $statement->fetch();

    return $manager ?: null;
}

function currentManager(): ?array
{
    $adminId = filter_var($_SESSION['manager_id'] ?? null, FILTER_VALIDATE_INT);
    $lastActivity = filter_var($_SESSION['manager_last_activity'] ?? null, FILTER_VALIDATE_INT);

    if (!$adminId) {
        return null;
    }
    if (!$lastActivity || time() - $lastActivity > 1800) {
        $_SESSION['manager_session_expired'] = true;
        clearManagerSession();
        return null;
    }

    $statement = database()->prepare(
        'SELECT a.id, a.email, a.vestiging_id, v.naam AS vestiging_naam
         FROM admins a
         INNER JOIN vestigingen v ON v.id = a.vestiging_id
         WHERE a.id = :id AND a.actief = 1 AND v.actief = 1
         LIMIT 1'
    );
    $statement->execute(['id' => $adminId]);
    $manager = $statement->fetch();

    if (!$manager) {
        clearManagerSession();
        return null;
    }

    $_SESSION['manager_last_activity'] = time();

    return $manager;
}

function clearManagerSession(): void
{
    unset($_SESSION['manager_id'], $_SESSION['manager_last_activity']);
    session_regenerate_id(true);
}

function getManagerFeedback(int $establishmentId, string $filter): array
{
    $where = 'WHERE vestiging_id = :vestiging_id';
    if ($filter === 'negative') {
        $where .= ' AND score <= 2';
    } elseif ($filter === 'open') {
        $where .= " AND status = 'Nieuw'";
    }

    $statement = database()->prepare(
        "SELECT id, score, categorie, toelichting AS beschrijving, status, aangemaakt_op
         FROM feedback $where
         ORDER BY aangemaakt_op DESC"
    );
    $statement->execute(['vestiging_id' => $establishmentId]);

    return $statement->fetchAll();
}

function getManagerAverageScore(int $establishmentId): string
{
    $statement = database()->prepare(
        'SELECT AVG(score) FROM feedback WHERE vestiging_id = :vestiging_id'
    );
    $statement->execute(['vestiging_id' => $establishmentId]);
    $average = $statement->fetchColumn();

    return $average === null ? '0.0' : number_format((float)$average, 1);
}

function getManagerFeedbackItem(int $feedbackId, int $establishmentId): ?array
{
    $statement = database()->prepare(
        'SELECT f.id, f.score, f.categorie, f.toelichting AS beschrijving, f.status, f.aangemaakt_op,
                v.naam AS vestiging_naam
         FROM feedback f
         INNER JOIN vestigingen v ON v.id = f.vestiging_id
         WHERE f.id = :id AND f.vestiging_id = :vestiging_id
         LIMIT 1'
    );
    $statement->execute([
        'id' => $feedbackId,
        'vestiging_id' => $establishmentId,
    ]);
    $item = $statement->fetch();

    return $item ?: null;
}

function updateFeedbackStatus(int $feedbackId, int $establishmentId, string $status): bool
{
    $statement = database()->prepare(
        'UPDATE feedback
         SET status = :status
         WHERE id = :id AND vestiging_id = :vestiging_id'
    );
    $statement->execute([
        'status' => $status,
        'id' => $feedbackId,
        'vestiging_id' => $establishmentId,
    ]);

    return $statement->rowCount() > 0 || getManagerFeedbackItem($feedbackId, $establishmentId) !== null;
}
