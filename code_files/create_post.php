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


if (!isset($_POST["content"])) {

    echo json_encode([
        "success" => false,
        "message" => "Post content missing"
    ]);

    exit();
}

$content = trim($_POST["content"]);


if ($content === "") {

    echo json_encode([
        "success" => false,
        "message" => "Post cannot be empty"
    ]);

    exit();
}


/* Insert post */

$sql = "INSERT INTO posts
        (user_id, content, post_type, created_at)
        VALUES (?, ?, 'text', NOW())";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "is",
    $user_id,
    $content
);


if (!$stmt->execute()) {

    echo json_encode([
        "success" => false,
        "message" => "Failed to create post"
    ]);

    exit();
}


/* Get user name */

$user_sql = "SELECT name FROM users WHERE id=?";

$user_stmt = $conn->prepare($user_sql);

$user_stmt->bind_param(
    "i",
    $user_id
);

$user_stmt->execute();

$user_result = $user_stmt->get_result();

$user = $user_result->fetch_assoc();


/* Return new post information */

echo json_encode([

    "success" => true,

    "post" => [

        "id" => $stmt->insert_id,

        "name" => $user["name"],

        "content" => $content

    ]

]);

?>