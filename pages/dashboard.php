<?php
/**
 * User Dashboard Page
 * 
 * This page displays a personalized dashboard for authenticated users.
 * It shows events created by the user and events they are attending.
 * Users can view, edit, and delete their created events, as well as
 * manage their RSVPs for events they're attending.
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include required files for authentication and database connection
require_once '../server/auth.php';
require_once '../database/db_connect.php';
include '../includes/header.php';

// Authentication check - redirect unauthenticated users to login page
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Initialize variables
$user_id = $_SESSION['user_id'];
$created_events = [];
$attending_events = [];
$error_message = null;

// Query to retrieve events created by this user
// Joins with users table to get organizer information
$stmt = $mysqli->prepare("SELECT e.*, u.username as organizer_name 
                      FROM events e 
                      LEFT JOIN users u ON e.user_id = u.id
                      WHERE e.user_id = ? 
                      ORDER BY e.date DESC");
if ($stmt) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Store all user-created events in array
    while ($event = $result->fetch_assoc()) {
        $created_events[] = $event;
    }
    $stmt->close();
} else {
    // Set error message if query fails
    $error_message = "An error occurred while retrieving your events.";
}

// Query to retrieve events the user is attending (has RSVP'd to)
// Includes the RSVP status for each event
$stmt = $mysqli->prepare("SELECT e.*, u.username as organizer_name, r.status as rsvp_status
                     FROM events e
                     JOIN users u ON e.user_id = u.id
                     JOIN rsvps r ON e.id = r.event_id
                     WHERE r.user_id = ?
                     ORDER BY e.date ASC");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$attending_events = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<div class="container">
    <div class="dashboard">
        <h1>User Dashboard</h1>
        
        <!-- Display error messages if any -->
        <?php if ($error_message): ?>
            <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        
        <!-- Dashboard action buttons -->
        <div class="dashboard-actions">
            <a href="add_event.php" class="btn">Create New Event</a>
        </div>
        
        <!-- Section: Events created by the user -->
        <h2>Your Events</h2>
        <?php if (!empty($created_events)): ?>
            <ul class="dashboard-events-list">
                <?php foreach ($created_events as $event): ?>
                    <li class="event-item">
                        <div class="event-info">
                            <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                            <p class="event-date">
                                <?php 
                                    // Format event date nicely
                                    $eventDate = new DateTime($event['date']);
                                    echo $eventDate->format('F j, Y, g:i a'); 
                                ?>
                            </p>
                            <p class="event-location">
                                <?php echo htmlspecialchars($event['city'] . ', ' . $event['country']); ?>
                            </p>
                        </div>
                        <!-- Action buttons for user's created events -->
                        <div class="event-actions">
                            <a href="event-details.php?id=<?php echo $event['id']; ?>" class="btn">View</a>
                            <button class="btn delete" onclick="deleteEvent(<?php echo $event['id']; ?>)">Delete</button>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <!-- Displayed when user hasn't created any events -->
            <p>You haven't created any events yet. <a href="add_event.php">Create your first event</a>.</p>
        <?php endif; ?>
        
        <!-- Section: Events the user is attending -->
        <h2>Events You're Attending</h2>
        <?php if (!empty($attending_events)): ?>
            <ul class="dashboard-events-list attending-list">
                <?php foreach ($attending_events as $event): ?>
                    <li class="event-item">
                        <div class="event-info">
                            <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                            <p class="event-date">
                                <?php 
                                    // Format event date nicely
                                    $eventDate = new DateTime($event['date']);
                                    echo $eventDate->format('F j, Y, g:i a'); 
                                ?>
                            </p>
                            <p class="event-location">
                                <?php echo htmlspecialchars($event['city'] . ', ' . $event['country']); ?>
                            </p>
                            <p class="event-organizer">
                                Organized by: <?php echo htmlspecialchars($event['organizer_name']); ?>
                            </p>
                        </div>
                        <div class="event-actions">
                            <a href="event-details.php?id=<?php echo $event['id']; ?>" class="btn">View</a>
                            
                            <!-- RSVP status badge displays current response status -->
                            <div class="rsvp-badge-container">
                                <?php if ($event['rsvp_status'] == 'attending'): ?>
                                    <span class="attending-badge">Attending</span>
                                <?php elseif ($event['rsvp_status'] == 'maybe'): ?>
                                    <span class="maybe-badge">Maybe</span>
                                <?php elseif ($event['rsvp_status'] == 'not_attending'): ?>
                                    <span class="declined-badge">Not Attending</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <!-- Displayed when user isn't attending any events -->
            <p>You're not attending any events yet. <a href="events.php">Browse events</a> to find some to attend.</p>
        <?php endif; ?>
    </div>
</div>

<!-- Include JavaScript for event actions (e.g. delete functionality) -->
<script src="../scripts/events.js"></script>

<?php include '../includes/footer.php'; ?>