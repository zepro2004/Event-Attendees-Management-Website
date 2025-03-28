<?php
// event-details.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../includes/header.php';
include '../database/db_connect.php';

// Validate ID parameter
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<p>Invalid event ID.</p>";
    include '../includes/footer.php';
    exit;
}

$eventId = intval($_GET['id']);

// Fetch event details from database
try {
    $stmt = $pdo->prepare("SELECT e.*, u.username as organizer_name, et.name as event_type 
                          FROM events e 
                          LEFT JOIN users u ON e.user_id = u.id
                          LEFT JOIN event_types et ON e.event_type_id = et.id
                          WHERE e.id = ?");
    $stmt->execute([$eventId]);
    $eventDetails = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$eventDetails) {
        echo "<p>Event not found.</p>";
        include '../includes/footer.php';
        exit;
    }
} catch (PDOException $e) {
    echo "<p>Error retrieving event details: " . $e->getMessage() . "</p>";
    include '../includes/footer.php';
    exit;
}

$userRsvpStatus = null;
if (isset($_SESSION['user_id'])) {
    try {
        $rsvpStmt = $pdo->prepare("SELECT status FROM rsvps 
                                  WHERE event_id = ? AND user_id = ?");
        $rsvpStmt->execute([$eventId, $_SESSION['user_id']]);
        $rsvpResult = $rsvpStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($rsvpResult) {
            $userRsvpStatus = $rsvpResult['status'];
        }
    } catch (PDOException $e) {
        // Log error but don't display to user
        error_log("RSVP query error: " . $e->getMessage());
    }
}

// Format date
$eventDate = new DateTime($eventDetails['date']);
$formattedDate = $eventDate->format('F j, Y, g:i a');

// Build location string
$location = $eventDetails['address'];
if (!empty($eventDetails['city'])) {
    $location .= ', ' . $eventDetails['city'];
}
if (!empty($eventDetails['state'])) {
    $location .= ', ' . $eventDetails['state'];
}
if (!empty($eventDetails['country'])) {
    $location .= ', ' . $eventDetails['country'];
}
?>

<div class="container">
    <div class="event-details"  data-event-id="<?php echo $eventId; ?>">
        <h1><?php echo htmlspecialchars($eventDetails['title']); ?></h1>
        
        <?php if (!empty($eventDetails['image_url'])): ?>
        <div class="event-image">
            <img src="<?php echo htmlspecialchars($eventDetails['image_url']); ?>" alt="<?php echo htmlspecialchars($eventDetails['title']); ?>">
        </div>
        <?php endif; ?>
        
        <div class="event-info">
            <p><strong>Date:</strong> <?php echo $formattedDate; ?></p>
            <p><strong>Location:</strong> <?php echo htmlspecialchars($location); ?></p>
            <p><strong>Organizer:</strong> <?php echo htmlspecialchars($eventDetails['organizer_name']); ?></p>
            <?php if (!empty($eventDetails['event_type'])): ?>
            <p><strong>Event Type:</strong> <?php echo htmlspecialchars($eventDetails['event_type']); ?></p>
            <?php endif; ?>
            <?php if (!empty($eventDetails['max_attendees'])): ?>
            <p><strong>Maximum Attendees:</strong> <?php echo htmlspecialchars($eventDetails['max_attendees']); ?></p>
            <?php endif; ?>
            
        </div>
        
        <div class="event-description">
            <h2>Description</h2>
            <p><?php echo nl2br(htmlspecialchars($eventDetails['description'])); ?></p>
        </div>

        <?php if (isset($_SESSION['user_id'])): ?>
        <!-- Only show RSVP form for logged-in users -->
        <div class="rsvp-section">
            <h2>RSVP</h2>
            <?php if (isset($_GET['rsvp']) && $_GET['rsvp'] == 'success'): ?>
                <div class="success-message">
                    <p>Your RSVP has been recorded!</p>
                </div>
            <?php endif; ?>
            
            <form id="rsvp-form" action="../server/event_handlers.php" method="POST">
                <input type="hidden" name="event_id" value="<?php echo $eventId; ?>">
                <input type="hidden" name="action" value="rsvp">

                <div class="form-group">
                    <label for="status">RSVP Status:</label>
                    <select id="status" name="status" required>
                        <option value="attending" <?php echo $userRsvpStatus == 'attending' ? 'selected' : ''; ?>>Attending</option>
                        <option value="maybe" <?php echo $userRsvpStatus == 'maybe' ? 'selected' : ''; ?>>Maybe</option>
                        <option value="not_attending" <?php echo $userRsvpStatus == 'not_attending' ? 'selected' : ''; ?>>Not Attending</option>
                    </select>
                </div>

                <button type="submit" class="btn">
                    <?php echo $userRsvpStatus ? 'Update RSVP' : 'Submit RSVP'; ?>
                </button>
            </form>
        </div>
        <?php else: ?>
        <div class="login-prompt">
            <p>Please <a href="login.php">log in</a> to RSVP for this event.</p>
        </div>
        <?php endif; ?>
        
        <div class="event-actions">
            <a href="events.php" class="btn">Go to Events</a>
            <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $eventDetails['user_id']): ?>
            <a href="edit-event.php?id=<?php echo $eventId; ?>" class="btn">Edit Event</a>
            <button id="delete-event" class="btn delete">Delete Event</button>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="../scripts/event-details.js"></script>

<?php include '../includes/footer.php'; ?>