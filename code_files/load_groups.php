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


/*
==========================================
        LOAD ALL GROUPS
==========================================
*/

$sql = "SELECT
            groups.id,
            groups.name,
            groups.group_type,
            groups.description,
            COUNT(group_members.id) AS member_count
        FROM groups
        LEFT JOIN group_members
        ON groups.id = group_members.group_id
        GROUP BY
            groups.id,
            groups.name,
            groups.group_type,
            groups.description
        ORDER BY groups.name ASC";


$result = $conn->query($sql);

$groups = [];


while ($group = $result->fetch_assoc()) {


    /*
    Check whether current user
    already joined this group
    */

    $join_sql = "SELECT id
                 FROM group_members
                 WHERE group_id = ?
                 AND user_id = ?";


    $join_stmt =
        $conn->prepare($join_sql);


    $join_stmt->bind_param(
        "ii",
        $group["id"],
        $current_user_id
    );


    $join_stmt->execute();


    $join_result =
        $join_stmt->get_result();


    $joined =
        $join_result->num_rows > 0;


    $groups[] = [

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
            $joined

    ];

}


echo json_encode([

    "success" => true,

    "groups" => $groups

]);

?>