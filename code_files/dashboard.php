<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.html");
    exit();
}

// Session theke role niyechi
$role = $_SESSION["role"] ?? "user";

$role = strtolower(trim($role));

// Role onujayi redirect
if ($role === "admin") {

    header("Location: admin-dashboard.php");
    exit();

} else {

    header("Location: home.php");
    exit();

}

?>