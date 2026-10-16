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


if (!isset($_GET["group_id"])) {

    echo json_encode([
        "success" => false,
        "message" => "Group ID missing"
    ]);

    exit();
}

$group_id = intval($_GET["group_id"]);


/* =========================
   GROUP INFORMATION
========================= */

$sql = "SELECT
            groups.id,
            groups.name,
            groups.group_type,
            groups.description,
            COUNT(group_members.id) AS member_count

        FROM groups

        LEFT JOIN group_members
        ON groups.id = group_members.group_id

        WHERE groups.id = ?

        GROUP BY
            groups.id,
            groups.name,
            groups.group_type,
            groups.description";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "i",
    $group_id
);

$stmt->execute();

$result = $stmt->get_result();


if ($result->num_rows === 0) {

    echo json_encode([
        "success" => false,
        "message" => "Group not found"
    ]);

    exit();
}


$group = $result->fetch_assoc();


/* =========================
   CHECK MEMBERSHIP
========================= */

$member_sql =
    "SELECT id
     FROM group_members
     WHERE group_id = ?
     AND user_id = ?";

$member_stmt =
    $conn->prepare($member_sql);

$member_stmt->bind_param(
    "ii",
    $group_id,
    $current_user_id
);

$member_stmt->execute();

$member_result =
    $member_stmt->get_result();

$is_member =
    $member_result->num_rows > 0;


/* =========================
   GET MEMBERS
========================= */

$members_sql =
    "SELECT
        users.id,
        users.name,
        users.account_type

     FROM group_members

     JOIN users
     ON group_members.user_id = users.id

     WHERE group_members.group_id = ?

     ORDER BY users.name ASC";

$members_stmt =
    $conn->prepare($members_sql);

$members_stmt->bind_param(
    "i",
    $group_id
);

$members_stmt->execute();

$members_result =
    $members_stmt->get_result();


$members = [];


while ($member =
       $members_result->fetch_assoc()) {

    $members[] = [

        "id" =>
            $member["id"],

        "name" =>
            $member["name"],

        "account_type" =>
            $member["account_type"]

    ];
}


/* =========================
   RESPONSE
========================= */

echo json_encode([

    "success" => true,

    "group" => [

        "id" =>
            $group["id"],

        "name" =>
            $group["name"],

        "group_type" =>
            $group["group_type"],

        "description" =>
            $group["description"],

        "member_count" =>
            $group["member_count"],

        "joined" =>
            $is_member

    ],

    "members" =>
        $members

]);

?>