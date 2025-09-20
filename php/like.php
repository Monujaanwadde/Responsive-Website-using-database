<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
  echo json_encode(['status'=>'auth_required']);
  exit;
}

$user_id = $_SESSION['user_id'];
$input = json_decode(file_get_contents('php://input'), true);
$image_id = intval($input['image_id'] ?? 0);

if ($image_id <= 0) {
  echo json_encode(['status'=>'error','message'=>'invalid image_id']);
  exit;
}

$conn = new mysqli('localhost','root','','usersdatabase');
if ($conn->connect_error) {
  echo json_encode(['status'=>'error','message'=>'DB connect failed']);
  exit;
}

// check existing
$stmt = $conn->prepare("SELECT liked FROM user_activity WHERE user_id = ? AND image_id = ?");
$stmt->bind_param('ii', $user_id, $image_id);
$stmt->execute();
$res = $stmt->get_result();

if ($row = $res->fetch_assoc()) {
  $current = (int)$row['liked'];
  $new = $current ? 0 : 1;
  $upd = $conn->prepare("UPDATE user_activity SET liked = ? WHERE user_id = ? AND image_id = ?");
  $upd->bind_param('iii', $new, $user_id, $image_id);
  $upd->execute();
  $liked = $new;
} else {
  $ins = $conn->prepare("INSERT INTO user_activity (user_id, image_id, liked) VALUES (?, ?, 1)");
  $ins->bind_param('ii', $user_id, $image_id);
  $ins->execute();
  $liked = 1;
}

// totals
$stmtL = $conn->prepare("SELECT COUNT(*) AS c FROM user_activity WHERE image_id = ? AND liked = 1");
$stmtL->bind_param('i', $image_id); $stmtL->execute();
$like_count = (int)$stmtL->get_result()->fetch_assoc()['c'];

$stmtV = $conn->prepare("SELECT COUNT(*) AS c FROM image_views WHERE image_id = ?");
$stmtV->bind_param('i', $image_id); $stmtV->execute();
$view_count = (int)$stmtV->get_result()->fetch_assoc()['c'];

echo json_encode([
  'status' => 'success',
  'liked' => (int)$liked,
  'like_count' => $like_count,
  'view_count' => $view_count
]);
