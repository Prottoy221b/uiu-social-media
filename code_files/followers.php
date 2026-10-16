
<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.html");
    exit();
}

include "db.php";

$current_user_id = $_SESSION["user_id"];

/*
==========================================
        GET FOLLOWERS
==========================================
*/

$sql = "SELECT
            users.id,
            users.name,
            users.account_type,
            users.department,
            users.email,
            follows.created_at
        FROM follows
        JOIN users
            ON follows.follower_id = users.id
        WHERE follows.following_id = ?
        ORDER BY follows.created_at DESC";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "i",
    $current_user_id
);

$stmt->execute();

$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Followers</title>

    <link rel="stylesheet" href="style.css">

    <style>

        .follow_page_middle {
            padding: 25px;
        }

        .follow_page_header {
            background: white;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 20px;
        }

        .follow_page_header h2 {
            margin-bottom: 8px;
        }

        .follow_page_header p {
            color: #777;
        }

        .follow_user_card {
            background: white;
            padding: 18px;
            border-radius: 12px;
            margin-bottom: 15px;

            display: flex;
            align-items: center;
            justify-content: space-between;

            gap: 15px;
        }

        .follow_user_left {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .follow_user_image {
            width: 55px;
            height: 55px;

            border-radius: 50%;
            object-fit: cover;
        }

        .follow_user_info h3 {
            margin: 0 0 5px 0;
        }

        .follow_user_info p {
            margin: 3px 0;
            color: #777;
            font-size: 14px;
        }

        .follow_view_button {
            text-decoration: none;
            background: #1877f2;
            color: white;

            padding: 9px 15px;
            border-radius: 7px;

            font-size: 14px;
        }

        .no_followers {
            background: white;
            padding: 25px;
            border-radius: 12px;
            text-align: center;
            color: #777;
        }

        @media (max-width: 600px) {

            .follow_user_card {
                align-items: flex-start;
                flex-direction: column;
            }

            .follow_view_button {
                margin-left: 70px;
            }

        }

    </style>

</head>

<body>

    <!-- HEADER -->

    <div id="header"></div>


    <!-- MAIN CONTENT -->

    <main class="home_main">

        <!-- LEFT SIDEBAR -->

        <div id="left-sidebar"></div>


        <!-- MIDDLE CONTENT -->

        <div class="home_middle follow_page_middle">

            <div class="follow_page_header">

                <h2>Followers</h2>

                <p>
                    People who follow you
                </p>

            </div>


            <?php if ($result->num_rows > 0) { ?>

                <?php while ($user = $result->fetch_assoc()) { ?>

                    <div class="follow_user_card">

                        <div class="follow_user_left">

                            <img
                                src="../img/profile_pic.jpg"
                                alt="Profile Picture"
                                class="follow_user_image"
                            >

                            <div class="follow_user_info">

                                <h3>
                                    <?php
                                    echo htmlspecialchars(
                                        $user["name"]
                                    );
                                    ?>
                                </h3>

                                <p>
                                    <?php
                                    echo htmlspecialchars(
                                        ucfirst(
                                            $user["account_type"]
                                        )
                                    );
                                    ?>
                                </p>

                                <p>
                                    <?php
                                    echo htmlspecialchars(
                                        $user["department"]
                                    );
                                    ?>
                                </p>

                            </div>

                        </div>


                        <a
                            href="profile.php?id=<?php echo (int)$user["id"]; ?>"
                            class="follow_view_button"
                        >
                            View Profile
                        </a>

                    </div>

                <?php } ?>

            <?php } else { ?>

                <div class="no_followers">

                    <p>
                        You don't have any followers yet.
                    </p>

                </div>

            <?php } ?>

        </div>


        <!-- RIGHT SIDEBAR -->

        <div id="right-sidebar"></div>

    </main>


    <!-- JAVASCRIPT -->

    <script src="script.js"></script>

</body>

</html>