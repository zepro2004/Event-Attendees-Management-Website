<?php
/**
 * Authentication System
 * 
 * This file manages user authentication functionality including:
 * - User registration
 * - Login verification
 * - Session management
 * - Logout handling
 * 
 * Security features:
 * - Password hashing using PHP's password_hash
 * - Input validation through prepared statements
 * - Session-based authentication
 */

// Start session if not already started
// This is required for maintaining user login state across pages
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get the root path of the application for including other files
$rootPath = dirname(__DIR__);

// Include database connection
require_once $rootPath . '/database/db_connect.php';

/**
 * Checks if a username already exists in the database
 * Prevents duplicate usernames during registration
 * 
 * @param mysqli $mysqli Database connection object
 * @param string $username Username to check
 * @return bool True if username exists, false otherwise
 */
function isUsernameTaken($mysqli, $username) {
    // Prepare a secure query using parameterized statement to prevent SQL injection
    $stmt = $mysqli->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();
    
    // Return true if any matching records found
    return $count > 0;
}

/**
 * Registers a new user in the database
 * Handles input validation, password hashing, and database insertion
 * 
 * @param mysqli $mysqli Database connection object
 * @param string $username User's username
 * @param string $password User's password (will be hashed)
 * @param string $email User's email address
 * @param string $firstName User's first name
 * @param string $lastName User's last name
 * @return bool True on successful registration, false if username exists or insertion fails
 */
function registerUser($mysqli, $username, $password, $email, $firstName, $lastName) {
    // Check if username already exists before attempting to register
    if (isUsernameTaken($mysqli, $username)) return false;
    
    // Hash password for security
    // Using PASSWORD_DEFAULT ensures the best available hashing algorithm is used
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert new user record with prepared statement to prevent SQL injection
    $stmt = $mysqli->prepare("INSERT INTO users (username, password, email, first_name, last_name) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $username, $hashedPassword, $email, $firstName, $lastName);
    $result = $stmt->execute();
    $stmt->close();
    
    // Return result of database operation (true on success, false on failure)
    return $result;
}

/**
 * Authenticates a user and creates a session if successful
 * Verifies credentials and establishes a login session
 * 
 * @param mysqli $mysqli Database connection object
 * @param string $username User's username
 * @param string $password User's password (plain text for verification)
 * @return bool True on successful login, false otherwise
 */
function loginUser($mysqli, $username, $password) {
    // Retrieve user data based on username
    $stmt = $mysqli->prepare("SELECT id, password, first_name, email FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // If user exists in database
    if ($user = $result->fetch_assoc()) {
        // Verify password against stored hash using secure PHP function
        // This prevents timing attacks and safely compares hashed passwords
        if (password_verify($password, $user['password'])) {
            // Store essential user data in session for use throughout the application
            // This creates a persistent login state
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $username;
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['email'] = $user['email'];
            
            // Update last login timestamp for user activity tracking
            updateLastLogin($mysqli, $user['id']);
            return true;
        }
    }
    
    // Authentication failed
    return false;
}

/**
 * Destroys the current user session
 * Removes all session data to log out the user
 */
function logoutUser() {
    // Remove all session variables
    session_unset();
    
    // Destroy the session completely
    session_destroy();
    
    // Note: Cookies should be handled separately if used
}

/**
 * Checks if a user is currently logged in
 * Used for access control throughout the application
 * 
 * @return bool True if user is logged in, false otherwise
 */
function isLoggedIn() {
    // Check if user_id exists in session
    // This is the primary indicator of an active login session
    return isset($_SESSION['user_id']);
}

/**
 * Updates the last login timestamp for a user
 * Tracks user login activity for security and analytics
 * 
 * @param mysqli $mysqli Database connection object
 * @param int $userId User ID
 * @return bool True on successful update, false otherwise
 */
function updateLastLogin($mysqli, $userId) {
    // Update database with current timestamp
    $stmt = $mysqli->prepare("UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $result = $stmt->execute();
    $stmt->close();
    return $result;
}