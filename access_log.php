<?php
function logAccess($type, $details = '') {
    $ip = $_SERVER['REMOTE_ADDR'];
    $forwarded_ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? 'N/A';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'N/A';
    $request_uri = $_SERVER['REQUEST_URI'];
    $timestamp = date('Y-m-d H:i:s');
    
    $log_entry = sprintf(
        "[%s] Type: %s | IP: %s | Forwarded IP: %s | URI: %s | User Agent: %s | Details: %s\n",
        $timestamp,
        $type,
        $ip,
        $forwarded_ip,
        $request_uri,
        $user_agent,
        $details
    );
    
    $log_file = 'access.log';
    file_put_contents($log_file, $log_entry, FILE_APPEND);
    
    // Suspicious activity control
    checkSuspiciousActivity($ip, $type);
}

function checkSuspiciousActivity($ip, $type) {
    $timeWindow = 300; // 5 minutes
    $maxAttempts = 10; // maximum number of attempts
    
    $recentLogs = file('access.log');
    $attempts = 0;
    $currentTime = time();
    
    foreach ($recentLogs as $log) {
        if (strpos($log, $ip) !== false && strpos($log, $type) !== false) {
            $logTime = strtotime(substr($log, 1, 19));
            if ($currentTime - $logTime <= $timeWindow) {
                $attempts++;
            }
        }
    }
    
    if ($attempts >= $maxAttempts) {
        $blockLog = sprintf(
            "[%s] BLOCKED | IP: %s | Reason: Too many attempts (%d in %d seconds)\n",
            date('Y-m-d H:i:s'),
            $ip,
            $attempts,
            $timeWindow
        );
        file_put_contents('blocked_ips.log', $blockLog, FILE_APPEND);
    }
}

// Check if IP is blocked
function isIPBlocked($ip) {
    if (!file_exists('blocked_ips.log')) {
        return false;
    }
    
    $blockedLogs = file('blocked_ips.log');
    $blockDuration = 3600; // 1 hour block duration
    $currentTime = time();
    
    foreach ($blockedLogs as $log) {
        if (strpos($log, $ip) !== false) {
            $blockTime = strtotime(substr($log, 1, 19));
            if ($currentTime - $blockTime <= $blockDuration) {
                return true;
            }
        }
    }
    
    return false;
}
?> 