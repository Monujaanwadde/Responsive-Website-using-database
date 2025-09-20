<?php
    session_start();
    header("Content-Type: application/json");

    $email = trim($_POST['email'] ?? '');
    $otp   = trim($_POST['otp'] ?? '');

    try {
        if(empty($email) || empty($otp)){
            throw new Exception("Email and OTP are required");
        }

        if(!isset($_SESSION['reset_otp'], $_SESSION['reset_email'], $_SESSION['reset_otp_expiry'])){
            throw new Exception("OTP not generated or session expired");
        }

        if($_SESSION['reset_email'] !== $email){
            throw new Exception("Email mismatch");
        }

        if(time() > $_SESSION['reset_otp_expiry']){
            unset($_SESSION['reset_otp'], $_SESSION['reset_email'], $_SESSION['reset_otp_expiry']);
            throw new Exception("OTP expired. Please request a new one.");
        }

        if($otp != $_SESSION['reset_otp']){
            throw new Exception("Invalid OTP");
        }

        // OTP verified successfully
        $_SESSION['otp_verified'] = true;
        unset($_SESSION['reset_otp'], $_SESSION['reset_otp_expiry']);

        echo json_encode(["success"=>true,"message"=>"OTP verified successfully"]);

    } catch(Exception $e){
        echo json_encode(["success"=>false,"message"=>$e->getMessage()]);
    }
    exit;
?>