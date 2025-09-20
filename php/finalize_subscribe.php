<?php
  session_start();

  use PHPMailer\PHPMailer\PHPMailer;
  use PHPMailer\PHPMailer\Exception;

  // Load PHPMailer
  require __DIR__ . '/../PHPMailer/src/PHPMailer.php';
  require __DIR__ . '/../PHPMailer/src/SMTP.php';
  require __DIR__ . '/../PHPMailer/src/Exception.php';


  $name  = $_POST['name'] ?? '';
  $email = $_POST['email'] ?? '';

  if (empty($name) || empty($email)) {
      die("Invalid request. Name or email missing.");
  }

  // Check if already subscribed
  $alreadySubscribed = false;
  $check = $conn->prepare("SELECT id FROM subscribers WHERE email = ?");
  $check->bind_param("s", $email);
  $check->execute();
  $check->store_result();
  if ($check->num_rows > 0) {
      $alreadySubscribed = true;
  }
  $check->close();

  // Insert or update subscriber
  $stmt = $conn->prepare("INSERT INTO subscribers (name, email) VALUES (?, ?) 
                          ON DUPLICATE KEY UPDATE subscribed_at = CURRENT_TIMESTAMP, name = VALUES(name)");
  $stmt->bind_param("ss", $name, $email);

  if ($stmt->execute()) {
      // Ensure stats row exists
      $conn->query("INSERT INTO subscribers_stats (id, total) VALUES (1, 0)
                    ON DUPLICATE KEY UPDATE id = id");

      if (!$alreadySubscribed) {
          // Only increase total if it's a new subscriber
          $conn->query("UPDATE subscribers_stats SET total = total + 1 WHERE id = 1");
      }

      // Fetch updated total
      $result = $conn->query("SELECT total FROM subscribers_stats WHERE id = 1");
      $totalSubscribers = 0;
      if ($result && $row = $result->fetch_assoc()) {
          $totalSubscribers = $row['total'];
      }

      if ($alreadySubscribed) {
          $message = "Welcome back, $name! You are already subscribed.<br>Total Subscribers: $totalSubscribers";
      } else {
          $message = "Thank you, $name! You have successfully subscribed.<br>Total Subscribers: $totalSubscribers";

          // Send emails using PHPMailer
          $mail = new PHPMailer(true);
          try {
              // SMTP settings
              $mail->isSMTP();
              $mail->Host       = 'smtp.gmail.com';
              $mail->SMTPAuth   = true;
              $mail->Username   = "godjanuwadde50@gmail.com"; 
              $mail->Password   = "tieq jnkc xsww vplm";    
              $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
              $mail->Port       = 587;

              // ---------- Send to User ----------
              $mail->setFrom("godjanuwadde50@gmail.com", "My Website");
              $mail->addAddress($email, $name);
              $mail->Subject = "Subscription Confirmation";
              $mail->Body    = "Hello $name,\n\nThank you for subscribing! 🎉\n\nRegards,\nMy Website Team";
              $mail->send();

              // ---------- Send to Admin ----------
              $mail->clearAddresses();
              $mail->addAddress("godjanuwadde50@gmail.com", "Admin");
              $mail->Subject = "New Subscriber Alert";
              $mail->Body    = "A new subscriber joined:\n\nName: $name\nEmail: $email\n\nTotal Subscribers: $totalSubscribers";
              $mail->send();
          } catch (Exception $e) {
              $message .= "<br>Email could not be sent. Error: {$mail->ErrorInfo}";
          }
      }

  } else {
      $message = "Error: " . $stmt->error;
  }

  $stmt->close();
  $conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Subscription Status</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: "Poppins", sans-serif;
      background: linear-gradient(to right, #43cea2, #185a9d);
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin: 0;
      color: #fff;
      text-align: center;
    }
    .message-box {
      background: rgba(0,0,0,0.6);
      padding: 30px;
      border-radius: 12px;
      max-width: 450px;
    }
    h2 {
      margin-bottom: 15px;
    }
    a {
      display: inline-block;
      margin-top: 20px;
      padding: 10px 20px;
      border-radius: 6px;
      background: crimson;
      color: #fff;
      text-decoration: none;
    }
    a:hover {
      background: #ff3366;
    }
  </style>
</head>
<body>

  <div class="message-box">
    <h2><?= $message ?></h2>
    <a href="../html/home.html">⬅ Back to Home</a>
  </div>

</body>
</html>
