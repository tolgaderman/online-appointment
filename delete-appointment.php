<?php
session_start();
require_once 'db.php';

// Authorization and CSRF check
if (!isset($_SESSION['admin']) || !isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    error_log("Unauthorized access attempt: " . ($_SERVER['REMOTE_ADDR'] ?? 'Unknown IP'));
    die(json_encode(['success' => false, 'error' => 'Unauthorized access']));
}

// Check POST data
if (!isset($_POST['id'])) {
    die(json_encode(['success' => false, 'error' => 'ID parameter is missing']));
}

$id = intval($_POST['id']);

try {
    // First check if the appointment exists
    $checkStmt = $db->prepare("SELECT id FROM appointments WHERE id = ?");
    $checkStmt->execute([$id]);
    
    if ($checkStmt->rowCount() === 0) {
        die(json_encode(['success' => false, 'error' => 'Appointment not found']));
    }

    // Delete the appointment
    $stmt = $db->prepare("DELETE FROM appointments WHERE id = ?");
    $success = $stmt->execute([$id]);
    
    if ($success) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Deletion failed']);
    }
} catch(PDOException $e) {
    error_log("Deletion error: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
?> 