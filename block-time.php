<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['admin']) || !isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die(json_encode(['success' => false, 'error' => 'Unauthorized access']));
}

$date = $_POST['date'] ?? '';
$time = $_POST['time'] ?? '';

if (!$date || !$time) {
    die(json_encode(['success' => false, 'error' => 'Date and time are required']));
}

try {
    // Create a fake appointment (blocked by admin)
    $stmt = $db->prepare("
        INSERT INTO appointments 
        (appointment_date, appointment_time, name, phone, email, status) 
        VALUES (?, ?, 'BLOCKED', 'BLOCKED', 'BLOCKED', 'confirmed')
    ");
    
    $success = $stmt->execute([$date, $time]);
    
    echo json_encode(['success' => $success]);
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
?> 