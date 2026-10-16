
<?php
    header('Content-Type: application/json; charset=utf-8');

    $sessionTimeout = 1800; // Testing only: 3 minutes
    $now = time();

    $hasUser = isset($_SESSION['userid']) && !empty($_SESSION['userid']);
    $lastActivity = isset($_SESSION['LAST_ACTIVITY'])
        ? (int) $_SESSION['LAST_ACTIVITY']
        : 0;

    $elapsed = ($lastActivity > 0) ? ($now - $lastActivity) : null;

    $debug = array(
        'server_time' => date('Y-m-d H:i:s', $now),
        'last_activity' => ($lastActivity > 0)
            ? date('Y-m-d H:i:s', $lastActivity)
            : null,
        'seconds_since_activity' => $elapsed,
        'timeout_seconds' => $sessionTimeout
    );

    if (!$hasUser || $lastActivity == 0 || $elapsed >= $sessionTimeout) {
        http_response_code(401);

        echo json_encode(array(
            'status' => 'session_expired',
            'message' => 'Session is missing or expired.',
            'debug' => $debug
        ));
        exit;
    }

    $action = isset($_POST['action']) ? $_POST['action'] : 'check';

    if ($action == 'refresh') {
        $_SESSION['LAST_ACTIVITY'] = $now;

        $debug['last_activity'] = date('Y-m-d H:i:s', $_SESSION['LAST_ACTIVITY']);
        $debug['seconds_since_activity'] = 0;
    }

    echo json_encode(array(
        'status' => 'ok',
        'action' => $action,
        'debug' => $debug
    ));
exit;

?>
