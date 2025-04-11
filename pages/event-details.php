<?php
/**
 * Event Details Page
 * 
 * This page displays detailed information about a specific event.
 * Features include:
 * - Basic event information (title, date, location)
 * - Event description and image
 * - RSVP functionality for authenticated users
 * - Event management options for event organizers
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../includes/header.php';
include '../database/db_connect.php';

// Validate ID parameter - ensure it exists and is numeric
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<p>Invalid event ID.</p>";
    include '../includes/footer.php';
    exit;
}

// Convert the ID to integer for security
$eventId = intval($_GET['id']);

// Fetch event details with joins for organizer name and event type
$stmt = $mysqli->prepare("SELECT e.*, u.username as organizer_name, et.name as event_type 
                      FROM events e 
                      LEFT JOIN users u ON e.user_id = u.id
                      LEFT JOIN event_types et ON e.event_type_id = et.id
                      WHERE e.id = ?");
$stmt->bind_param("i", $eventId);
$stmt->execute();
$result = $stmt->get_result();
$event = $result->fetch_assoc();
$stmt->close();

// If no event found with this ID, display error and exit
if (!$event) {
    echo "<p>Event not found.</p>";
    include '../includes/footer.php';
    exit;
}

// Check user's RSVP status if they are logged in
$userRsvpStatus = null;
if (isset($_SESSION['user_id'])) {
    $rsvpStmt = $mysqli->prepare("SELECT status FROM rsvps WHERE event_id = ? AND user_id = ?");
    $rsvpStmt->bind_param("ii", $eventId, $_SESSION['user_id']);
    $rsvpStmt->execute();
    $result = $rsvpStmt->get_result();
    $rsvpResult = $result->fetch_assoc();
    $rsvpStmt->close();
    $userRsvpStatus = $rsvpResult ? $rsvpResult['status'] : null;
}

// Format date and location for display
$eventDate = new DateTime($event['date']);
$formattedDate = $eventDate->format('F j, Y, g:i a');
$location = implode(', ', array_filter([$event['city'], $event['state'], $event['country']]));
?>

<div class="container">
    <div class="event-details" data-event-id="<?php echo $eventId; ?>">
        <!-- Event title -->
        <h1><?php echo htmlspecialchars($event['title']); ?></h1>
        
        <!-- Event image (if available) -->
        <?php if (!empty($event['image_url'])): ?>
            <div class="event-image">
                <img src="<?php echo htmlspecialchars($event['image_url']); ?>" alt="<?php echo htmlspecialchars($event['title']); ?>">
            </div>
        <?php endif; ?>
        
        <!-- Basic event information -->
        <div class="event-info">
            <p><strong>Date:</strong> <?php echo $formattedDate; ?></p>
            <p><strong>Location:</strong> <?php echo htmlspecialchars($location); ?></p>
            <p><strong>Organizer:</strong> <?php echo htmlspecialchars($event['organizer_name']); ?></p>
            <?php if (!empty($event['event_type'])): ?>
                <p><strong>Type:</strong> <?php echo htmlspecialchars($event['event_type']); ?></p>
            <?php endif; ?>
        </div>
        
        <!-- Event description -->
        <div class="event-description">
            <h2>Description</h2>
            <p><?php echo nl2br(htmlspecialchars($event['description'])); ?></p>
        </div>

        <?php if (isset($_SESSION['user_id'])): ?>
            <!-- RSVP section for logged-in users -->
            <div class="rsvp-section">
                <h2>RSVP</h2>
                <?php if (isset($_GET['rsvp']) && $_GET['rsvp'] == 'success'): ?>
                    <!-- Success message after RSVP submission -->
                    <div class="success-message">
                        <p>Your RSVP has been recorded!</p>
                    </div>
                <?php endif; ?>
                
                <!-- RSVP form with current status pre-selected if available -->
                <form id="rsvp-form" action="../server/event_handlers.php" method="POST">
                    <input type="hidden" name="event_id" value="<?php echo $eventId; ?>">
                    <input type="hidden" name="action" value="rsvp">
                    <div class="form-group">
                        <select id="status" name="status" required>
                            <option value="attending" <?php echo $userRsvpStatus == 'attending' ? 'selected' : ''; ?>>Attending</option>
                            <option value="maybe" <?php echo $userRsvpStatus == 'maybe' ? 'selected' : ''; ?>>Maybe</option>
                            <option value="not_attending" <?php echo $userRsvpStatus == 'not_attending' ? 'selected' : ''; ?>>Not Attending</option>
                        </select>
                        <!-- Button text changes based on whether updating or submitting new RSVP -->
                        <button type="submit" class="btn"><?php echo $userRsvpStatus ? 'Update RSVP' : 'Submit RSVP'; ?></button>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <!-- Login prompt for non-authenticated users -->
            <div class="login-prompt">
                <p>Please <a href="login.php">log in</a> to RSVP for this event.</p>
            </div>
        <?php endif; ?>
        
        <!-- Action buttons -->
        <div class="event-actions">
            <a href="events.php" class="btn">Back to Events</a>
            <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $event['user_id']): ?>
                <!-- Delete button only shown to event owner -->
                <button id="delete-event" class="btn delete" onclick="deleteEvent(<?php echo $eventId; ?>)">Delete Event</button>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Include JavaScript for event interactions -->
<script src="../scripts/events.js"></script>
<?php include '../includes/footer.php'; ?>