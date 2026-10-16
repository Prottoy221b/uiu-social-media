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


if (!isset($_GET["group_id"])) {

    echo json_encode([
        "success" => false,
        "message" => "Group ID missing"
    ]);

    exit();
}


$group_id = intval($_GET["group_id"]);


/* =========================
   LOAD GROUP POSTS
========================= */

$sql = "
    SELECT
        posts.id,
        posts.user_id,
        posts.content,
        posts.post_type,
        posts.created_at,
        users.name

    FROM posts

    JOIN users
    ON posts.user_id = users.id

    WHERE posts.group_id = ?

    ORDER BY posts.created_at DESC
";


$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "i",
    $group_id
);

$stmt->execute();

$result = $stmt->get_result();


$posts = [];


while ($post = $result->fetch_assoc()) {

    $posts[] = [

        "id" => $post["id"],

        "user_id" => $post["user_id"],

        "name" => $post["name"],

        "content" => $post["content"],

        "post_type" => $post["post_type"],

        "created_at" => $post["created_at"]

    ];
}


echo json_encode([

    "success" => true,

    "posts" => $posts

]);

?>