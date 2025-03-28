<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/Assignment2/database/db_connect.php';
require_once 'events.php';

// Fetch events with search/filter parameters
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        // Start with base query
        $sql = "SELECT e.*, u.username as organizer_name, et.name as event_type 
                FROM events e 
                LEFT JOIN users u ON e.user_id = u.id
                LEFT JOIN event_types et ON e.event_type_id = et.id";
        
        $params = [];
        $whereConditions = [];
        
        // Apply search filters if provided
        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $searchTerm = '%' . $_GET['search'] . '%';
            $whereConditions[] = "(e.title LIKE ? OR e.description LIKE ?)";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if (isset($_GET['city']) && !empty($_GET['city'])) {
            $whereConditions[] = "e.city LIKE ?";
            $params[] = '%' . $_GET['city'] . '%';
        }
        
        if (isset($_GET['date']) && !empty($_GET['date'])) {
            $whereConditions[] = "DATE(e.date) = ?";
            $params[] = $_GET['date'];
        }
                // Province/State filter
        if (isset($_GET['state']) && !empty($_GET['state'])) {
            $whereConditions[] = "e.state LIKE ?";
            $params[] = '%' . $_GET['state'] . '%';
        }
        // Postal code filter
        if (isset($_GET['postal_code']) && !empty($_GET['postal_code'])) {
            $whereConditions[] = "e.postal_code LIKE ?";
            $params[] = '%' . $_GET['postal_code'] . '%';
        }
        // Year filter
        if (isset($_GET['year']) && !empty($_GET['year'])) {
            $whereConditions[] = "YEAR(e.date) = ?";
            $params[] = $_GET['year'];
        }
        // Month filter
        if (isset($_GET['month']) && !empty($_GET['month'])) {
            $whereConditions[] = "MONTH(e.date) = ?";
            $params[] = $_GET['month'];
        }
        // Max attendees filter
        if (isset($_GET['max_attendees']) && !empty($_GET['max_attendees'])) {
            $whereConditions[] = "e.max_attendees <= ?";
            $params[] = $_GET['max_attendees'];
        }
        
        // Add WHERE clause if conditions exist
        if (!empty($whereConditions)) {
            $sql .= " WHERE " . implode(" AND ", $whereConditions);
        }
        
        // Add ordering
        $sql .= " ORDER BY e.date";
        
        // Prepare and execute the query
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Return events as JSON
        header('Content-Type: application/json');
        echo json_encode($events);
        exit;
    } catch (PDOException $e) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        exit;
    }
}

// Handle DELETE requests for removing events
if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && isset($_GET['id'])) {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'You must be logged in to remove events.']);
        exit;
    }
    
    $eventId = $_GET['id'];
    $userId = $_SESSION['user_id'];
    
    try {
        if (removeEvent($pdo, $eventId, $userId)) {
            echo json_encode(['status' => 'success', 'message' => 'Event removed successfully.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to remove event or you do not have permission.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

// Handle RSVP submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'rsvp') {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'You must be logged in to RSVP.']);
        exit;
    }
    
    // Validate inputs
    if (!isset($_POST['event_id']) || !isset($_POST['status'])) {
        echo json_encode(['status' => 'error', 'message' => 'Missing required fields.']);
        exit;
    }
    
    $eventId = $_POST['event_id'];
    $userId = $_SESSION['user_id'];
    $status = $_POST['status'];
    
    // Validate status value
    if (!in_array($status, ['attending', 'maybe', 'not_attending'])) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid RSVP status.']);
        exit;
    }
    
    try {
        if (handleEventRSVP($pdo, $eventId, $userId, $status)) {
            // For form submissions, redirect back to event page
            header("Location: ../pages/event-details.php?id=$eventId&rsvp=success");
        } else {
            header("Location: ../pages/event-details.php?id=$eventId&rsvp=error");
        }
    } catch (PDOException $e) {
        error_log("RSVP error: " . $e->getMessage());
        header("Location: ../pages/event-details.php?id=$eventId&rsvp=error");
    }
    exit;
}

// Handle POST requests for adding events
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'You must be logged in to add events.']);
        exit;
    }
    
    $eventData = [
        'title' => $_POST['title'],
        'description' => $_POST['description'] ?? '',
        'date' => $_POST['date'],
        'end_date' => $_POST['end_date'] ?? null,
        'address' => $_POST['address'],
        'city' => $_POST['city'],
        'state' => $_POST['state'] ?? '',
        'postal_code' => $_POST['postal_code'] ?? '',
        'country' => $_POST['country'] ?? 'Canada',
        'event_type_id' => $_POST['event_type_id'] ?? null,
        'user_id' => $_SESSION['user_id'],
        'max_attendees' => $_POST['max_attendees'] ?? null,
        'image_url' => $_POST['image_url'] ?? null
    ];
    
    try {
        if (addEvent($pdo, $eventData)) {
            echo json_encode(['status' => 'success', 'message' => 'Event added successfully.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to add event.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}



