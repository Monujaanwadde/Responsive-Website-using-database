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
        $email = trim($_POST['email'] ?? '');
        if(!$email) throw new Exception("Email is required");

        // Check email exists
        $stmt=$conn->prepare("SELECT id FROM users WHERE email=?");
        $stmt->bind_param("s",$email);
        $stmt->execute();
        $stmt->store_result();
        if($stmt->num_rows==0) throw new Exception("Email not found");
        $stmt->close();

        // Generate OTP
        $otp = rand(100000,999999);
        $_SESSION['reset_email']=$email;
        $_SESSION['reset_otp']=$otp;
        $_SESSION['reset_otp_expiry']=time()+120;

        // Send OTP
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host="smtp.gmail.com";
        $mail->SMTPAuth=true;
        $mail->Username="godjanuwadde50@gmail.com"; 
        $mail->Password="tieq jnkc xsww vplm";  
        $mail->SMTPSecure=PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port=587;

        $mail->setFrom("godjanuwadde50@gmail.com","Reset Password OTP");
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject="Your Password Reset OTP";
        $mail->Body="<h2>Your OTP is <b>$otp</b></h2><p>Valid for 2 minutes only.</p>";
        $mail->send();

        ob_end_clean();
        echo json_encode([
            "success"=>true,
            "message"=>"OTP sent successfully",
            "email"=>$email
        ]);

    } catch(Exception $e){
        ob_end_clean();
        echo json_encode(["success"=>false,"message"=>"Error: ".$e->getMessage()]);
    }
?>