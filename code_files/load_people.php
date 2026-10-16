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


/*
==========================================
        GET PEOPLE MAY KNOW
==========================================
*/

$sql = "SELECT
            users.id,
            users.name,
            users.account_type
        FROM users
        WHERE users.id != ?
        ORDER BY users.name ASC
        LIMIT 5";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "i",
    $current_user_id
);

$stmt->execute();

$result = $stmt->get_result();

$people = [];


while ($user = $result->fetch_assoc()) {

    /*
    Check whether current user
    already follows this person
    */

    $follow_sql = "SELECT id
                   FROM follows
                   WHERE follower_id = ?
                   AND following_id = ?";

    $follow_stmt =
        $conn->prepare($follow_sql);

    $follow_stmt->bind_param(
        "ii",
        $current_user_id,
        $user["id"]
    );

    $follow_stmt->execute();

    $follow_result =
        $follow_stmt->get_result();


    $is_following =
        $follow_result->num_rows > 0;


    $people[] = [

        "id" => $user["id"],

        "name" => $user["name"],

        "account_type" =>
            $user["account_type"],

        "following" =>
            $is_following

    ];

}


echo json_encode([

    "success" => true,

    "people" => $people

]);

?>