<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: text/html; charset=UTF-8');
require_once 'db.php';

$code = $_GET['code'] ?? '';
// Debugging
error_log("Cancel code: " . $code);

if (empty($code)) {
    die("Invalid cancel code!");
}

try {
    // First find the appointment
    $stmt = $db->prepare("SELECT * FROM appointments WHERE cancel_code = ? AND status != 'cancelled'");
    $stmt->execute([$code]);
    // Debugging
    error_log("SQL query executed");
    error_log("Found record count: " . $stmt->rowCount());
    
    $appointment = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$appointment) {
        die("Appointment not found or already cancelled!");
    }
    
    // Format the date
    $formattedDate = date('d/m/Y', strtotime($appointment['appointment_date']));
    
    // Cancel the appointment
    $updateStmt = $db->prepare("UPDATE appointments SET status = 'cancelled' WHERE id = ?");
    $success = $updateStmt->execute([$appointment['id']]);
    
    if ($success) {
        // Send cancellation confirmation email
        $headers = "From: Your Company <noreply@your-company.com>\r\n";
        $headers .= "Reply-To: noreply@your-company.com\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        
        $subject = "=?UTF-8?B?" . base64_encode("Your appointment has been cancelled") . "?=";
        $message = "
        <!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
        </head>
        <body>
            <h2>Dear {$appointment['name']},</h2>
            <p>Your appointment has been successfully cancelled:</p>
            <p><b>Appointment Details:</b></p>
            <p>Date: {$formattedDate}</p>
            <p>Time: {$appointment['appointment_time']}</p>
              <hr>
        <p><b>Contact Information:</b></p>
        <p>Phone: <a href='tel:+491234567890'>+491234567890</a></p>
        <p>E-mail: <a href='mailto:info@your-company.com'>info@your-company.com</a></p>
        <hr>
        <p>Best regards,<br>Your Company</p>
        </body>
        </html>";
        
        mail($appointment['email'], $subject, $message, $headers);
        
        // Success message
        echo "
        <!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { 
                    font-family: Arial, sans-serif; 
                    margin: 0;
                    padding: 0;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    min-height: 100vh;
                    background-color: #f5f5f5;
                }
                .container {
                    background-color: white;
                    padding: 30px;
                    border-radius: 8px;
                    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                    text-align: center;
                    max-width: 500px;
                    width: 90%;
                }
                .success { 
                    color: green;
                    margin-bottom: 20px;
                }
                .details { 
                    margin: 20px 0;
                    padding: 20px;
                    border: 1px solid #ddd;
                    border-radius: 4px;
                    background-color: #f9f9f9;
                }
                .details p {
                    margin: 10px 0;
                }
            </style>
        </head>
        <body>
            <div class='container'>
                <h2 class='success'>Your appointment has been successfully cancelled</h2>
                <div class='details'>
                    <p><b>Date:</b> {$formattedDate}</p>
                    <p><b>Time:</b> {$appointment['appointment_time']}</p>
                </div>
                <p>Cancellation confirmation has been sent to your email address.</p>
            </div>
        </body>
        </html>";
    } else {
        die("An error occurred while cancelling the appointment!");
    }
} catch(PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>