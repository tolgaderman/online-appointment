<?php
require_once 'db.php';

$date = $_GET['date'] ?? '';

try {
    // Get booked times for the selected date
    $stmt = $db->prepare("
        SELECT appointment_time 
        FROM appointments 
        WHERE appointment_date = ? 
        AND (status = 'pending' OR status = 'confirmed')
    ");
    $stmt->execute([$date]);
    $bookedTimes = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Standardize time format (e.g., "9:00" -> "09:00")
    $formattedBookedTimes = array_map(function($time) {
        $hour = explode(':', $time)[0];
        return sprintf("%02d:00", intval($hour));
    }, $bookedTimes);

    // Debugging
    error_log("Date: " . $date);
    error_log("Booked times: " . print_r($formattedBookedTimes, true));

    echo json_encode([
        'success' => true,
        'bookedTimes' => $formattedBookedTimes
    ]);
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?> 