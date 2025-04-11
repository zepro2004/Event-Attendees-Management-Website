<?php
/**
 * Form Handler Script
 * 
 * This file processes all authentication-related form submissions including:
 * - User registration
 * - Login verification
 * - Username availability checking (AJAX)
 * 
 * All form submissions are validated and appropriate responses/redirects
 * are returned based on success or failure.
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Include required authentication functions and database connection
require_once 'auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Assignment2/database/db_connect.php';

/**
 * AJAX Username Availability Check
 * 
 * This endpoint allows the registration form to check if a username
 * is already taken without submitting the entire form.
 */
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['check_username'])) {
  $username = $_GET['username'] ?? '';
  
  // Return JSON response for client-side processing
  header('Content-Type: application/json');
  echo json_encode(['taken' => isUsernameTaken($mysqli, $username)]);
  exit;
}

/**
 * Form Submission Router
 * 
 * Routes POST requests to the appropriate handler function based on
 * the 'action' parameter included in the form submission.
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
  switch ($_POST['action']) {
    case 'register':
      handleRegistration($mysqli);
      break;
    case 'login':
      handleLogin($mysqli);
      break;
    default:
      // Unknown action, redirect to homepage
      header("Location: ../index.php");
      exit;
  }
}

// Redirect to homepage if no valid action or direct script access
header("Location: ../index.php");
exit;

/**
 * Registration Form Handler
 * 
 * Processes user registration submissions with the following steps:
 * 1. Validates username availability
 * 2. Attempts to create a new user account
 * 3. Automatically logs in the new user on success
 * 
 * @param mysqli $mysqli Database connection object
 */
function handleRegistration($mysqli) {
  $errors = [];
  $username = $_POST['username'] ?? '';
  
  // Check if username is already taken
  if (isUsernameTaken($mysqli, $username)) {
    $errors['username'] = "Username is already taken!";
  }
  
  // If no validation errors, attempt to create the account
  if (empty($errors)) {
    $password = $_POST['password'] ?? '';
    $email = $_POST['email'] ?? '';
    $firstName = $_POST['first_name'] ?? '';
    $lastName = $_POST['last_name'] ?? '';

    // Register user and automatically log them in if successful
    if (registerUser($mysqli, $username, $password, $email, $firstName, $lastName)) {
      // Clear any stored form data from session
      unset($_SESSION['form_data']);
      
      // Auto-login the new user
      loginUser($mysqli, $username, $password);
      
      // Redirect to homepage with welcome message
      header("Location: ../index.php?welcome=1");
      exit;
    } else {
      // Database error during registration
      $errors['database'] = "Registration failed. Username or email may already exist.";
    }
  }
  
  // If we reached here, there were validation errors or registration failed
  // Store errors and form data in session for displaying on the form
  $_SESSION['registration_errors'] = $errors;
  $_SESSION['form_data'] = $_POST;
  
  // Redirect back to registration form
  header("Location: ../pages/register.php");
  exit;
}

/**
 * Login Form Handler
 * 
 * Processes login attempts with the following steps:
 * 1. Extracts credentials from the POST data
 * 2. Attempts to authenticate the user
 * 3. Creates a session or returns an error based on result
 * 
 * @param mysqli $mysqli Database connection object
 */
function handleLogin($mysqli) {
  $username = $_POST['username'] ?? '';
  $password = $_POST['password'] ?? '';

  // Debug code (remove after fixing)
  error_log("Login attempt: $username");
  
  // Attempt to authenticate user
  if (loginUser($mysqli, $username, $password)) {
    // Clear any stored form data and errors from session
    unset($_SESSION['form_data'], $_SESSION['error_message']);
    
    // Redirect to homepage upon successful login
    header("Location: ../index.php");
  } else {
    // Store error and username for redisplay on the form
    $_SESSION['error_message'] = "Invalid username or password";
    $_SESSION['form_data'] = ['username' => $username];
    
    // Redirect back to login form
    header("Location: ../pages/login.php");
  }
  exit;
}