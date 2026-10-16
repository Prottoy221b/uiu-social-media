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

if (!isset($_GET["post_id"])) {
    echo json_encode([
        "success" => false,
        "message" => "Post ID missing"
    ]);
    exit();
}

$post_id = intval($_GET["post_id"]);

$sql = "SELECT 
            comments.id,
            comments.content,
            comments.created_at,
            users.name
        FROM comments
        JOIN users ON comments.user_id = users.id
        WHERE comments.post_id = ?
        ORDER BY comments.created_at ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $post_id);
$stmt->execute();

$result = $stmt->get_result();

$comments = [];

while ($row = $result->fetch_assoc()) {

    $comments[] = [
        "id" => $row["id"],
        "name" => $row["name"],
        "content" => $row["content"],
        "created_at" => $row["created_at"]
    ];

}

echo json_encode([
    "success" => true,
    "comments" => $comments
]);

?>