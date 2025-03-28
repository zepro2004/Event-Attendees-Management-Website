<?php
// Database connection file
$dsn = "mysql:host=localhost;dbname=assignment2";
$username = "root"; // Database username
$password = ""; // Database password

try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Log error privately
    error_log("Database connection error: " . $e->getMessage());
    // Show generic error
    die("A database connection error occurred. Please try again later.");
}