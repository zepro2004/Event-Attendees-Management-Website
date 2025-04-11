<?php
/**
 * Event Handlers API
 * 
 * This file provides the backend API endpoints for event management:
 * - GET: Fetch and filter events
 * - POST: Create events and handle RSVPs
 * - DELETE: Remove events
 * 
 * All responses are formatted as JSON for client-side processing.
 */

// Suppress PHP errors/warnings from appearing in output
// This prevents error messages from breaking the JSON response format
ini_set('display_errors', 0);
error_reporting(0);

// Start session if not already started
// Required for user authentication checks
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include required files
require_once $_SERVER['DOCUMENT_ROOT'] . '/Assignment2/database/db_connect.php';
require_once 'events.php'; // Contains event business logic functions

// Ensure proper JSON responses
header('Content-Type: application/json');

/**
 * GET ENDPOINT: Fetch events with optional filtering
 * Supports multiple filter parameters including:
 * - search: Text search in title and description
 * - city/state/postal_code: Location filters
 * - date/year/month: Time-based filters
 */
if ($_SERVER['REQUEST_METHOD'] === 'GET' && !isset($_GET['id'])) {
    try {
        // Start with base query - join necessary tables for related data
        $sql = "SELECT e.*, u.username as organizer_name, et.name as event_type 
                FROM events e 
                LEFT JOIN users u ON e.user_id = u.id
                LEFT JOIN event_types et ON e.event_type_id = et.id";
        
        // Arrays to store query conditions and parameters
        $whereConditions = [];
        $paramTypes = "";
        $paramValues = [];
        
        // Process text search - covers both title and description
        if (!empty($_GET['search'])) {
            $whereConditions[] = "(e.title LIKE ? OR e.description LIKE ?)";
            $paramTypes .= "ss";
            $searchTerm = "%" . $_GET['search'] . "%";
            $paramValues[] = $searchTerm;
            $paramValues[] = $searchTerm;
        }
        
        // Process location filters - city, state, postal code
        $locationFilters = ['city', 'state', 'postal_code'];
        foreach ($locationFilters as $filter) {
            if (!empty($_GET[$filter])) {
                $whereConditions[] = "e.$filter LIKE ?";
                $paramTypes .= "s";
                $paramValues[] = "%" . $_GET[$filter] . "%";
            }
        }
        
        // Process date filters - specific date, year, month
        if (!empty($_GET['date'])) {
            $whereConditions[] = "DATE(e.date) = ?";
            $paramTypes .= "s";
            $paramValues[] = $_GET['date'];
        }
        
        if (!empty($_GET['year'])) {
            $whereConditions[] = "YEAR(e.date) = ?";
            $paramTypes .= "i";
            $paramValues[] = (int)$_GET['year'];
        }
        
        if (!empty($_GET['month'])) {
            $whereConditions[] = "MONTH(e.date) = ?";
            $paramTypes .= "i";
            $paramValues[] = (int)$_GET['month'];
        }
        
        // Add WHERE clause if any conditions exist
        if (!empty($whereConditions)) {
            $sql .= " WHERE " . implode(" AND ", $whereConditions);
        }
        
        // Add ordering - chronological by default
        $sql .= " ORDER BY e.date";
        
        // Prepare statement
        $stmt = $mysqli->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $mysqli->error);
        }
        
        // Bind parameters if we have any using dynamic binding
        if (!empty($paramValues)) {
            try {
                // Create array of references for bind_param
                $params = array($paramTypes);
                foreach ($paramValues as $key => $value) {
                    $params[] = &$paramValues[$key];
                }
                call_user_func_array(array($stmt, 'bind_param'), $params);
            } catch (Exception $e) {
                throw new Exception("Binding parameters failed: " . $e->getMessage());
            }
        }
        
        // Execute the query
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        $result = $stmt->get_result();
        
        // Fetch all events as associative arrays
        $events = [];
        while ($row = $result->fetch_assoc()) {
            $events[] = $row;
        }
        
        $stmt->close();
        
        // Return events as JSON array
        echo json_encode($events);
    } catch (Exception $e) {
        // Log error and return error response
        logError("Error in GET events: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Error retrieving events: ' . $e->getMessage()]);
    }
    exit;
}

/**
 * DELETE ENDPOINT: Remove an event
 * Requires:
 * - User authentication
 * - Event ID parameter
 * - User must be the event creator
 */
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    try {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['status' => 'error', 'message' => 'Authentication required']);
            exit;
        }
        
        // Get event ID from query parameters
        $eventId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        // Call event removal function (this checks ownership internally)
        $result = removeEvent($mysqli, $eventId, $_SESSION['user_id']);
        
        // Return success/failure response
        echo json_encode([
            'status' => $result ? 'success' : 'error',
            'message' => $result ? 'Event removed successfully' : 'Failed to remove event'
        ]);
    } catch (Exception $e) {
        // Log error and return error response
        logError("Error in DELETE event: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Error deleting event: ' . $e->getMessage()]);
    }
    exit;
}

