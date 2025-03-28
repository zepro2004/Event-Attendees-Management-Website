<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

require_once 'auth.php';

// Handle form submissions

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['check_username'])) {
  $username = $_GET['username'] ?? '';
  $taken = isUsernameTaken($pdo, $username);
  
  header('Content-Type: application/json');
  echo json_encode(['taken' => $taken]);
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'register':
              handleRegistration($pdo);
              break;
                
            case 'login':
              handleLogin($pdo);
              break;
                
            default:
                // Unknown action
                header("Location: ../index.php");
                exit;
        }
    } else {
        // No action specified
        header("Location: ../index.php");
        exit;
    }
}

function handleRegistration($pdo) {
  global $pdo;
  $errors = [];
                
  if (isUsernameTaken($pdo, $_POST['username'])) {
    $errors['username'] = "Username is already taken!";
  }
  
  if (empty($errors)) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $email = $_POST['email'];
    $firstName = $_POST['first_name'];
    $lastName = $_POST['last_name'];

    if (registerUser($pdo, $_POST['username'], $_POST['password'], $_POST['email'], $_POST['first_name'], $_POST['last_name'])) {
      if (isset($_SESSION['form_data'])) {
        unset($_SESSION['form_data']);
      }  
      loginUser($pdo, $username, $password);
      header("Location: ../index.php?welcome=1");
      exit;
    } else {
        $errors['database'] = "Registration failed. Username or email may already exist.";
    }
  }
  
  // If we get here, there were errors
  $_SESSION['registration_errors'] = $errors;
  $_SESSION['form_data'] = $_POST; // Save form data for repopulating the form
  header("Location: ../pages/register.php");
  exit;  
}

function handleLogin($pdo) {
  $username = $_POST['username'] ?? '';
  $password = $_POST['password'] ?? '';
  
  if (loginUser($pdo, $username, $password)) {
    if (isset($_SESSION['form_data'])) {
      unset($_SESSION['form_data']);
    }
    if (isset($_SESSION['login_error'])) {
      unset($_SESSION['login_error']);
    }
    header("Location: ../index.php");
  } else {
      $_SESSION['login_error'] = "Invalid username or password";
      $_SESSION['form_data'] = ['username' => $username];
      header("Location: ../pages/login.php");
  }
  exit;  
}