<?php
session_start();

// Authorization control
if (!isset($_SESSION['admin'])) {
    header("Location: admin-login.html");
    exit;
}

// Get log type (access or blocked)
$logType = $_GET['type'] ?? 'access';
$logFile = $logType === 'blocked' ? 'blocked_ips.log' : 'access.log';

// Filter parameters
$dateFilter = $_GET['date'] ?? '';
$ipFilter = $_GET['ip'] ?? '';
$typeFilter = $_GET['activity_type'] ?? '';

// Read log file
$logs = file_exists($logFile) ? file($logFile) : [];

// Apply filter
if ($dateFilter || $ipFilter || $typeFilter) {
    $logs = array_filter($logs, function($log) use ($dateFilter, $ipFilter, $typeFilter) {
        $match = true;
        
        if ($dateFilter && strpos($log, $dateFilter) === false) {
            $match = false;
        }
        
        if ($ipFilter && strpos($log, $ipFilter) === false) {
            $match = false;
        }
        
        if ($typeFilter && strpos($log, "Type: $typeFilter") === false) {
            $match = false;
        }
        
        return $match;
    });
}

// Show last 1000 logs (for performance)
$logs = array_slice($logs, -1000);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log View</title>
    <link rel="stylesheet" href="admin-style.css">
    <style>
        .log-container {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }
        
        .log-entry {
            font-family: monospace;
            padding: 10px;
            border-bottom: 1px solid #dee2e6;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        
        .log-entry:nth-child(odd) {
            background: #f1f3f4;
        }
        
        .filter-form {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .filter-form input, .filter-form select {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            flex: 1;
        }
        
        .log-type-tabs {
            margin-bottom: 20px;
        }
        
        .log-type-tabs a {
            padding: 10px 20px;
            text-decoration: none;
            color: #666;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-right: 10px;
        }
        
        .log-type-tabs a.active {
            background: #007bff;
            color: white;
            border-color: #007bff;
        }
        
        .suspicious {
            color: #dc3545;
            font-weight: bold;
        }
        
        .success {
            color: #28a745;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <div class="header">
            <h2>Log View</h2>
            <a href="admin-panel.php" class="back-button">← Admin Panel</a>
        </div>
        
        <div class="log-type-tabs">
            <a href="?type=access" class="<?php echo $logType === 'access' ? 'active' : ''; ?>">Access Logs</a>
            <a href="?type=blocked" class="<?php echo $logType === 'blocked' ? 'active' : ''; ?>">Blocked IPs</a>
        </div>
        
        <form class="filter-form" method="GET">
            <input type="hidden" name="type" value="<?php echo htmlspecialchars($logType); ?>">
            <input type="date" name="date" value="<?php echo htmlspecialchars($dateFilter); ?>" placeholder="Date Filter">
            <input type="text" name="ip" value="<?php echo htmlspecialchars($ipFilter); ?>" placeholder="IP Address Filter">
            <?php if ($logType === 'access'): ?>
            <select name="activity_type">
                <option value="">All Activity</option>
                <option value="FAILED_LOGIN" <?php echo $typeFilter === 'FAILED_LOGIN' ? 'selected' : ''; ?>>Login Failed</option>
                <option value="SUCCESSFUL_LOGIN" <?php echo $typeFilter === 'SUCCESSFUL_LOGIN' ? 'selected' : ''; ?>>Login Successful</option>
                <option value="BLOCKED_LOGIN_ATTEMPT" <?php echo $typeFilter === 'BLOCKED_LOGIN_ATTEMPT' ? 'selected' : ''; ?>>BLOCKED</option>
                <option value="ERROR" <?php echo $typeFilter === 'ERROR' ? 'selected' : ''; ?>>Erros</option>
            </select>
            <?php endif; ?>
            <button type="submit">Filter</button>
        </form>
        
        <div class="log-container">
            <?php if (empty($logs)): ?>
                <p>Log not found.</p>
            <?php else: ?>
                <?php foreach ($logs as $log): ?>
                    <?php
                    $logClass = '';
                    if (strpos($log, 'FAILED_LOGIN') !== false || strpos($log, 'BLOCKED') !== false) {
                        $logClass = 'suspicious';
                    } elseif (strpos($log, 'SUCCESSFUL_LOGIN') !== false) {
                        $logClass = 'success';
                    }
                    ?>
                    <div class="log-entry <?php echo $logClass; ?>">
                        <?php echo htmlspecialchars($log); ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html> 