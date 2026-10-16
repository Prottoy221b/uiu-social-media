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
        CHECK RECEIVER ID
==========================================
*/

if (!isset($_GET["receiver_id"])) {

    echo json_encode([
        "success" => false,
        "message" => "Receiver ID missing"
    ]);

    exit();
}


$receiver_id = intval($_GET["receiver_id"]);


/*
==========================================
        PREVENT SELF CHAT
==========================================
*/

if ($current_user_id == $receiver_id) {

    echo json_encode([
        "success" => false,
        "message" => "Invalid receiver"
    ]);

    exit();
}


/*
==========================================
        LOAD CONVERSATION
==========================================

Messages can be:

Current user → Receiver

OR

Receiver → Current user
*/

$sql = "SELECT
            messages.id,
            messages.sender_id,
            messages.receiver_id,
            messages.message,
            messages.created_at,
            users.name AS sender_name

        FROM messages

        JOIN users
        ON messages.sender_id = users.id

        WHERE
            (
                messages.sender_id = ?
                AND
                messages.receiver_id = ?
            )

            OR

            (
                messages.sender_id = ?
                AND
                messages.receiver_id = ?
            )

        ORDER BY messages.created_at ASC";


$stmt = $conn->prepare($sql);


$stmt->bind_param(
    "iiii",
    $current_user_id,
    $receiver_id,
    $receiver_id,
    $current_user_id
);


$stmt->execute();


$result = $stmt->get_result();


/*
==========================================
        CREATE MESSAGE ARRAY
==========================================
*/

$messages = [];


while ($row = $result->fetch_assoc()) {

    $messages[] = [

        "id" => $row["id"],

        "sender_id" => $row["sender_id"],

        "receiver_id" => $row["receiver_id"],

        "message" => $row["message"],

        "created_at" => $row["created_at"],

        "sender_name" => $row["sender_name"]

    ];

}


/*
==========================================
        RESPONSE
==========================================
*/

echo json_encode([

    "success" => true,

    "messages" => $messages

]);

?>