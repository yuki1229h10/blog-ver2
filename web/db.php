<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once('../vendor/autoload.php');

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

try {
  $dbDsn = $_ENV['DB_DSN'];
  $dbUser = $_ENV['DB_USER'];
  $dbPassword = $_ENV['DB_PASSWORD'];

  $pdo = new PDO(
    $dbDsn,
    $dbUser,
    $dbPassword,
    [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]
  );
} catch (PDOException $e) {
  header('Content-Type: text/plain; charset=UTF-8', true, 500);
  exit($e->getMessage());
}

header('Content-Type: text/html; charset=UTF-8');
