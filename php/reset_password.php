<?php
    session_start();
    header("Content-Type: application/json");

    // DB connection
    $conn = new mysqli("localhost", "root", "", "usersdatabase"); 
    if ($conn->connect_error) 
    { 
        die(json_encode(["status" => "error", "message" => "DB connection failed"])); 
    } 

    // Ensure OTP was verified first
    if(!isset($_SESSION['otp_verified']) || $_SESSION['otp_verified'] !== true){
        echo json_encode(["status"=>"error","message"=>"Unauthorized access. Verify OTP first."]);
        exit;
    }

    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';

    if(empty($password) || empty($confirmPassword)){
        echo json_encode(["status"=>"error","message"=>"All fields are required."]);
        exit;
    }

    if($password !== $confirmPassword){
        echo json_encode(["status"=>"error","message"=>"Passwords do not match."]);
        exit;
    }

    // Strong password check
    if(!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@#$%^&+=!]).{8,}$/",$password)){
        echo json_encode(["status"=>"error","message"=>"Password is not strong enough."]);
        exit;
    }

    // Update password in DB (hashed)
    $email = $_SESSION['reset_email'] ?? '';
    if(!$email){
        echo json_encode(["status"=>"error","message"=>"Email not found in session."]);
        exit;
    }

    $hashed = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("UPDATE users SET password=? WHERE email=?");
    $stmt->bind_param("ss", $hashed, $email);

    if($stmt->execute()){
        // Clear session after reset
        unset($_SESSION['otp_verified']);
        unset($_SESSION['reset_email']);
        echo json_encode(["status"=>"success","message"=>"Password updated successfully."]);
    }else{
        echo json_encode(["status"=>"error","message"=>"Failed to update password."]);
    }
    $stmt->close();
    $conn->close();
?>