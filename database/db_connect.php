<?php
// Database connection file
$host = "localhost";
$dbname = "assignment2";
$username = "root"; 
$password = ""; 

// Create MySQLi connection
$mysqli = new mysqli($host, $username, $password, $dbname);

// Check for connection errors
if ($mysqli->connect_error) {
    error_log("Database connection error: " . $mysqli->connect_error);
    die("A database connection error occurred. Please try again later.");
}

// Set charset to ensure proper encoding
$mysqli->set_charset("utf8mb4");