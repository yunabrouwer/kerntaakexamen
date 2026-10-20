<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

<<<<<<< HEAD
// Veiligheid: Voorkomt XSS (hacken via invoervelden)
function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// Haal alle feedback op uit het tekstbestand
function get_all_feedback() {
    $file = __DIR__ . '/../feedback.txt';
    if (!file_exists($file)) {
        return [];
    }
    $content = file_get_contents($file);
    return json_decode($content, true) ?? [];
}

// Sla een nieuw feedback-item op in het tekstbestand
function save_feedback($score, $categorie, $toelichting, $vestiging_id) {
    $file = __DIR__ . '/../feedback.txt';
    $all_feedback = get_all_feedback();

    $new_item = [
        'id' => time() . rand(100, 999), // Uniek ID
        'vestiging_id' => (int)$vestiging_id,
        'score' => (int)$score,
        'categorie' => $categorie,
        'toelichting' => $toelichting,
        'status' => 'Nieuw',
        'aangemaakt_op' => date('Y-m-d H:i:s')
    ];

    array_unshift($all_feedback, $new_item); // Zet de nieuwste bovenaan
    file_put_contents($file, json_encode($all_feedback, JSON_PRETTY_PRINT));
}

// Bereken het gemiddelde (afgerond op 1 decimaal)
function get_average_score() {
    $all = get_all_feedback();
    if (empty($all)) return '0.0';
    
    $total = 0;
    foreach ($all as $item) {
        $total += $item['score'];
    }
    return number_format($total / count($all), 1);
=======
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
>>>>>>> 4d3db418d8247204f7d5c557ce917929eb3610a2
}
