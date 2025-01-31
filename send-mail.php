<?php
function sendAppointmentMail($userEmail, $name, $date, $time, $cancel_code) {
    // Admin mail address
    $admin_email = "info@your-company.com";
    
    // Mail headers
    $headers = "From: Your Company <noreply@your-company.com>\r\n";
    $headers .= "Reply-To: noreply@your-company.com\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    // User mail
    $subject = "Appointment Confirmation";
    
    // Create cancellation link
    $cancel_link = "https://your-company.com/cancel-appointment.php?code=" . $cancel_code;
    
    // Date formatting helper function
    function formatDate($date) {
        return date('d/m/Y', strtotime($date));
    }
    
    // Create email content
    $message = "
    <html>
    <body>
        <h2>Dear {$name},</h2>
        <p>Your appointment has been successfully created. Your appointment details are as follows:</p>
        <p><b>Appointment Details:</b></p>
        <p>Date: " . formatDate($date) . "</p>
        <p>Time: {$time}</p>
        <hr>
        <p>You can cancel your appointment by clicking the link below:</p>
        <p><a href='$cancel_link'>Cancel Appointment</a></p>
        <hr>
        <p><b>Contact Information:</b></p>
        <p>Phone: <a href='tel:+491234567890'>+491234567890</a></p>
        <p>Email: <a href='mailto:info@your-company.com'>info@your-company.com</a></p>
        <hr>
        <p>Best Regards,<br>Your Company</p>
    </body>
    </html>";

    // Send email to user
    $user_mail_sent = mail($userEmail, $subject, $message, $headers);
    
    // Send admin notification email
    $admin_subject = "New Appointment Created";
    $admin_message = "
    <html>
    <body>
        <h2>New Appointment Details</h2>
        <p><b>Name:</b> $name</p>
        <p><b>Email:</b> $userEmail</p>
        <p><b>Date:</b> $date</p>
        <p><b>Time:</b> $time</p>
    </body>
    </html>";
    
    // Send admin notification email
    $admin_mail_sent = mail($admin_email, $admin_subject, $admin_message, $headers);
    
    return $user_mail_sent && $admin_mail_sent;
}
?>
