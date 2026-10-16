
<?php
// Start or resume the existing PHP session.
if (session_id() == '') {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');

// TESTING: 180 seconds = 3 minutes
// PRODUCTION: change to 1800 seconds = 30 minutes
$sessionTimeout = 1800; // 30 minutes; use 120 for testing

$now = time();

$hasUser = isset($_SESSION['userid']) &&
           !empty($_SESSION['userid']);

$lastActivity = isset($_SESSION['LAST_ACTIVITY'])
    ? (int) $_SESSION['LAST_ACTIVITY']
    : 0;

$elapsed = ($lastActivity > 0)
    ? ($now - $lastActivity)
    : null;

$debug = array(
    'server_time' => date('Y-m-d H:i:s', $now),
    'last_activity' => ($lastActivity > 0)
        ? date('Y-m-d H:i:s', $lastActivity)
        : null,
    'seconds_since_activity' => $elapsed,
    'timeout_seconds' => $sessionTimeout
);

// Reject missing or expired sessions.
if (
    !$hasUser ||
    $lastActivity == 0 ||
    $elapsed >= $sessionTimeout
) {
    http_response_code(401);

    echo json_encode(array(
        'status' => 'session_expired',
        'message' => 'Session is missing or expired.',
        'debug' => $debug
    ));

    exit;
}

$action = isset($_POST['action'])
    ? $_POST['action']
    : 'check';

// Refresh only when explicitly requested.
if ($action == 'refresh') {
    $_SESSION['LAST_ACTIVITY'] = $now;

    $debug['last_activity'] = date(
        'Y-m-d H:i:s',
        $_SESSION['LAST_ACTIVITY']
    );

    $debug['seconds_since_activity'] = 0;
}

echo json_encode(array(
    'status' => 'ok',
    'action' => $action,
    'debug' => $debug
));

exit;
?>