<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once '../server/auth.php';
require_once '../database/db_connect.php';
include '../includes/header.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$created_events = [];
$attending_events = [];

// Query to fetch events created by the logged-in user
try {
    // 1. First get events created by the user
    $created_query = "SELECT e.*, et.name as event_type, u.username as organizer_name, 'created' as relationship
                  FROM events e 
                  LEFT JOIN event_types et ON e.event_type_id = et.id
                  LEFT JOIN users u ON e.user_id = u.id
                  WHERE e.user_id = ? 
                  ORDER BY e.date DESC";
    $stmt = $pdo->prepare($created_query);
    $stmt->execute([$user_id]);
    $created_events = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 2. Get events the user is attending (but didn't create)
    $attending_query = "SELECT e.*, et.name as event_type, u.username as organizer_name, 'attending' as relationship
                     FROM events e
                     LEFT JOIN event_types et ON e.event_type_id = et.id
                     LEFT JOIN users u ON e.user_id = u.id
                     JOIN rsvps r ON e.id = r.event_id
                     WHERE r.user_id = ? AND r.status = 'attending' AND e.user_id != ?
                     ORDER BY e.date ASC";
    $stmt = $pdo->prepare($attending_query);
    $stmt->execute([$user_id, $user_id]);
    $attending_events = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Dashboard query error: " . $e->getMessage());
    $error_message = "An error occurred while retrieving your events.";
}
?>

<div class="container">
    <div class="dashboard">
        <h1>User Dashboard</h1>
        
        <?php if (isset($error_message)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        
        <div class="dashboard-actions">
            <a href="add_event.php" class="btn">Create New Event</a>
        </div>
        
        <h2>Your Events</h2>
        <?php if (!empty($created_events)): ?>
            <ul class="dashboard-events-list">
                <?php foreach ($created_events as $event): ?>
                    <li class="event-item">
                        <div class="event-info">
                            <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                            <p class="event-date">
                                <?php 
                                    $eventDate = new DateTime($event['date']);
                                    echo $eventDate->format('F j, Y, g:i a'); 
                                ?>
                            </p>
                            <p class="event-location">
                                <?php echo htmlspecialchars($event['city'] . ', ' . $event['country']); ?>
                            </p>
                        </div>
                        <div class="event-actions">
                            <a href="event-details.php?id=<?php echo $event['id']; ?>" class="btn">View</a>
                            <a href="edit-event.php?id=<?php echo $event['id']; ?>" class="btn">Edit</a>
                            <button class="btn delete" data-event-id="<?php echo $event['id']; ?>">Delete</button>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>You haven't created any events yet. <a href="add_event.php">Create your first event</a>.</p>
        <?php endif; ?>
        
        <!-- Events user is attending -->
        <h2>Events You're Attending</h2>
        <?php if (!empty($attending_events)): ?>
            <ul class="dashboard-events-list attending-list">
                <?php foreach ($attending_events as $event): ?>
                    <li class="event-item">
                        <div class="event-info">
                            <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                            <p class="event-date">
                                <?php 
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
                            <span class="attending-badge">Attending</span>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>You're not attending any events yet. <a href="events.php">Browse events</a> to find some to attend.</p>
        <?php endif; ?>
    </div>
</div>
<script src="../scripts/dashboard.js"></script>

<?php include '../includes/footer.php'; ?>