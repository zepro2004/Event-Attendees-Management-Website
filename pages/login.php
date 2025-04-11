<?php
/**
 * User Login Page
 * 
 * This page provides the login interface for users to authenticate.
 * Features include:
 * - Username and password input fields
 * - Error message display for failed login attempts
 * - Form validation
 * - Link to registration page for new users
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../includes/header.php';

// Get username from session if available (form was previously submitted with errors)
// This preserves the username input after a failed login attempt
$username = isset($_SESSION['form_data']['username']) ? htmlspecialchars($_SESSION['form_data']['username']) : '';
?>

<div class="container">
    <h2>Uevent Login</h2>
    
    <!-- Display error messages if login failed -->
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="error-message"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
    <?php endif; ?>
    
    <!-- Login form - submits to form_handlers.php -->
    <form id="loginForm" action="../server/form_handlers.php" method="POST">
        <!-- Username input field -->
        <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" value="<?php echo $username; ?>">
        </div>
        
        <!-- Password input field -->
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password">
        </div>
        
        <!-- Hidden field to identify form action in form_handlers.php -->
        <input type="hidden" name="action" value="login">
        <button type="submit" class="btn">Login</button>
    </form>
    
    <!-- Link to registration page for new users -->
    <div class="form-footer">
        <a href="register.php">Don't have an account? Register here</a>
    </div>
</div>

<!-- Include client-side validation script -->
<script src="../scripts/validation.js"></script>
<?php include '../includes/footer.php'; ?>