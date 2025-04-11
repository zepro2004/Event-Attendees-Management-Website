<?php
/**
 * User Registration Page
 * 
 * This page allows new users to create an account in the system.
 * Features include:
 * - Form fields for username, name, email, and password
 * - Form validation (both client and server-side)
 * - Error message display for failed registration attempts
 * - Password confirmation field for security
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../includes/header.php';
?>

<div class="container">
    <h2>User Registration</h2>
    
    <!-- Display validation errors if registration failed -->
    <?php if (isset($_SESSION['registration_errors']) && !empty($_SESSION['registration_errors'])): ?>
        <div class="error-container">
            <?php foreach($_SESSION['registration_errors'] as $error): ?>
                <p class="error"><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>
        <?php unset($_SESSION['registration_errors']); ?>
    <?php endif; ?>
    
    <!-- Registration form - submits to form_handlers.php -->
    <form id="registrationForm" action="../server/form_handlers.php" method="POST">
        <!-- Username field -->
        <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username">
        </div>
        
        <!-- First and last name fields in a row -->
        <div class="form-row">
            <div class="form-group">
                <label for="first_name">First Name:</label>
                <input type="text" id="first_name" name="first_name" >
            </div>
            <div class="form-group">
                <label for="last_name">Last Name:</label>
                <input type="text" id="last_name" name="last_name">
            </div>
        </div>
        
        <!-- Email field -->
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email">
        </div>
        
        <!-- Password and confirmation fields in a row -->
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
        
        <!-- Hidden field to identify form action in form_handlers.php -->
        <input type="hidden" name="action" value="register">
        <button type="submit" class="btn">Register</button>
    </form>
    
    <!-- Link to login page for existing users -->
    <div class="form-footer">
        <p>Already have an account? <a href="login.php">Login here</a></p>
    </div>
</div>

<!-- Include client-side validation script -->
<script src="../scripts/validation.js"></script>
<?php include '../includes/footer.php'; ?>