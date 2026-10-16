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

$sql = "SELECT
            id,
            notification_type,
            message,
            is_read,
            created_at
        FROM notifications
        WHERE user_id = ?
        ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

$notifications = [];

while ($row = $result->fetch_assoc()) {
    $notifications[] = [
        "id" => (int) $row["id"],
        "type" => $row["notification_type"],
        "message" => $row["message"],
        "is_read" => (int) $row["is_read"],
        "created_at" => $row["created_at"]
    ];
}

echo json_encode([
    "success" => true,
    "notifications" => $notifications
]);

$stmt->close();

?>