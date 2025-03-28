<?php 
require_once $_SERVER['DOCUMENT_ROOT'] . '/Assignment2/server/auth.php'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Planning System</title>
    <link rel="stylesheet" href="/Assignment2/assets/styles/styles.css">
</head>
<body>
    
<header>
    <div class="container">
        <div class="logo">
            <h1>Welcome to the Event Planning System</h1>
        </div>
        <nav class="navigation">
            <ul class="nav-list">
                <li><a href="/Assignment2/index.php">Home</a></li>
                <li><a href="/Assignment2/pages/events.php">Events</a></li>
                <?php if (!isLoggedIn()): ?>
                    <li><a href="/Assignment2/pages/register.php">Register</a></li>
                    <li><a href="/Assignment2/pages/login.php">Login</a></li>
                <?php else: ?>
                    <li><a href="/Assignment2/pages/dashboard.php">Dashboard</a></li>
                    <li><a href="/Assignment2/server/logout.php">Logout</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</header>

    