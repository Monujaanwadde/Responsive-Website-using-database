<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Content-Type: application/json");
    echo json_encode([
        "status" => "error",
        "message" => "No active session"
    ]);
    exit;
}

// Destroy session
$_SESSION = [];
session_unset();
session_destroy();

// Clear session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Return JSON response
header("Content-Type: application/json");
echo json_encode([
    "status" => "success",
    "message" => "Logged out successfully"
]);
exit;
