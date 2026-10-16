<?php

session_start();
include "db.php";

header("Content-Type: application/json");

if (!isset($_SESSION["user_id"])) {
    echo json_encode([
        "success" => false,
        "message" => "Not logged in"
    ]);
    exit();
}

$user_id = $_SESSION["user_id"];

if (!isset($_GET["post_id"])) {
    echo json_encode([
        "success" => false,
        "message" => "Post ID missing"
    ]);
    exit();
}

$post_id = intval($_GET["post_id"]);


/* Get total likes */
$count_sql = "SELECT COUNT(*) AS total_likes
              FROM likes
              WHERE post_id=?";

$count_stmt = $conn->prepare($count_sql);
$count_stmt->bind_param("i", $post_id);
$count_stmt->execute();

$count_result = $count_stmt->get_result();
$count_data = $count_result->fetch_assoc();

$total_likes = $count_data["total_likes"];


/* Check if current user liked this post */
$check_sql = "SELECT id
              FROM likes
              WHERE post_id=? AND user_id=?";

$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("ii", $post_id, $user_id);
$check_stmt->execute();

$check_result = $check_stmt->get_result();

$liked = $check_result->num_rows > 0;


/* Send response */
echo json_encode([
    "success" => true,
    "likes" => $total_likes,
    "liked" => $liked
]);

?>