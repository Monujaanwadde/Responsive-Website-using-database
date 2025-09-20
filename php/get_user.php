<?php
session_start();


// DB connect
    $conn = new mysqli('localhost','root','','usersdatabase');
    if ($conn->connect_error) {
        echo json_encode(['status'=>'error','message'=>'DB connect failed']);
        exit;
    }


// User is logged in with session
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "User not logged in"]);
    exit;
}

$user_id = $_SESSION['user_id'];
$sql = "SELECT name, email FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode([
        "status" => "success",
        "name"   => $row['name'],
        "email"  => $row['email']
    ]);
} else {
    echo json_encode(["status" => "error", "message" => "User not found"]);
}
?>