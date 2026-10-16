<?php
session_start();
header("Content-Type: application/json; charset=UTF-8");

if (!isset($_SESSION["user_id"])) {
    http_response_code(401);
    echo json_encode(["logged_in" => false]);
    exit();
}

include("db.php");

$sql = "SELECT id, name, account_type, department, last_activity
        FROM users
        WHERE last_activity IS NOT NULL
          AND last_activity >= (NOW() - INTERVAL 2 MINUTE)
        ORDER BY last_activity DESC";

$result = $conn->query($sql);

$users = [];

while ($row = $result->fetch_assoc()) {
    $users[] = [
        "id" => (int)$row["id"],
        "name" => $row["name"],
        "account_type" => $row["account_type"],
        "department" => $row["department"],
        "last_activity" => $row["last_activity"]
    ];
}

echo json_encode([
    "logged_in" => true,
    "users" => $users
]);
?>
