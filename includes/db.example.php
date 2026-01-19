<?php
// includes/db.example.php
// Database connection for Inventory Project
// Copy this file to db.php and fill in your actual database credentials

$host = 'localhost';        // Database host (usually localhost)
$db   = 'your_database_name';     // database name
$user = 'your_db_username';             // DB username
$pass = 'your_db_password';                 // DB password
$charset = 'utf8mb4';       // Charset for proper encoding

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Throw exceptions on errors
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Fetch associative arrays
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Use real prepared statements
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    // If connection fails, show error (for dev only; in production, log instead)
    die("Database connection failed: " . $e->getMessage());
}
?>