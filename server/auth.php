<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$rootPath = dirname(__DIR__);
require_once $rootPath . '/database/db_connect.php';

function isUsernameTaken($pdo, $username) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
    $stmt->execute([$username]);
    return (int)$stmt->fetchColumn() > 0;
}

function registerUser($pdo, $username, $password, $email, $firstName, $lastName) {
    if (isUsernameTaken($pdo, $username)) {
        return false; // Username already exists
    }
    
    try {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
        $stmt = $pdo->prepare("INSERT INTO users (username, password, email, first_name, last_name) VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([$username, $hashedPassword, $email, $firstName, $lastName]);
    } catch (PDOException $e) {
        error_log('Registration error: ' . $e->getMessage());
        return false;
    }
}

function loginUser($pdo, $username, $password) {
    try {
        $stmt = $pdo->prepare("SELECT id, password, first_name, last_name, email FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            // Store user data in session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $username;
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['email'] = $user['email'];
            
            // Update last login timestamp
            updateLastLogin($pdo, $user['id']);
            
            return true;
        }
        return false;

    } catch (PDOException $e) {
        error_log('Login error: ' . $e->getMessage());
        return false;
    }
}

function logoutUser() {
    session_unset();
    session_destroy();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function updateLastLogin($pdo, $userId) {
    try {
        $stmt = $pdo->prepare("UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = ?");
        return $stmt->execute([$userId]);
    } catch (PDOException $e) {
        error_log('Update last login error: ' . $e->getMessage());
        return false;
    }
}