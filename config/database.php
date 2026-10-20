<?php
declare(strict_types=1);

function getDatabaseConnection(): PDO
{
    $host = getenv('TABLETALK_DB_HOST') ?: '127.0.0.1';
    $database = getenv('TABLETALK_DB_NAME') ?: 'tabletalk';
    $username = getenv('TABLETALK_DB_USER') ?: 'root';
    $password = getenv('TABLETALK_DB_PASSWORD') ?: '';
    $dsn = "mysql:host={$host};dbname={$database};charset=utf8mb4";

    try {
        return new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    } catch (PDOException $exception) {
        error_log('TableTalk database connection failed: ' . $exception->getMessage());
        http_response_code(500);
        exit('De database is tijdelijk niet beschikbaar.');
    }
}
