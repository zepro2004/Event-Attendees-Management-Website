<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Planning System</title>
    <link rel="stylesheet" href="assets/styles/styles.css">
</head>
<body class="body">
    <?php include 'includes/header.php'; ?>
    
    <section class="hero full-bg">
        <div class="hero-content">
            <h1>Plan Smarter, Celebrate Better</h1>
            <p>Your one-stop platform for discovering and managing events</p>
            <a href="pages/events.php" class="btn">Explore Events</a>
        </div>
    </section>

    <section class="featured">
        <h2>Featured Events</h2>
        <div class="event-grid">
        <?php
        // Include database connection
        include 'database/db_connect.php';
        
        // Query to fetch 4 most recent events with image URLs
        $stmt = $mysqli->prepare("SELECT id, title, date, description, image_url, city 
                                FROM events 
                                WHERE image_url IS NOT NULL AND image_url != '' 
                                ORDER BY date DESC 
                                LIMIT 4");
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Check if we have events
        if ($result->num_rows > 0) {
            while ($event = $result->fetch_assoc()) {
                // Format the date
                $eventDate = new DateTime($event['date']);
                $formattedDate = $eventDate->format('M j, Y');
                
                // Create event card
                echo '<div class="event-card">';
                
                // Image
                if (!empty($event['image_url'])) {
                    echo '<div class="event-image">';
                    echo '<img src="' . htmlspecialchars($event['image_url']) . '" alt="' . htmlspecialchars($event['title']) . '">';
                    echo '</div>';
                }
                
                // Event info
                echo '<div class="event-info">';
                echo '<h3>' . htmlspecialchars($event['title']) . '</h3>';
                echo '<p class="event-date">' . $formattedDate . '</p>';
                
                if (!empty($event['city'])) {
                    echo '<p class="event-location">' . htmlspecialchars($event['city']) . '</p>';
                }
                
                echo '<p class="event-excerpt">' . htmlspecialchars(substr($event['description'], 0, 100)) . '...</p>';
                echo '<a href="pages/event-details.php?id=' . $event['id'] . '" class="btn">View Details</a>';
                echo '</div>';
                echo '</div>';
            }
            
            $stmt->close();
        } else {
            echo '<p>No featured events available at this time.</p>';
        }
        ?>    
        </div>
    </section>

    <section class="testimonials">
        <h2>What Users Say</h2>
        <blockquote>
            "I've never organized an event so easily. Everything was intuitive and fast!" – Sarah M.
        </blockquote>
        <blockquote>
            "Great design, smooth experience, and love the RSVP feature!" – Daniel K.
        </blockquote>
    </section>

    <?php include 'includes/footer.php'; ?>
</body>
</html>