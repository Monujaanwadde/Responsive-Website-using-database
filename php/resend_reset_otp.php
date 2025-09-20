<?php
    session_start();
    header("Content-Type: application/json");
    ob_start();

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    require __DIR__.'/../PHPMailer/src/PHPMailer.php';
    require __DIR__.'/../PHPMailer/src/SMTP.php';
    require __DIR__.'/../PHPMailer/src/Exception.php';

    // DB connection
    $conn = new mysqli("localhost", "root", "", "usersdatabase"); 
    if ($conn->connect_error) 
    { 
        die(json_encode(["status" => "error", "message" => "DB connection failed"])); 
    } 

    try {
        $email = $_SESSION['reset_email'] ?? '';
        if (!$email) throw new Exception("No email found in session, please restart reset process");

        // Generate new OTP
        $otp = rand(100000, 999999);
        $_SESSION['reset_otp'] = $otp;
        $_SESSION['reset_otp_expiry'] = time() + 120; // 2 minutes

        // Send OTP via PHPMailer
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = "smtp.gmail.com";
        $mail->SMTPAuth = true;
        $mail->Username = "godjanuwadde50@gmail.com";
        $mail->Password = "tieq jnkc xsww vplm";  
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom("godjanuwadde50@gmail.com", "Reset Password OTP");
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = "Your Password Reset OTP (Resent)";
        $mail->Body = "<h2>Your new OTP is <b>$otp</b></h2><p>Valid for 2 minutes only.</p>";

        $mail->send();

        ob_end_clean();
        echo json_encode([
            "success"=>true,
            "message"=>"New OTP sent successfully",
            "email"=>$email
        ]);

    } catch(Exception $e) {
        ob_end_clean();
        echo json_encode(["success"=>false,"message"=>"Error: ".$e->getMessage()]);
    }
?>