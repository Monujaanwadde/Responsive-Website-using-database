<?php
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    require __DIR__.'/../PHPMailer/src/PHPMailer.php';
    require __DIR__.'/../PHPMailer/src/SMTP.php';
    require __DIR__.'/../PHPMailer/src/Exception.php';


    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $name    = htmlspecialchars($_POST['name']);
        $email   = htmlspecialchars($_POST['email']);
        $subject = htmlspecialchars($_POST['subject']);
        $message = htmlspecialchars($_POST['message']);

        $mail = new PHPMailer(true);

        try {
            // SMTP settings
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = "your_email_address"; 
            $mail->Password   = "email_app_password";      
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;

            // Sender & Receiver
            $mail->setFrom('your_email_address', 'Portfolio Contact');
            $mail->addAddress('your_email_address'); 
            $mail->addReplyTo($email, $name);

            // Email content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = "
                <h3>New Contact Form Submission</h3>
                <p><b>Name:</b> {$name}</p>
                <p><b>Email:</b> {$email}</p>
                <p><b>Message:</b><br>{$message}</p>
            ";

            $mail->send();
            echo "<p style='color:green;'>Message sent successfully!</p>";
        } catch (Exception $e) {
            echo "<p style='color:red;'>Message failed. Error: {$mail->ErrorInfo}</p>";
        }
    }
?>
