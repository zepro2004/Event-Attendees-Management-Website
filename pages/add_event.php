<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=create-event');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Event | Event Planning System</title>
    <link rel="stylesheet" href="../assets/styles/styles.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="container">
        <div class="page-header">
            <h1>Create New Event</h1>
            <a href="dashboard.php" class="btn secondary">Back to Dashboard</a>
        </div>

        <div class="add-event-container">
            <form id="addEventForm" class="event-form" action="../server/events.php" method="POST">
                <input type="hidden" name="action" value="add">    
                <div class="form-row">
                    <div class="form-group">
                        <label for="title">Event Title:</label>
                        <input type="text" id="title" name="title" required>
                    </div>
                    <div class="form-group">
                        <label for="date">Date:</label>
                        <input type="datetime-local" id="date" name="date" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="end_date">End Date (Optional):</label>
                        <input type="datetime-local" id="end_date" name="end_date">
                    </div>
                    <div class="form-group">
                        <label for="max_attendees">Maximum Attendees:</label>
                        <input type="number" id="max_attendees" name="max_attendees" min="1">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="address">Address:</label>
                        <input type="text" id="address" name="address" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="city">City:</label>
                        <input type="text" id="city" name="city" required>
                    </div>
                    <div class="form-group">
                        <label for="state">State/Province:</label>
                        <input type="text" id="state" name="state">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="postal_code">Postal Code:</label>
                        <input type="text" id="postal_code" name="postal_code">
                    </div>
                    <div class="form-group">
                        <label for="country">Country:</label>
                        <input type="text" id="country" name="country" value="Canada">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="description">Description:</label>
                    <textarea id="description" name="description" rows="4"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="image_url">Image URL (Optional):</label>
                    <input type="url" id="image_url" name="image_url" placeholder="https://example.com/image.jpg">
                </div>
                
                <input type="hidden" name="action" value="add">
                <button type="submit" class="btn">Create Event</button>
            </form>
        </div>
    </div>

    <script src="../scripts/events.js"></script>
    <?php include '../includes/footer.php'; ?>
</body>
</html>