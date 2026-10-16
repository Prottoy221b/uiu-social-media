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


$sql = "
    SELECT
        events.id,
        events.title,
        events.description,
        events.event_date,
        events.event_time,
        events.location,
        events.created_by,
        events.created_at,
        users.name AS creator_name
    FROM events

    LEFT JOIN users
        ON events.created_by = users.id

    ORDER BY
        events.event_date ASC,
        events.event_time ASC
";


$result = $conn->query($sql);


if (!$result) {

    echo json_encode([
        "success" => false,
        "message" => "Failed to load events"
    ]);

    exit();
}


$events = [];


while ($event = $result->fetch_assoc()) {

    $events[] = $event;

}


echo json_encode([
    "success" => true,
    "events" => $events
]);

?>