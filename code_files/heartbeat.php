<?php
session_start();
header("Content-Type: application/json; charset=UTF-8");

if (!isset($_SESSION["user_id"])) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Not logged in"]);
    exit();
}

include("db.php");

$user_id = (int)$_SESSION["user_id"];

$stmt = $conn->prepare("UPDATE users SET last_activity = NOW() WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();

echo json_encode([
    "success" => true,
    "last_activity" => date("Y-m-d H:i:s")
]);
?>
