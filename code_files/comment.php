
<?php

session_start();
include "db.php";

header("Content-Type: application/json");


/* =========================
   CHECK LOGIN
========================= */

if (!isset($_SESSION["user_id"])) {

    echo json_encode([
        "success" => false,
        "message" => "Not logged in"
    ]);

    exit();
}

$user_id = $_SESSION["user_id"];


/* =========================
   CHECK INPUT
========================= */

if (!isset($_POST["post_id"]) || !isset($_POST["content"])) {

    echo json_encode([
        "success" => false,
        "message" => "Missing data"
    ]);

    exit();
}

$post_id = intval($_POST["post_id"]);
$content = trim($_POST["content"]);


if ($content === "") {

    echo json_encode([
        "success" => false,
        "message" => "Comment cannot be empty"
    ]);

    exit();
}


/* =========================
   GET POST OWNER
========================= */

$post_sql = "SELECT user_id
             FROM posts
             WHERE id = ?";

$post_stmt = $conn->prepare($post_sql);
$post_stmt->bind_param("i", $post_id);
$post_stmt->execute();

$post_result = $post_stmt->get_result();
$post = $post_result->fetch_assoc();

$post_stmt->close();


if (!$post) {

    echo json_encode([
        "success" => false,
        "message" => "Post not found"
    ]);

    exit();
}

$post_owner_id = (int) $post["user_id"];


/* =========================
   INSERT COMMENT
========================= */

$sql = "INSERT INTO comments
        (user_id, post_id, content, created_at)
        VALUES (?, ?, ?, NOW())";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "iis",
    $user_id,
    $post_id,
    $content
);

$stmt->execute();

$comment_id = $stmt->insert_id;

$stmt->close();


/* =========================
   GET COMMENT AUTHOR NAME
========================= */

$user_sql = "SELECT name
             FROM users
             WHERE id = ?";

$user_stmt = $conn->prepare($user_sql);

$user_stmt->bind_param(
    "i",
    $user_id
);

$user_stmt->execute();

$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();

$user_stmt->close();


$actor_name = $user["name"];


/* =========================
   CREATE COMMENT NOTIFICATION
========================= */

/*
   Do not notify yourself when
   commenting on your own post.
*/

if ($post_owner_id !== (int) $user_id) {

    $notification_type = "comment";

    $message = $actor_name . " commented on your post.";

    $is_read = 0;

    $notification_sql = "INSERT INTO notifications
                         (user_id, notification_type, message, is_read, created_at)
                         VALUES (?, ?, ?, ?, NOW())";

    $notification_stmt = $conn->prepare($notification_sql);

    $notification_stmt->bind_param(
        "issi",
        $post_owner_id,
        $notification_type,
        $message,
        $is_read
    );

    $notification_stmt->execute();

    $notification_stmt->close();
}


/* =========================
   GET UPDATED COMMENT COUNT
========================= */

$count_sql = "SELECT COUNT(*) AS total_comments
              FROM comments
              WHERE post_id = ?";

$count_stmt = $conn->prepare($count_sql);

$count_stmt->bind_param(
    "i",
    $post_id
);

$count_stmt->execute();

$count_result = $count_stmt->get_result();
$count_data = $count_result->fetch_assoc();

$count_stmt->close();


/* =========================
   SEND RESPONSE
========================= */

echo json_encode([

    "success" => true,

    "comment" => [

        "id" => $comment_id,

        "name" => $actor_name,

        "content" => $content

    ],

    "comment_count" => $count_data["total_comments"]

]);

?>