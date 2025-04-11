<?php
/**
 * Events Business Logic
 * 
 * This file contains core functions for event management including:
 * - Retrieving events (all events or by specific ID)
 * - Creating new events
 * - Removing events
 * - Managing RSVPs for events
 * 
 * These functions are called by event_handlers.php to separate
 * business logic from API endpoint handling.
 */

// Include database connection and ensure session is started
require_once $_SERVER['DOCUMENT_ROOT'] . '/Assignment2/database/db_connect.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Retrieves all events from the database with related information
 * 
 * @param mysqli $mysqli Database connection object
 * @return array Array of events with organizer and event type information
 */
function getEvents($mysqli) {
    // Query includes joins to get organizer username and event type name
    $sql = "SELECT e.*, u.username as organizer_name, et.name as event_type 
            FROM events e 
            LEFT JOIN users u ON e.user_id = u.id
            LEFT JOIN event_types et ON e.event_type_id = et.id
            ORDER BY e.date";
    $result = $mysqli->query($sql);
    
    // Build array of events from result set
    $events = [];
    while ($row = $result->fetch_assoc()) {
        $events[] = $row;
    }
    return $events;
}

/**
 * Creates a new event in the database
 * 
 * @param mysqli $mysqli Database connection object
 * @param array $data Event data including title, description, date, location, etc.
 * @return bool True on successful creation, false otherwise
 */
function addEvent($mysqli, $data) {
    // Prepare statement with all event fields
    $stmt = $mysqli->prepare("INSERT INTO events (title, description, date, end_date, address, 
                              city, state, postal_code, country, event_type_id, user_id, 
                              max_attendees, image_url) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    // Bind all parameters with appropriate types
    // s = string, i = integer
    $stmt->bind_param("sssssssssiiss", 
        $data['title'], $data['description'], $data['date'], $data['end_date'], 
        $data['address'], $data['city'], $data['state'], $data['postal_code'], 
        $data['country'], $data['event_type_id'], $data['user_id'], 
        $data['max_attendees'], $data['image_url']);
    
    // Execute and close statement
    $result = $stmt->execute();
    $stmt->close();
    return $result;
}

/**
 * Removes an event from the database
 * Requires matching user_id to ensure only the creator can delete their events
 * 
 * @param mysqli $mysqli Database connection object
 * @param int $eventId ID of the event to remove
 * @param int $userId ID of the user attempting to remove the event
 * @return bool True if event was deleted, false otherwise
 */
function removeEvent($mysqli, $eventId, $userId) {
    // Secure deletion - requires both event ID and matching user ID
    $stmt = $mysqli->prepare("DELETE FROM events WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $eventId, $userId);
    $result = $stmt->execute();
    $stmt->close();
    return $result;
}

/**
 * Retrieves a specific event by its ID
 * Includes organizer and event type information from related tables
 * 
 * @param mysqli $mysqli Database connection object
 * @param int $eventId ID of the event to retrieve
 * @return array|null Event data as associative array, or null if not found
 */
function getEvent($mysqli, $eventId) {
    // Query with joins for related data
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
    return $event;
}

/**
 * Handles RSVP functionality for events
 * Supports creating, updating, or removing RSVPs based on status
 * 
 * @param mysqli $mysqli Database connection object
 * @param int $eventId ID of the event being RSVP'd to
 * @param int $userId ID of the user making the RSVP
 * @param string $status RSVP status ('attending', 'maybe', 'not_attending')
 * @return bool True on successful operation, false otherwise
 */
function handleEventRSVP($mysqli, $eventId, $userId, $status) {
    try {
        // First check if user already has an RSVP for this event
        $checkStmt = $mysqli->prepare("SELECT id FROM rsvps WHERE event_id = ? AND user_id = ?");
        $checkStmt->bind_param("ii", $eventId, $userId);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        $existingRsvp = $result->fetch_assoc();
        $checkStmt->close();
        
        // If status is 'not_attending', we might want to remove the RSVP entirely
        if ($status === 'not_attending' && $existingRsvp) {
            $deleteStmt = $mysqli->prepare("DELETE FROM rsvps WHERE id = ?");
            $deleteStmt->bind_param("i", $existingRsvp['id']);
            $success = $deleteStmt->execute();
            $deleteStmt->close();
            return $success;
        }
        
        // If RSVP exists, update it
        if ($existingRsvp) {
            $updateStmt = $mysqli->prepare("UPDATE rsvps SET status = ? WHERE id = ?");
            $updateStmt->bind_param("si", $status, $existingRsvp['id']);
            $success = $updateStmt->execute();
            $updateStmt->close();
            return $success;
        } 
        
        // Otherwise, create a new RSVP
        $insertStmt = $mysqli->prepare("INSERT INTO rsvps (event_id, user_id, status) VALUES (?, ?, ?)");
        $insertStmt->bind_param("iis", $eventId, $userId, $status);
        $success = $insertStmt->execute();
        $insertStmt->close();
        return $success;
    } catch (Exception $e) {
        // Log any errors but don't expose details to the user
        error_log("RSVP error: " . $e->getMessage());
        return false;
    }
}

/**
 * Gets events that a specific user has created
 * 
 * @param mysqli $mysqli Database connection object
 * @param int $userId ID of the user whose events to fetch
 * @return array Array of events created by the specified user
 */
function getUserEvents($mysqli, $userId) {
    $stmt = $mysqli->prepare("SELECT * FROM events WHERE user_id = ? ORDER BY date DESC");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $events = [];
    while ($row = $result->fetch_assoc()) {
        $events[] = $row;
    }
    $stmt->close();
    return $events;
}

/**
 * Gets events that a user has RSVP'd to
 * 
 * @param mysqli $mysqli Database connection object
 * @param int $userId ID of the user
 * @return array Array of events the user is attending with RSVP status
 */
function getUserAttendingEvents($mysqli, $userId) {
    $stmt = $mysqli->prepare("SELECT e.*, r.status as rsvp_status, u.username as organizer_name 
                             FROM events e
                             JOIN rsvps r ON e.id = r.event_id
                             JOIN users u ON e.user_id = u.id
                             WHERE r.user_id = ?
                             ORDER BY e.date ASC");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $events = [];
    while ($row = $result->fetch_assoc()) {
        $events[] = $row;
    }
    $stmt->close();
    return $events;
}