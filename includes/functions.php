<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// XSS-beveiliging: Maakt invoer van gasten veilig voor weergave
function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// Genereer een CSRF-token tegen formulier-hacking
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Controleer of het meegestuurde CSRF-token klopt
function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Bereken de gemiddelde score van een vestiging (afgerond op 1 decimaal)
function get_average_score($pdo, $vestiging_id) {
    $stmt = $pdo->prepare("SELECT AVG(score) as average FROM feedback WHERE vestiging_id = ?");
    $stmt->execute([$vestiging_id]);
    $result = $stmt->fetch();
    return $result['average'] ? number_format($result['average'], 1) : '0.0';
}
