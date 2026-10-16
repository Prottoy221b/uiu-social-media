
<?php

session_start();

include("db.php");


// =====================================================
// CHECK REQUEST METHOD
// =====================================================

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    echo "Invalid request";
    exit();

}


// =====================================================
// GET LOGIN DATA
// =====================================================

$email = trim($_POST["email"] ?? "");

$password = $_POST["password"] ?? "";


if ($email === "" || $password === "") {

    echo "Please fill in all fields";
    exit();

}


// =====================================================
// FETCH USER INCLUDING ROLE
// =====================================================

$sql = "
    SELECT
        id,
        name,
        account_type,
        role,
        department,
        email,
        password

    FROM users

    WHERE email = ?
";


$stmt = $conn->prepare($sql);

if (!$stmt) {

    echo "Database error";
    exit();

}


$stmt->bind_param(
    "s",
    $email
);

$stmt->execute();

$result = $stmt->get_result();


// =====================================================
// CHECK USER
// =====================================================

if ($result->num_rows !== 1) {

    echo "Email not found";
    exit();

}


$user = $result->fetch_assoc();


// =====================================================
// VERIFY PASSWORD
// =====================================================

if (!password_verify($password, $user["password"])) {

    echo "Wrong password";
    exit();

}


// =====================================================
// CHECK ROLE
// =====================================================

$user_role = strtolower(
    trim((string) $user["role"])
);


// =====================================================
// REGENERATE SESSION
// =====================================================

session_regenerate_id(true);


// =====================================================
// SAVE SESSION DATA
// =====================================================

$_SESSION["user_id"] =
    (int) $user["id"];

$_SESSION["user_name"] =
    $user["name"];

$_SESSION["account_type"] =
    $user["account_type"];

$_SESSION["role"] =
    $user_role;

$_SESSION["login_time"] =
    time();


// =====================================================
// UPDATE LOGIN ACTIVITY
// =====================================================

$update = $conn->prepare(
    "UPDATE users
     SET last_login = NOW(),
         last_activity = NOW()
     WHERE id = ?"
);

if ($update) {

    $update->bind_param(
        "i",
        $user["id"]
    );

    $update->execute();

}


// =====================================================
// SEND ROLE WITH LOGIN RESPONSE
// =====================================================

echo "Login successful|" . $user_role;

?>