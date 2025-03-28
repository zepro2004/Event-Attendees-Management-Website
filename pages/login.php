<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log In</title>
    <link rel="stylesheet" href="../assets/styles/styles.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="container">
        <h2>Uevent Login</h2>
        <form id="loginForm" action="../server/form_handlers.php" method="POST">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" 
                value="<?php echo isset($_SESSION['form_data']['username']) ? 
                htmlspecialchars($_SESSION['form_data']['username']) : ''; ?>">
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password">
            </div>
            <input type="hidden" name="action" value="login">
            <button type="submit" class="btn">Login</button>
        </form>
        <a href="register.php">Don't have an account? Register here</a>
    </div>

    <script src="../scripts/validation.js"></script>
    <?php include '../includes/footer.php'; ?>
</body>
</html>