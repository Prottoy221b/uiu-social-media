<?php
session_start();
header("Content-Type: application/json; charset=UTF-8");

if (!isset($_SESSION["user_id"])) {
    http_response_code(401);
    echo json_encode([
        "logged_in" => false,
        "message" => "Not logged in"
    ]);
    exit();
}

include("db.php");

$user_id = (int)$_SESSION["user_id"];

$sql = "SELECT id, name, account_type, department, email, last_login, last_activity
        FROM users
        WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    session_unset();
    session_destroy();

    http_response_code(401);
    echo json_encode([
        "logged_in" => false,
        "message" => "User not found"
    ]);
    exit();
}

$user = $result->fetch_assoc();

echo json_encode([
    "logged_in" => true,
    "user" => [
        "id" => (int)$user["id"],
        "name" => $user["name"],
        "account_type" => $user["account_type"],
        "department" => $user["department"],
        "email" => $user["email"],
        "last_login" => $user["last_login"],
        "last_activity" => $user["last_activity"]
    ]
]);
?>
