<?php
ob_start();
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'db.php';
require_once 'access_log.php';

// IP control
$ip = $_SERVER['REMOTE_ADDR'];
if (isIPBlocked($ip)) {
    logAccess('BLOCKED_LOGIN_ATTEMPT', 'IP is blocked');
    die(json_encode(['success' => false, 'error' => 'Too many failed attempts. Please try again later.']));
}

// POST data
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

try {
    // Check admin information
    $stmt = $db->prepare("SELECT * FROM admin WHERE username = ? AND password = ?");
    $stmt->execute([$username, md5($password)]);
    
    if ($stmt->rowCount() > 0) {
        // Successful login
        $_SESSION['admin'] = true;
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        
        logAccess('SUCCESSFUL_LOGIN', "Username: $username");
        
        header("Location: admin-panel.php");
        exit();
    } else {
        // Failed login
        logAccess('FAILED_LOGIN', "Username: $username");
        
        header("Location: admin-login.html?error=1");
        exit();
    }
} catch(PDOException $e) {
    logAccess('ERROR', "Database error: " . $e->getMessage());
    die("Database error: " . $e->getMessage());
}

ob_end_flush();
?> 