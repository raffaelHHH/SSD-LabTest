<?php

function getDb(): PDO
{
    static $pdo;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . getenv('DB_HOST') . ';dbname=' . getenv('DB_NAME') . ';charset=utf8mb4';
        $pdo = new PDO($dsn, getenv('DB_USER'), getenv('DB_PASSWORD'), [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
    }
    return $pdo;
}
