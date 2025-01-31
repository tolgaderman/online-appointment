<?php
session_start();
require_once 'db.php';

// Debug for
error_log("Session status: " . print_r($_SESSION, true));
error_log("POST data: " . print_r($_POST, true));

// Authorization check
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    die(json_encode(['success' => false, 'error' => 'Unauthorized access']));
}

// POST data
$id = $_POST['id'] ?? null;
$status = $_POST['status'] ?? null;
$csrf_token = $_POST['csrf_token'] ?? null;

// CSRF token check
if (!$csrf_token || $csrf_token !== $_SESSION['csrf_token']) {
    error_log("CSRF token mismatch: Received=" . $csrf_token . ", Session=" . $_SESSION['csrf_token']);
    die(json_encode(['success' => false, 'error' => 'Invalid security token']));
}

if (!$id || !$status) {
    die(json_encode(['success' => false, 'error' => 'Missing parameters']));
}

try {
    // First, get appointment details
    $stmt = $db->prepare("SELECT * FROM appointments WHERE id = ?");
    $stmt->execute([$id]);
    $appointment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$appointment) {
        die(json_encode(['success' => false, 'error' => 'Appointment not found']));
    }

    // Update status
    $updateStmt = $db->prepare("UPDATE appointments SET status = ? WHERE id = ?");
    $success = $updateStmt->execute([$status, $id]);

    if ($success && $status === 'cancelled') {
        // Send cancellation email
        $headers = "From: Your Company <noreply@your-company.com>\r\n";
        $headers .= "Reply-To: noreply@your-company.com\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

        $subject = "Appointment Cancelled";
        $message = "
        <html>
        <body>
            <h2>Dear {$appointment['name']},</h2>
            <p>The following appointment has been cancelled by our side:</p>
            <p><b>Appointment Details:</b></p>
            <p>Date: {$appointment['appointment_date']}</p>
            <p>Time: {$appointment['appointment_time']}</p>
            <hr>
            <p>You can visit our website to make a new appointment or contact us.</p>
            <p><b>Contact Information:</b></p>
            <p>Phone: <a href='tel:+491234567890'>+49 123 456 78 90</a></p>
            <p>E-Mail: <a href='mailto:info@your-company.com'>info@your-company.com</a></p>
            <hr>
            <p>Best Regards,<br>Your Company</p>
        </body>
        </html>";

        mail($appointment['email'], $subject, $message, $headers);
    }

    echo json_encode(['success' => $success]);

} catch(PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?> 