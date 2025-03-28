<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Assignment2/database/db_connect.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Function to fetch all events
function getEvents($pdo) {
    $sql = "SELECT e.*, u.username as organizer_name, et.name as event_type 
            FROM events e 
            LEFT JOIN users u ON e.user_id = u.id
            LEFT JOIN event_types et ON e.event_type_id = et.id
            ORDER BY e.date";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to add a new event
function addEvent($pdo, $eventData) {
    $stmt = $pdo->prepare("INSERT INTO events (
        title, description, date, end_date, address, city, state, 
        postal_code, country, event_type_id, user_id, max_attendees, image_url
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    return $stmt->execute([
        $eventData['title'], 
        $eventData['description'],
        $eventData['date'],
        $eventData['end_date'],
        $eventData['address'],
        $eventData['city'],
        $eventData['state'],
        $eventData['postal_code'],
        $eventData['country'],
        $eventData['event_type_id'],
        $eventData['user_id'],
        $eventData['max_attendees'],
        $eventData['image_url']
    ]);
}

// Function to remove an event
function removeEvent($pdo, $eventId, $userId) {
    // Only allow deletion if user owns the event
    $stmt = $pdo->prepare("DELETE FROM events WHERE id = ? AND user_id = ?");
    return $stmt->execute([$eventId, $userId]);
}

function handleEventRSVP($pdo, $eventId, $userId, $status) {
    $checkStmt = $pdo->prepare("SELECT id FROM rsvps WHERE event_id = ? AND user_id = ?");
    $checkStmt->execute([$eventId, $userId]);
    $existingRsvp = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existingRsvp) {
        // Update existing RSVP
        $stmt = $pdo->prepare("UPDATE rsvps SET status = ?, rsvp_date = NOW() WHERE id = ?");
        return $stmt->execute([$status, $existingRsvp['id']]);
    } else {
        // Create new RSVP
        $stmt = $pdo->prepare("INSERT INTO rsvps (event_id, user_id, status, rsvp_date) VALUES (?, ?, ?, NOW())");
        return $stmt->execute([$eventId, $userId, $status]);
    }
}