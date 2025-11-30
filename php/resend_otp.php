<?php
    session_start();
    header("Content-Type: application/json");

    if (!isset($_SESSION['user_data'])) {
        echo json_encode(["status" => "error", "message" => "No user data available"]);
        exit;
    }

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    // PHPMailer
    require __DIR__ . '/../PHPMailer/src/Exception.php';
    require __DIR__ . '/../PHPMailer/src/PHPMailer.php';
    require __DIR__ . '/../PHPMailer/src/SMTP.php';

    // Generate new OTP
    $otp = rand(100000, 999999);
    $_SESSION['otp'] = $otp;
    $_SESSION['otp_expiry'] = time() + 120; // 2 minutes

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = "smtp.gmail.com";
        $mail->SMTPAuth = true;
        $mail->Username = "your_email_address";
        $mail->Password = "email_app_password";
        $mail->SMTPSecure = "tls";
        $mail->Port = 587;

        $mail->setFrom("your_email_address", "OTP Verification");
        $mail->addAddress($_SESSION['user_data']['email']);

        $mail->isHTML(true);
        $mail->Subject = "Your New OTP Code";
        $mail->Body = "<h2>Your new OTP is <b>$otp</b></h2><p>It is valid for 2 minutes only.</p>";

        $mail->send();
        echo json_encode(["status" => "success", "message" => "New OTP sent successfully"]);
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => "OTP could not be sent. Mailer Error: {$mail->ErrorInfo}"]);
    }
?>
