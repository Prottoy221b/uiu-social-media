
<?php

session_start();
include "db.php";

header("Content-Type: application/json");


// ==========================================
// CHECK LOGIN
// ==========================================

if (!isset($_SESSION["user_id"])) {

    echo json_encode([
        "success" => false,
        "message" => "Not logged in"
    ]);

    exit();

}

$user_id = (int) $_SESSION["user_id"];


// ==========================================
// VALIDATE POST ID
// ==========================================

if (!isset($_POST["post_id"])) {

    echo json_encode([
        "success" => false,
        "message" => "Post ID missing"
    ]);

    exit();

}

$post_id = (int) $_POST["post_id"];

if ($post_id <= 0) {

    echo json_encode([
        "success" => false,
        "message" => "Invalid post ID"
    ]);

    exit();

}


// ==========================================
// GET POST OWNER
// ==========================================

$post_sql = "SELECT user_id
             FROM posts
             WHERE id = ?";

$post_stmt = $conn->prepare($post_sql);

if (!$post_stmt) {

    echo json_encode([
        "success" => false,
        "message" => "Failed to prepare post query"
    ]);

    exit();

}

$post_stmt->bind_param("i", $post_id);
$post_stmt->execute();

$post_result = $post_stmt->get_result();

$post_data = $post_result->fetch_assoc();

$post_stmt->close();


if (!$post_data) {

    echo json_encode([
        "success" => false,
        "message" => "Post not found"
    ]);

    exit();

}

$post_owner_id = (int) $post_data["user_id"];


// ==========================================
// CHECK IF ALREADY LIKED
// ==========================================

$check_sql = "SELECT id
              FROM likes
              WHERE post_id = ?
              AND user_id = ?";

$stmt = $conn->prepare($check_sql);

$stmt->bind_param(
    "ii",
    $post_id,
    $user_id
);

$stmt->execute();

$result = $stmt->get_result();


// ==========================================
// ALREADY LIKED → UNLIKE
// ==========================================

if ($result->num_rows > 0) {

    $like = $result->fetch_assoc();

    $delete_sql = "DELETE FROM likes
                   WHERE id = ?";

    $delete_stmt = $conn->prepare($delete_sql);

    $delete_stmt->bind_param(
        "i",
        $like["id"]
    );

    $delete_stmt->execute();

    $delete_stmt->close();

    $liked = false;

}


// ==========================================
// NOT LIKED → LIKE
// ==========================================

else {

    $insert_sql = "INSERT INTO likes
                   (post_id, user_id, created_at)
                   VALUES (?, ?, NOW())";

    $insert_stmt = $conn->prepare($insert_sql);

    $insert_stmt->bind_param(
        "ii",
        $post_id,
        $user_id
    );

    $insert_stmt->execute();

    $insert_stmt->close();

    $liked = true;


    // ==========================================
    // CREATE NOTIFICATION
    // ==========================================

    // Don't notify users when they like
    // their own post.

    if ($post_owner_id !== $user_id) {


        // Get the name of the person who liked

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

        $user_data = $user_result->fetch_assoc();

        $user_stmt->close();


        $actor_name = $user_data["name"]
            ?? "Someone";


        // Notification message

        $message = $actor_name
            . " liked your post.";


        // Insert notification

        $notification_sql = "INSERT INTO notifications
                             (
                                 user_id,
                                 notification_type,
                                 message,
                                 is_read,
                                 created_at
                             )
                             VALUES (?, ?, ?, ?, NOW())";

        $notification_stmt =
            $conn->prepare($notification_sql);


        $notification_type = "like";

        $is_read = 0;


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

}


// ==========================================
// GET UPDATED LIKE COUNT
// ==========================================

$count_sql = "SELECT COUNT(*) AS total_likes
              FROM likes
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

$total_likes = (int) $count_data["total_likes"];


// ==========================================
// RETURN RESPONSE
// ==========================================

echo json_encode([

    "success" => true,

    "liked" => $liked,

    "likes" => $total_likes

]);

?>