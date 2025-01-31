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

    // Create all times and mark booked ones
    $html = '';
    for ($hour = 9; $hour < 17; $hour++) {
        $timeSlot = sprintf("%02d:00", $hour);
        $isBooked = in_array($timeSlot, $bookedTimes);
        
        $html .= sprintf(
            '<div class="time-slot%s" %s onclick="%s">%s</div>',
            $isBooked ? ' booked' : '',
            $isBooked ? 'title="This time is already booked"' : '',
            $isBooked ? '' : "selectTimeSlot(this)",
            $timeSlot
        );
    }

    echo $html;
} catch(PDOException $e) {
    echo '<div class="error">An error occurred: ' . htmlspecialchars($e->getMessage()) . '</div>';
}
?> 