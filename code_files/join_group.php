
<?php

session_start();
include "db.php";

header("Content-Type: application/json");


/* =========================
   CHECK LOGIN
========================= */

if (!isset($_SESSION["user_id"])) {

    echo json_encode([
        "success" => false,
        "message" => "Not logged in"
    ]);

    exit();
}

$current_user_id = (int) $_SESSION["user_id"];


/* =========================
   CHECK GROUP ID
========================= */

if (!isset($_POST["group_id"])) {

    echo json_encode([
        "success" => false,
        "message" => "Group ID missing"
    ]);

    exit();
}

$group_id = intval($_POST["group_id"]);


/* =========================
   GET GROUP INFORMATION
========================= */

$group_sql = "SELECT name
              FROM groups
              WHERE id = ?";

$group_stmt = $conn->prepare($group_sql);
$group_stmt->bind_param("i", $group_id);
$group_stmt->execute();

$group_result = $group_stmt->get_result();
$group = $group_result->fetch_assoc();

$group_stmt->close();


if (!$group) {

    echo json_encode([
        "success" => false,
        "message" => "Group not found"
    ]);

    exit();
}

$group_name = $group["name"];


/* =========================
   CHECK EXISTING MEMBERSHIP
========================= */

$check_sql = "SELECT id
              FROM group_members
              WHERE group_id = ?
              AND user_id = ?";

$check_stmt = $conn->prepare($check_sql);

$check_stmt->bind_param(
    "ii",
    $group_id,
    $current_user_id
);

$check_stmt->execute();

$result = $check_stmt->get_result();


/* =========================
   ALREADY JOINED → LEAVE
========================= */

if ($result->num_rows > 0) {

    $member = $result->fetch_assoc();

    $delete_sql = "DELETE FROM group_members
                   WHERE id = ?";

    $delete_stmt = $conn->prepare($delete_sql);

    $delete_stmt->bind_param(
        "i",
        $member["id"]
    );

    $delete_stmt->execute();
    $delete_stmt->close();

    $joined = false;
}


/* =========================
   NOT JOINED → JOIN
========================= */

else {

    $insert_sql = "INSERT INTO group_members
                   (group_id, user_id, joined_at)
                   VALUES (?, ?, NOW())";

    $insert_stmt = $conn->prepare($insert_sql);

    $insert_stmt->bind_param(
        "ii",
        $group_id,
        $current_user_id
    );

    if (!$insert_stmt->execute()) {

        echo json_encode([
            "success" => false,
            "message" => "Failed to join group"
        ]);

        exit();
    }

    $insert_stmt->close();

    $joined = true;


    /* =========================
       GET JOINING USER NAME
    ========================= */

    $user_sql = "SELECT name
                 FROM users
                 WHERE id = ?";

    $user_stmt = $conn->prepare($user_sql);

    $user_stmt->bind_param(
        "i",
        $current_user_id
    );

    $user_stmt->execute();

    $user_result = $user_stmt->get_result();
    $user = $user_result->fetch_assoc();

    $user_stmt->close();

    $actor_name = $user["name"];


    /* =========================
       GET EXISTING GROUP MEMBERS
    ========================= */

    /*
       Notify members who were already
       in the group.

       The new member is excluded.
    */

    $members_sql = "SELECT user_id
                    FROM group_members
                    WHERE group_id = ?
                    AND user_id != ?";

    $members_stmt = $conn->prepare($members_sql);

    $members_stmt->bind_param(
        "ii",
        $group_id,
        $current_user_id
    );

    $members_stmt->execute();

    $members_result = $members_stmt->get_result();


    /* =========================
       INSERT NOTIFICATIONS
    ========================= */

    $notification_type = "group_join";

    $message = $actor_name .
               " joined the " .
               $group_name .
               " group.";

    $is_read = 0;


    $notification_sql = "INSERT INTO notifications
                         (user_id, notification_type, message, is_read, created_at)
                         VALUES (?, ?, ?, ?, NOW())";

    $notification_stmt = $conn->prepare(
        $notification_sql
    );


    while ($member_row = $members_result->fetch_assoc()) {

        $recipient_id = (int) $member_row["user_id"];

        $notification_stmt->bind_param(
            "issi",
            $recipient_id,
            $notification_type,
            $message,
            $is_read
        );

        $notification_stmt->execute();
    }


    $notification_stmt->close();
    $members_stmt->close();
}


/* =========================
   GET UPDATED MEMBER COUNT
========================= */

$count_sql = "SELECT COUNT(*) AS total_members
              FROM group_members
              WHERE group_id = ?";

$count_stmt = $conn->prepare($count_sql);

$count_stmt->bind_param(
    "i",
    $group_id
);

$count_stmt->execute();

$count_result = $count_stmt->get_result();
$count_data = $count_result->fetch_assoc();

$count_stmt->close();

$total_members = $count_data["total_members"];


/* =========================
   RESPONSE
========================= */

echo json_encode([

    "success" => true,

    "joined" => $joined,

    "members" => $total_members

]);

?>