<?php

echo 'test';

require __DIR__ . '../../vendor/autoload.php';

function getDb(): PDO
{
  $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
  $dotenv->load();

  $dbDsn = $_ENV['DB_DSN'];
  $dbUser = $_ENV['DB_USER'];
  $dbPassword = $_ENV['DB_PASSWORD'];

  $db = new PDO(
    $dbDsn,
    $dbUser,
    $dbPassword,
    [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]
  );
  return $db;
}
