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

$sender_id = $_SESSION["user_id"];


/* Check required data */

if (!isset($_POST["receiver_id"]) || !isset($_POST["message"])) {

    echo json_encode([
        "success" => false,
        "message" => "Missing data"
    ]);

    exit();
}

$receiver_id = intval($_POST["receiver_id"]);
$message = trim($_POST["message"]);


/* Empty message check */

if ($message === "") {

    echo json_encode([
        "success" => false,
        "message" => "Message cannot be empty"
    ]);

    exit();
}


/* Cannot message yourself */

if ($sender_id == $receiver_id) {

    echo json_encode([
        "success" => false,
        "message" => "You cannot message yourself"
    ]);

    exit();
}


/* Insert message */

$sql = "INSERT INTO messages
        (sender_id, receiver_id, message, created_at)
        VALUES (?, ?, ?, NOW())";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "iis",
    $sender_id,
    $receiver_id,
    $message
);


if (!$stmt->execute()) {

    echo json_encode([
        "success" => false,
        "message" => "Failed to send message"
    ]);

    exit();
}


/* Return success */

echo json_encode([
    "success" => true,
    "message_id" => $stmt->insert_id,
    "message" => $message
]);

?>