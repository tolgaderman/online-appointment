<?php
require_once 'db.php';
require_once 'send-mail.php';


// Get POST data
$data = $_POST;


try {
    // Prepare data
    $date = $data['date'];
    $time = $data['time'];
    $name = $data['name'];
    $phone = $data['phone'];
    $email = $data['email'];

    // Check if there is another appointment at the selected date and time
    $checkStmt = $db->prepare("SELECT COUNT(*) FROM appointments WHERE appointment_date = ? AND appointment_time = ? AND status != 'cancelled'");
    $checkStmt->execute([$date, $time]);
    $count = $checkStmt->fetchColumn();

    if ($count > 0) {
        // This time slot is full
        echo json_encode([
            'success' => false,
            'error' => 'This time slot is full. Please select another time.'
        ]);
        exit;
    }

    // Create cancellation code
    $cancel_code = md5($email . $date . $time . uniqid());

    // Save appointment
    $stmt = $db->prepare("INSERT INTO appointments (appointment_date, appointment_time, name, phone, email, status, cancel_code) VALUES (?, ?, ?, ?, ?, 'confirmed', ?)");
    $success = $stmt->execute([$date, $time, $name, $phone, $email, $cancel_code]);

    if ($success) {
        // Send email
        $mailSent = sendAppointmentMail($email, $name, $date, $time, $cancel_code);
        
        if (!$mailSent) {
            error_log("Mail not sent: {$email}");
        }
    }

    echo json_encode(['success' => $success]);

} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?> 