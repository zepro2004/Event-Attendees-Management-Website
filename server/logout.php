<?php
// Include auth file for logout function
require_once 'auth.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is actually logged in
if (isLoggedIn()) {
    logoutUser();
    setcookie("logout_message", "You have been successfully logged out.", time() + 60, "/");
}

// Redirect to homepage
header("Location: ../index.php");
exit;
