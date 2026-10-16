<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.html");
    exit();
}

include("db.php");

$user_id = $_SESSION["user_id"];

/* Check submitted fields */

if (
    !isset($_POST["name"]) ||
    !isset($_POST["department"]) ||
    !isset($_POST["email"])
) {
    header("Location: edit_profile.php");
    exit();
}

/* Get form values */

$name = trim($_POST["name"]);
$department = trim($_POST["department"]);
$email = trim($_POST["email"]);

/* Validate empty fields */

if (
    $name === "" ||
    $department === "" ||
    $email === ""
) {
    echo "<script>
            alert('All fields are required.');
            window.location.href = 'edit_profile.php';
          </script>";

    exit();
}

/* Validate email */

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

    echo "<script>
            alert('Please enter a valid email address.');
            window.location.href = 'edit_profile.php';
          </script>";

    exit();
}

/* Check whether email is already used by another user */

$email_check_sql = "
    SELECT id
    FROM users
    WHERE email = ?
    AND id != ?
";

$email_check_stmt = $conn->prepare($email_check_sql);

$email_check_stmt->bind_param(
    "si",
    $email,
    $user_id
);

$email_check_stmt->execute();

$email_check_result = $email_check_stmt->get_result();

if ($email_check_result->num_rows > 0) {

    echo "<script>
            alert('This email is already used by another user.');
            window.location.href = 'edit_profile.php';
          </script>";

    exit();
}

/* Update user information */

$update_sql = "
    UPDATE users
    SET name = ?,
        department = ?,
        email = ?
    WHERE id = ?
";

$update_stmt = $conn->prepare($update_sql);

$update_stmt->bind_param(
    "sssi",
    $name,
    $department,
    $email,
    $user_id
);

/* Execute update */

if ($update_stmt->execute()) {

    /*
     * Update session name
     * so the new name is available immediately.
     */

    $_SESSION["user_name"] = $name;

    echo "<script>
            alert('Profile updated successfully.');
            window.location.href = 'profile.php';
          </script>";

} else {

    echo "<script>
            alert('Failed to update profile.');
            window.location.href = 'edit_profile.php';
          </script>";

}

?>