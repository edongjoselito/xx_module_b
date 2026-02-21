<?php
// db.php - database connection (PDO)

$dbHost = '127.0.0.1';
$dbName = 'xx_module_b';
$dbUser = 'root';
$dbPass = 'moth34board';
$dbCharset = 'utf8mb4';

$dsn = "mysql:host=$dbHost;dbname=$dbName;charset=$dbCharset";

try {
    $pdo = new PDO($dsn, $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}