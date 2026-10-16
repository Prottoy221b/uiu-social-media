<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.html");
    exit();
}

include "db.php";

$current_user_id = $_SESSION["user_id"];

if (!isset($_GET["id"])) {
    header("Location: groups.php");
    exit();
}

$group_id = intval($_GET["id"]);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Group</title>

    <link rel="stylesheet"
          href="style.css">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

</head>

<body>

    <div id="header"></div>


    <main class="home_main">

        <div id="left-sidebar"></div>


        <div class="group_details_middle">

            <!-- GROUP HEADER -->

            <div class="group_details_header">

                <div class="group_details_image">

                    <img
                        id="groupImage"
                        src="../img/CSE_grp.jpg"
                        alt="Group">

                </div>


                <div class="group_details_info">

                    <h1 id="groupName">
                        Loading...
                    </h1>

                    <p id="groupType">
                        Loading...
                    </p>

                    <p id="groupMembers">
                        Loading...
                    </p>

                </div>


                <button
                    id="groupJoinBtn"
                    class="join"
                    data-group-id="<?php echo $group_id; ?>">

                    Loading...

                </button>

            </div>


            <!-- ABOUT -->

            <div class="group_details_section">

                <h2>About</h2>

                <p id="groupDescription">
                    Loading...
                </p>

            </div>


            <!-- MEMBERS -->

            <div class="group_details_section">

                <h2>Members</h2>

                <div id="groupMembersList">

                    <p>Loading members...</p>

                </div>

            </div>


            <!-- =========================
     GROUP POSTS
========================= -->

<div class="group_details_section group_posts_section">

    <h2>Group Posts</h2>


    <!-- CREATE POST -->

    <div id="groupPostCreator"
         class="group_post_creator">

        <textarea
            id="groupPostInput"
            placeholder="Write something to this group..."
            maxlength="1000"></textarea>


        <button
            id="createGroupPostBtn"
            type="button">

            Post

        </button>

    </div>


    <!-- POSTS -->

    <div id="groupPostsList">

        <p>Loading posts...</p>

    </div>

</div>

        </div>


        <div id="right-sidebar"></div>

    </main>
<script>

        window.currentUserId =
            <?php echo (int)$current_user_id; ?>;

        window.currentGroupId =
            <?php echo (int)$group_id; ?>;

    </script>

    <script src="script.js"></script>


    

</body>

</html>