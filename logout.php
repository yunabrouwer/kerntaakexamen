<?php
require_once __DIR__ . '/includes/functions.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST' || !isValidCsrfToken($_POST['csrf_token'] ?? null)) {
    http_response_code(405);
    exit('Ongeldig verzoek.');
}

$_SESSION = [];
session_regenerate_id(true);
header('Location: login.php', true, 303);
exit;
