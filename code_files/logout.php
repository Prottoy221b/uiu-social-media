<?php
session_start();
include("db.php");

if (isset($_SESSION["user_id"])) {
    $user_id = (int)$_SESSION["user_id"];

    $stmt = $conn->prepare("UPDATE users SET last_activity = NULL WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
}

$_SESSION = [];

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        "",
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

session_destroy();

header("Location: login.html");
exit();
?>