/**
 * POST ENDPOINT: Handle RSVP submissions
 * Processes user responses to events (attending, maybe, not attending)
 * Redirects to event details page with appropriate status
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'rsvp') {
    try {
        // Check if user is logged in - redirect to login if not
        if (!isset($_SESSION['user_id'])) {
            header('Location: ../pages/login.php?redirect=' . urlencode($_SERVER['HTTP_REFERER']));
            exit;
        }
        
        // Validate required inputs
        if (!isset($_POST['event_id']) || !isset($_POST['status'])) {
            header("Location: ../pages/event-details.php?id={$_POST['event_id']}&rsvp=error&message=missing_fields");
            exit;
        }
        
        $eventId = (int)$_POST['event_id'];
        $userId = (int)$_SESSION['user_id'];
        $status = $_POST['status'];
        
        // Validate RSVP status value - must be one of the allowed values
        if (!in_array($status, ['attending', 'maybe', 'not_attending'])) {
            header("Location: ../pages/event-details.php?id=$eventId&rsvp=error&message=invalid_status");
            exit;
        }
        
        // Process the RSVP and redirect based on result
        if (handleEventRSVP($mysqli, $eventId, $userId, $status)) {
            header("Location: ../pages/event-details.php?id=$eventId&rsvp=success");
        } else {
            header("Location: ../pages/event-details.php?id=$eventId&rsvp=error");
        }
    } catch (Exception $e) {
        // Log error and redirect with error status
        error_log("RSVP error: " . $e->getMessage());
        header("Location: ../pages/event-details.php?id={$_POST['event_id']}&rsvp=error");
    }
    exit;
}

/**
 * POST ENDPOINT: Create a new event
 * Handles form submission including image upload
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    try {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['status' => 'error', 'message' => 'Authentication required']);
            exit;
        }
        
        // Process image upload if provided
        $imageUrl = null;
        if (isset($_FILES['event_image']) && $_FILES['event_image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/Assignment2/uploads/';
            
            // Create upload directory if it doesn't exist
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            
            // Generate unique filename to prevent collisions
            $filename = uniqid() . '.' . pathinfo($_FILES['event_image']['name'], PATHINFO_EXTENSION);
            $targetPath = $uploadDir . $filename;
            
            // Validate file type - security measure to prevent malicious file uploads
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($_FILES['event_image']['type'], $allowedTypes)) {
                echo json_encode(['status' => 'error', 'message' => 'Invalid file type']);
                exit;
            }
            
            // Validate file size - limit to 5MB
            if ($_FILES['event_image']['size'] > 5 * 1024 * 1024) {
                echo json_encode(['status' => 'error', 'message' => 'File too large (max 5MB)']);
                exit;
            }
            
            // Move uploaded file to final destination
            if (move_uploaded_file($_FILES['event_image']['tmp_name'], $targetPath)) {
                $imageUrl = '/Assignment2/uploads/' . $filename;
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to upload image']);
                exit;
            }
        }
        
        // Gather event data from form submission
        $eventData = [
            'title' => $_POST['title'] ?? '',
            'description' => $_POST['description'] ?? '',
            'date' => $_POST['date'] ?? '',
            'end_date' => $_POST['end_date'] ?? null,
            'address' => $_POST['address'] ?? '',
            'city' => $_POST['city'] ?? '',
            'state' => $_POST['state'] ?? '',
            'postal_code' => $_POST['postal_code'] ?? '',
            'country' => $_POST['country'] ?? 'Canada',
            'event_type_id' => !empty($_POST['event_type_id']) ? (int)$_POST['event_type_id'] : null,
            'user_id' => (int)$_SESSION['user_id'],
            'max_attendees' => !empty($_POST['max_attendees']) ? (int)$_POST['max_attendees'] : null,
            'image_url' => $imageUrl
        ];
        
        // Add event to database
        $result = addEvent($mysqli, $eventData);
        echo json_encode([
            'status' => $result ? 'success' : 'error',
            'message' => $result ? 'Event added successfully' : 'Failed to add event'
        ]);
    } catch (Exception $e) {
        // Log error and return error response
        logError("Error in add event: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Error adding event: ' . $e->getMessage()]);
    }
    exit;
}

// Default response for invalid requests
// This catches any request that didn't match the above endpoints
http_response_code(400);
echo json_encode(['status' => 'error', 'message' => 'Invalid request']);

/**
 * Helper function to log errors
 * 
 * @param string $message Error message to log
 */
function logError($message) {
    error_log($message);
}