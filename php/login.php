<?php session_start(); 
header("Content-Type: application/json"); 

$conn = new mysqli("localhost", "root", "", "usersdatabase"); 
if ($conn->connect_error) 
{ 
    die(json_encode(["status" => "error", "message" => "DB connection failed"])); 
} 

$email = trim($_POST['email']); 
$password = trim($_POST['password']); 

$stmt = $conn->prepare("SELECT id, password, verified FROM users WHERE email = ?"); 
$stmt->bind_param("s", $email); 
$stmt->execute();
$result = $stmt->get_result(); 
if ($result->num_rows === 0) { 
    echo json_encode(["status" => "error", "message" => "Email not found"]); 
    exit; 
} 
$user = $result->fetch_assoc();  
if ($user['verified'] == 0) { 
    echo json_encode(["status" => "error", "message" => "Account not verified"]); 
    exit; 
} 
if (!password_verify($password, $user['password'])) { 
    echo json_encode(["status" => "error", "message" => "Invalid password"]); 
    exit; 
} 
 
$_SESSION['user_id'] = $user['id']; 
    echo json_encode([ "status" => "success", "message" => "Login successful", "user_id" => $user['id'] ]); 
?>