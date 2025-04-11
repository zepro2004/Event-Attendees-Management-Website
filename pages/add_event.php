<?php
/**
 * Event Creation Page
 * 
 * This page allows authenticated users to create new events in the system.
 * It includes a form for entering event details such as title, date, location,
 * description, and uploading an event image.
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Authentication check - redirect unauthenticated users to login page
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
            <!-- Event creation form - submits to event_handlers.php -->
            <form id="addEventForm" class="event-form" action="../server/event_handlers.php" method="POST" enctype="multipart/form-data">
                <!-- Hidden input for form action identification -->
                <input type="hidden" name="action" value="add">    
                
                <!-- Basic event information section -->
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
                
                <!-- Additional timing and capacity section -->
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
                
                <!-- Event location section -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="address">Address:</label>
                        <input type="text" id="address" name="address" required>
                    </div>
                </div>
                
                <!-- City and state/province section -->
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
                
                <!-- Postal code and country section -->
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
                
                <!-- Event description section -->
                <div class="form-group">
                    <label for="description">Description:</label>
                    <textarea id="description" name="description" rows="4"></textarea>
                </div>
                
                <!-- Event image upload section -->
                <div class="form-group">
                    <label for="event_image">Event Image</label>
                    <input type="file" id="event_image" name="event_image" accept="image/*">
                    <small>Recommended size: 800x400px. Maximum file size: 5MB.</small>
                </div>
                
                <!-- Form submission section -->
                <input type="hidden" name="action" value="add">
                <button type="submit" class="btn">Create Event</button>
            </form>
        </div>
    </div>

    <!-- Include client-side validation and event handling scripts -->
    <script src="../scripts/events.js"></script>
    <?php include '../includes/footer.php'; ?>
</body>
</html>