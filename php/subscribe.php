<?php
  session_start();

  // DB connection
    $conn = new mysqli("localhost", "root", "", "usersdatabase"); 
    if ($conn->connect_error) 
    { 
        die(json_encode(["status" => "error", "message" => "DB connection failed"])); 
    } 

  // User is logged in and we stored user_id in session
  $user_id = $_SESSION['user_id'] ?? null;

  if (!$user_id) {
    die("You must be logged in to subscribe.");
  }

  // Fetch user details
  $stmt = $conn->prepare("SELECT name, email FROM users WHERE id = ?");
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $stmt->bind_result($name, $email);
  $stmt->fetch();
  $stmt->close();
  $conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Subscribe</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: "Poppins", sans-serif;
      background: linear-gradient(to right, #c60073, #0004ff);
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin: 0;
      color: #fff;
    }
    .subscribe-box {
      background: rgba(0,0,0,0.6);
      padding: 30px;
      border-radius: 12px;
      text-align: center;
      width: 350px;
    }
    .subscribe-box h2 {
      margin-bottom: 20px;
    }
    .info {
      margin: 15px 0;
      font-size: 18px;
    }
    .btn {
      background: crimson;
      color: white;
      padding: 12px;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      width: 100%;
      font-size: 16px;
    }
    .btn:hover {
      background: #ff3366;
    }
  </style>
</head>
<body>

  <div class="subscribe-box">
    <h2>Subscribe Now</h2>
    <div class="info"><strong>Name:</strong> <?= htmlspecialchars($name) ?></div>
    <div class="info"><strong>Email:</strong> <?= htmlspecialchars($email) ?></div>
    <form action="finalize_subscribe.php" method="POST">
      <input type="hidden" name="name" value="<?= htmlspecialchars($name) ?>">
      <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
      <button type="submit" class="btn">Confirm Subscribe</button>
    </form>
  </div>

</body>
</html>