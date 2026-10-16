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

$current_user_id = $_SESSION["user_id"];


/* =========================
   CHECK DATA
========================= */

if (!isset($_POST["group_id"]) || !isset($_POST["content"])) {

    echo json_encode([
        "success" => false,
        "message" => "Missing data"
    ]);

    exit();
}

$group_id = intval($_POST["group_id"]);
$content = trim($_POST["content"]);


if ($content === "") {

    echo json_encode([
        "success" => false,
        "message" => "Post cannot be empty"
    ]);

    exit();
}


/* =========================
   CHECK MEMBERSHIP
========================= */

$member_sql = "
    SELECT id
    FROM group_members
    WHERE group_id = ?
    AND user_id = ?
";

$member_stmt = $conn->prepare($member_sql);

$member_stmt->bind_param(
    "ii",
    $group_id,
    $current_user_id
);

$member_stmt->execute();

$member_result = $member_stmt->get_result();


if ($member_result->num_rows === 0) {

    echo json_encode([
        "success" => false,
        "message" => "You must join this group before posting"
    ]);

    exit();
}


/* =========================
   CREATE POST
========================= */

$insert_sql = "
    INSERT INTO posts
    (user_id, group_id, content, post_type, created_at)
    VALUES (?, ?, ?, 'group', NOW())
";

$insert_stmt = $conn->prepare($insert_sql);

$insert_stmt->bind_param(
    "iis",
    $current_user_id,
    $group_id,
    $content
);


if (!$insert_stmt->execute()) {

    echo json_encode([
        "success" => false,
        "message" => "Failed to create post"
    ]);

    exit();
}


/* =========================
   GET USER NAME
========================= */

$user_sql = "
    SELECT name
    FROM users
    WHERE id = ?
";

$user_stmt = $conn->prepare($user_sql);

$user_stmt->bind_param(
    "i",
    $current_user_id
);

$user_stmt->execute();

$user_result = $user_stmt->get_result();

$user = $user_result->fetch_assoc();


/* =========================
   RESPONSE
========================= */

echo json_encode([

    "success" => true,

    "post" => [

        "id" => $insert_stmt->insert_id,

        "user_id" => $current_user_id,

        "name" => $user["name"],

        "content" => $content,

        "created_at" => date("Y-m-d H:i:s")

    ]

]);

?>