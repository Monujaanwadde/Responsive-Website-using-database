<?php
    session_start();
    header("Content-Type: application/json");

    // DB connection
    $conn = new mysqli("localhost", "root", "", "usersdatabase"); 
    if ($conn->connect_error) 
    { 
        die(json_encode(["status" => "error", "message" => "DB connection failed"])); 
    } 

    $enteredOtp = trim($_POST['otp']);

    // Check OTP session
    if (!isset($_SESSION['otp']) || !isset($_SESSION['otp_expiry']) || !isset($_SESSION['user_data'])) {
        echo json_encode(["status" => "error", "message" => "No OTP session found"]);
        exit;
    }

    // Check OTP expired
    if (time() > $_SESSION['otp_expiry']) {
        echo json_encode(["status" => "error", "message" => "OTP expired"]);
        unset($_SESSION['otp'], $_SESSION['otp_expiry'], $_SESSION['user_data']);
        exit;
    }

    // Check OTP match
    if ($enteredOtp != $_SESSION['otp']) {
        echo json_encode(["status" => "error", "message" => "Invalid OTP"]);
        exit;
    }

    // Insert user into DB
    $userData = $_SESSION['user_data'];
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, verified) VALUES (?, ?, ?, 1)");
    $stmt->bind_param("sss", $userData['name'], $userData['email'], $userData['password']);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "OTP verified. User registered successfully."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to register user"]);
    }
    $stmt->close();

    // Clear session
    unset($_SESSION['otp'], $_SESSION['otp_expiry'], $_SESSION['user_data']);
?>