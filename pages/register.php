<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create an account</title>
    <link rel="stylesheet" href="../assets/styles/styles.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>

<div class="container">
    <h2>User Registration</h2>
    
    <?php if (isset($_SESSION['registration_errors']) && !empty($_SESSION['registration_errors'])): ?>
        <div class="error-container">
            <p class="error"><?php echo htmlspecialchars($_SESSION['registration_errors']['username'] ?? ''); ?></p>
        </div>
        <?php unset($_SESSION['registration_errors']); ?>
    <?php endif; ?>
    <form id="registrationForm" action="../server/form_handlers.php" method="POST" novalidate>
    <div class="form-group">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username">
    </div>
    
    <div class="form-row">
        <div class="form-group">
            <label for="first_name">First Name:</label>
            <input type="text" id="first_name" name="first_name">
        </div>
        <div class="form-group">
            <label for="last_name">Last Name:</label>
            <input type="text" id="last_name" name="last_name">
        </div>
    </div>
    
    <div class="form-group">
        <label for="email">Email:</label>
        <input type="email" id="email" name="email">
    </div>
    
    <div class="form-row">
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password">
        </div>
        <div class="form-group">
            <label for="confirmPassword">Confirm Password:</label>
            <input type="password" id="confirmPassword" name="confirmPassword">
        </div>
    </div>
    
    <input type="hidden" name="action" value="register">
    <button type="submit" class="btn">Register</button>
</form>
</div>

<script src="../scripts/validation.js"></script>

<?php
include('../includes/footer.php');
?>