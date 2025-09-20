<?php
    session_start();
    header("Content-Type: application/json");

    // DB connect
    $conn = new mysqli('localhost','root','','usersdatabase');
    if ($conn->connect_error) {
        echo json_encode(['status'=>'error','message'=>'DB connect failed']);
        exit;
    }

    $user_id = $_SESSION['user_id'] ?? 0;

    $sql = "SELECT p.id, p.filename, 
                IFNULL(s.total_views, 0) AS total_views, 
                IFNULL(s.total_likes, 0) AS total_likes,
                IF(l.id IS NULL, 0, 1) AS liked
            FROM photos p
            LEFT JOIN photo_stats s ON p.id = s.photo_id
            LEFT JOIN likes l ON l.photo_id = p.id AND l.user_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $photos = [];
    while ($row = $result->fetch_assoc()) {
        $photos[] = $row;
    }

    echo json_encode($photos);
?>