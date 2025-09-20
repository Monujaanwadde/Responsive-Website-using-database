<?php
session_start();
header('Content-Type: application/json');

$user_id = $_SESSION['user_id'] ?? null;
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

// record view only for logged in users
if ($user_id) {
  $ins = $conn->prepare("INSERT INTO image_views (user_id, image_id) VALUES (?, ?)");
  $ins->bind_param('ii', $user_id, $image_id);
  $ins->execute();
}

// totals
$stmtL = $conn->prepare("SELECT COUNT(*) AS c FROM user_activity WHERE image_id = ? AND liked = 1");
$stmtL->bind_param('i', $image_id); $stmtL->execute();
$like_count = (int)$stmtL->get_result()->fetch_assoc()['c'];

$stmtV = $conn->prepare("SELECT COUNT(*) AS c FROM image_views WHERE image_id = ?");
$stmtV->bind_param('i', $image_id); $stmtV->execute();
$view_count = (int)$stmtV->get_result()->fetch_assoc()['c'];

// user liked status (if logged in)
$liked = 0;
if ($user_id) {
  $stmtU = $conn->prepare("SELECT liked FROM user_activity WHERE user_id = ? AND image_id = ?");
  $stmtU->bind_param('ii', $user_id, $image_id); $stmtU->execute();
  $r = $stmtU->get_result()->fetch_assoc();
  if ($r) $liked = (int)$r['liked'];
}

// If user not logged-in tell client it's auth_required but still return counts
if (!$user_id) {
  echo json_encode(['status'=>'auth_required','like_count'=>$like_count,'view_count'=>$view_count,'liked'=>0]);
  exit;
}

echo json_encode(['status'=>'success','like_count'=>$like_count,'view_count'=>$view_count,'liked'=>$liked]);
