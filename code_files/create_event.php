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


$title =
    isset($_POST["title"])
    ? trim($_POST["title"])
    : "";

$date =
    isset($_POST["event_date"])
    ? $_POST["event_date"]
    : "";

$time =
    isset($_POST["event_time"])
    ? $_POST["event_time"]
    : "";

$location =
    isset($_POST["location"])
    ? trim($_POST["location"])
    : "";

$description =
    isset($_POST["description"])
    ? trim($_POST["description"])
    : "";


if ($title === "") {

    echo json_encode([
        "success" => false,
        "message" => "Event title is required"
    ]);

    exit();
}


if ($date === "") {

    echo json_encode([
        "success" => false,
        "message" => "Event date is required"
    ]);

    exit();
}


if ($time === "") {

    echo json_encode([
        "success" => false,
        "message" => "Event time is required"
    ]);

    exit();
}


if ($location === "") {

    echo json_encode([
        "success" => false,
        "message" => "Event location is required"
    ]);

    exit();
}


$sql = "
    INSERT INTO events
    (
        title,
        description,
        event_date,
        event_time,
        location,
        created_by,
        created_at
    )
    VALUES
    (?, ?, ?, ?, ?, ?, NOW())
";


$stmt = $conn->prepare($sql);


if (!$stmt) {

    echo json_encode([
        "success" => false,
        "message" => "Database error"
    ]);

    exit();
}


$stmt->bind_param(
    "sssssi",
    $title,
     $description,
    $date,
    $time,
    $location,
    $current_user_id
);


if ($stmt->execute()) {

    echo json_encode([
        "success" => true,
        "message" => "Event created successfully",
        "event_id" => $stmt->insert_id
    ]);

} else {

    echo json_encode([
        "success" => false,
        "message" => "Failed to create event"
    ]);

}

?>