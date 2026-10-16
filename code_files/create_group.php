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

$name = isset($_POST["name"]) ? trim($_POST["name"]) : "";
$group_type = isset($_POST["group_type"]) ? trim($_POST["group_type"]) : "";
$description = isset($_POST["description"]) ? trim($_POST["description"]) : "";

if ($name === "") {
    echo json_encode([
        "success" => false,
        "message" => "Group name is required"
    ]);
    exit();
}

if ($group_type === "") {
    echo json_encode([
        "success" => false,
        "message" => "Group type is required"
    ]);
    exit();
}

if ($description === "") {
    echo json_encode([
        "success" => false,
        "message" => "Group description is required"
    ]);
    exit();
}


/* Check duplicate group name */

$check_sql = "SELECT id FROM groups WHERE name = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("s", $name);
$check_stmt->execute();

$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {

    echo json_encode([
        "success" => false,
        "message" => "A group with this name already exists"
    ]);

    exit();
}


/* Create group */

$insert_sql = "INSERT INTO groups
               (name, group_type, description, created_at)
               VALUES (?, ?, ?, NOW())";

$insert_stmt = $conn->prepare($insert_sql);

$insert_stmt->bind_param(
    "sss",
    $name,
    $group_type,
    $description
);

if (!$insert_stmt->execute()) {

    echo json_encode([
        "success" => false,
        "message" => "Failed to create group"
    ]);

    exit();
}

$group_id = $insert_stmt->insert_id;


/* Automatically add creator as member */

$member_sql = "INSERT INTO group_members
               (group_id, user_id, joined_at)
               VALUES (?, ?, NOW())";

$member_stmt = $conn->prepare($member_sql);

$member_stmt->bind_param(
    "ii",
    $group_id,
    $current_user_id
);

if (!$member_stmt->execute()) {

    // Remove group if member creation fails
    $delete_sql = "DELETE FROM groups WHERE id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $group_id);
    $delete_stmt->execute();

    echo json_encode([
        "success" => false,
        "message" => "Group created but membership setup failed"
    ]);

    exit();
}


echo json_encode([
    "success" => true,
    "message" => "Group created successfully",
    "group_id" => $group_id,
    "name" => $name,
    "group_type" => $group_type,
    "description" => $description
]);

?>