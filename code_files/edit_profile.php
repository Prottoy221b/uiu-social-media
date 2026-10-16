<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.html");
    exit();
}

include("db.php");

$user_id = $_SESSION["user_id"];

/* Fetch current user information */

$sql = "SELECT name, account_type, department, email
        FROM users
        WHERE id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    die("User not found.");
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Profile</title>

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <link rel="stylesheet" href="style.css">

    <style>

        .edit_profile_container {
            background: white;
            padding: 30px;
            border-radius: 12px;
            width: 100%;
            max-width: 650px;
            margin: 20px auto;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.08);
        }

        .edit_profile_title {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 25px;
            color: #222;
        }

        .profile_form_group {
            margin-bottom: 20px;
        }

        .profile_form_group label {
            display: block;
            font-size: 15px;
            font-weight: 600;
            margin-bottom: 8px;
            color: #333;
        }

        .profile_form_group input,
        .profile_form_group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #d6d6d6;
            border-radius: 8px;
            font-size: 15px;
            outline: none;
            box-sizing: border-box;
        }

        .profile_form_group input:focus,
        .profile_form_group select:focus {
            border-color: #1877f2;
        }

        .profile_form_group input:disabled {
            background: #f1f1f1;
            cursor: not-allowed;
        }

        .profile_form_buttons {
            display: flex;
            gap: 12px;
            margin-top: 25px;
        }

        .save_profile_btn,
        .cancel_profile_btn {
            padding: 12px 22px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 15px;
            text-decoration: none;
            text-align: center;
        }

        .save_profile_btn {
            background: #1877f2;
            color: white;
        }

        .save_profile_btn:hover {
            background: #0d65d9;
        }

        .cancel_profile_btn {
            background: #e5e5e5;
            color: #222;
        }

        .cancel_profile_btn:hover {
            background: #d4d4d4;
        }

    </style>

</head>

<body>

    <!-- Header -->

    <div id="header"></div>

    <!-- Main Layout -->

    <main class="home_main">

        <!-- Left Sidebar -->

        <div id="left-sidebar"></div>

        <!-- Middle Content -->

        <div class="home_middle">

            <div class="edit_profile_container">

                <h2 class="edit_profile_title">
                    <i class="fa-solid fa-user-pen"></i>
                    Edit Profile
                </h2>

                <form action="update_profile.php"
                      method="POST">

                    <!-- Full Name -->

                    <div class="profile_form_group">

                        <label for="name">
                            Full Name
                        </label>

                        <input
                            type="text"
                            id="name"
                            name="name"
                            value="<?php echo htmlspecialchars($user['name']); ?>"
                            required
                        >

                    </div>

                    <!-- Account Type -->

                    <div class="profile_form_group">

                        <label for="account_type">
                            Account Type
                        </label>

                        <input
                            type="text"
                            id="account_type"
                            value="<?php echo htmlspecialchars($user['account_type']); ?>"
                            disabled
                        >

                    </div>

                    <!-- Department -->

                    <div class="profile_form_group">

                        <label for="department">
                            Department
                        </label>

                        <input
                            type="text"
                            id="department"
                            name="department"
                            value="<?php echo htmlspecialchars($user['department']); ?>"
                            required
                        >

                    </div>

                    <!-- Email -->

                    <div class="profile_form_group">

                        <label for="email">
                            Email
                        </label>

                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="<?php echo htmlspecialchars($user['email']); ?>"
                            required
                        >

                    </div>

                    <!-- Buttons -->

                    <div class="profile_form_buttons">

                        <button
                            type="submit"
                            class="save_profile_btn">

                            <i class="fa-solid fa-check"></i>
                            Save Changes

                        </button>

                        <a
                            href="profile.php"
                            class="cancel_profile_btn">

                            Cancel

                        </a>

                    </div>

                </form>

            </div>

        </div>

        <!-- Right Sidebar -->

        <div id="right-sidebar"></div>

    </main>

    <script src="script.js"></script>

    <script>

        /*
         * Load common page components
         */

        fetch("header.html")
            .then(response => response.text())
            .then(data => {
                document.getElementById("header").innerHTML = data;
            });

        fetch("left-sidebar.html")
            .then(response => response.text())
            .then(data => {
                document.getElementById("left-sidebar").innerHTML = data;
            });

        fetch("right-sidebar.html")
            .then(response => response.text())
            .then(data => {
                document.getElementById("right-sidebar").innerHTML = data;
            });

    </script>

</body>

</html>