<?php
// ============================================================
// db_connect.php
// Handles the MySQL database connection for all PHP scripts
// ============================================================

$host     = 'localhost';
$dbname   = 'hospital_system';
$username = 'root';
$password = 'mysql';  // Default AMPPS password is empty

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    // Never expose raw errors to the browser in production
    die(json_encode(['success' => false, 'message' => 'Database connection failed.']));
}
?>