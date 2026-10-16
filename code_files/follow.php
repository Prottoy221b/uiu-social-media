<?php

session_start();

include "db.php";

header("Content-Type: application/json");


/*
==========================================
        CHECK LOGIN
==========================================
*/

if (!isset($_SESSION["user_id"])) {

    echo json_encode([
        "success" => false,
        "message" => "Not logged in"
    ]);

    exit();
}


$current_user_id = $_SESSION["user_id"];


/*
==========================================
        CHECK FOLLOWING ID
==========================================
*/

if (!isset($_POST["following_id"])) {

    echo json_encode([
        "success" => false,
        "message" => "User ID missing"
    ]);

    exit();
}


$following_id =
    intval($_POST["following_id"]);


/*
==========================================
        PREVENT SELF FOLLOW
==========================================
*/

if ($current_user_id == $following_id) {

    echo json_encode([
        "success" => false,
        "message" => "You cannot follow yourself"
    ]);

    exit();
}


/*
==========================================
        CHECK EXISTING FOLLOW
==========================================
*/

$check_sql = "SELECT id
              FROM follows
              WHERE follower_id = ?
              AND following_id = ?";

$check_stmt =
    $conn->prepare($check_sql);

$check_stmt->bind_param(
    "ii",
    $current_user_id,
    $following_id
);

$check_stmt->execute();

$result =
    $check_stmt->get_result();


/*
==========================================
        ALREADY FOLLOWING → UNFOLLOW
==========================================
*/

if ($result->num_rows > 0) {

    $follow =
        $result->fetch_assoc();


    $delete_sql =
        "DELETE FROM follows
         WHERE id = ?";


    $delete_stmt =
        $conn->prepare($delete_sql);


    $delete_stmt->bind_param(
        "i",
        $follow["id"]
    );


    $delete_stmt->execute();


    $following = false;

}


/*
==========================================
        NOT FOLLOWING → FOLLOW
==========================================
*/

else {

    $insert_sql =
        "INSERT INTO follows
        (follower_id, following_id, created_at)
        VALUES (?, ?, NOW())";


    $insert_stmt =
        $conn->prepare($insert_sql);


    $insert_stmt->bind_param(
        "ii",
        $current_user_id,
        $following_id
    );


    $insert_stmt->execute();


    $following = true;

}


/*
==========================================
        GET FOLLOWER COUNT
==========================================
*/

$count_sql =
    "SELECT COUNT(*) AS total_followers
     FROM follows
     WHERE following_id = ?";


$count_stmt =
    $conn->prepare($count_sql);


$count_stmt->bind_param(
    "i",
    $following_id
);


$count_stmt->execute();


$count_result =
    $count_stmt->get_result();


$count_data =
    $count_result->fetch_assoc();


$total_followers =
    $count_data["total_followers"];


/*
==========================================
        RESPONSE
==========================================
*/

echo json_encode([

    "success" => true,

    "following" => $following,

    "followers" => $total_followers

]);

?>