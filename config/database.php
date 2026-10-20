<?php
// Zorg dat foutmeldingen aanstaan voor het testen
ini_set('display_errors', 1);
error_reporting(E_ALL);

$host = 'localhost';
$db   = 'tabletalk'; // VERANDER DIT als je database in phpMyAdmin anders heet!
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    // Maak de databaseverbinding aan via PDO
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // Toon een nette foutmelding als de verbinding mislukt
    die("Database verbinding mislukt: " . $e->getMessage());
}
