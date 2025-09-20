<?php
session_start();
header('Content-Type: application/json');

$user_id = $_SESSION['user_id'] ?? null;
$input = json_decode(file_get_contents('php://input'), true);
$image_ids = $input['image_ids'] ?? [];

if (!is_array($image_ids) || count($image_ids) === 0) {
  echo json_encode(['status'=>'error','message'=>'no image_ids']);
  exit;
}

$conn = new mysqli('localhost','root','','usersdatabase');
if ($conn->connect_error) {
  echo json_encode(['status'=>'error','message'=>'DB connect failed']);
  exit;
}

$stmtLikes = $conn->prepare("SELECT COUNT(*) AS cnt FROM user_activity WHERE image_id = ? AND liked = 1");
$stmtViews = $conn->prepare("SELECT COUNT(*) AS cnt FROM image_views WHERE image_id = ?");
$stmtUser  = $conn->prepare("SELECT liked FROM user_activity WHERE user_id = ? AND image_id = ?");

$stats = [];

foreach ($image_ids as $id) {
  $id = intval($id);

  $stmtLikes->bind_param('i', $id);
  $stmtLikes->execute();
  $likeCount = (int)$stmtLikes->get_result()->fetch_assoc()['cnt'];

  $stmtViews->bind_param('i', $id);
  $stmtViews->execute();
  $viewCount = (int)$stmtViews->get_result()->fetch_assoc()['cnt'];

  $liked = 0;
  if ($user_id) {
    $stmtUser->bind_param('ii', $user_id, $id);
    $stmtUser->execute();
    $r = $stmtUser->get_result()->fetch_assoc();
    if ($r) $liked = (int)$r['liked'];
  }

  $stats[$id] = [
    'like_count' => $likeCount,
    'view_count' => $viewCount,
    'liked'      => $liked
  ];
}

echo json_encode(['status'=>'success','stats'=>$stats]);
