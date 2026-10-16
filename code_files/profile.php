
<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.html");
    exit();
}

include("db.php");

$current_user_id = (int) $_SESSION["user_id"];


/* =====================================================
   GET PROFILE USER ID
   ===================================================== */

if (isset($_GET["id"]) && is_numeric($_GET["id"])) {

    $profile_user_id = (int) $_GET["id"];

} else {

    $profile_user_id = $current_user_id;

}


/* =====================================================
   GET USER PROFILE INFORMATION
   ===================================================== */

$user_sql = "
    SELECT
        id,
        name,
        account_type,
        department,
        email
    FROM users
    WHERE id = ?
";

$user_stmt = $conn->prepare($user_sql);

$user_stmt->bind_param(
    "i",
    $profile_user_id
);

$user_stmt->execute();

$user_result = $user_stmt->get_result();

$profile_user = $user_result->fetch_assoc();


/* =====================================================
   CHECK USER EXISTS
   ===================================================== */

if (!$profile_user) {

    echo "<h2>User profile not found.</h2>";
    exit();

}


/* =====================================================
   FOLLOWERS COUNT
   ===================================================== */

$followers_sql = "
    SELECT COUNT(*) AS total_followers
    FROM follows
    WHERE following_id = ?
";

$followers_stmt = $conn->prepare($followers_sql);

$followers_stmt->bind_param(
    "i",
    $profile_user_id
);

$followers_stmt->execute();

$followers_result = $followers_stmt->get_result();

$followers_data = $followers_result->fetch_assoc();

$total_followers = (int) $followers_data["total_followers"];


/* =====================================================
   FOLLOWING COUNT
   ===================================================== */

$following_sql = "
    SELECT COUNT(*) AS total_following
    FROM follows
    WHERE follower_id = ?
";

$following_stmt = $conn->prepare($following_sql);

$following_stmt->bind_param(
    "i",
    $profile_user_id
);

$following_stmt->execute();

$following_result = $following_stmt->get_result();

$following_data = $following_result->fetch_assoc();

$total_following = (int) $following_data["total_following"];


/* =====================================================
   CHECK CURRENT USER FOLLOWING THIS PROFILE
   ===================================================== */

$is_following = false;

if ($current_user_id != $profile_user_id) {

    $check_follow_sql = "
        SELECT id
        FROM follows
        WHERE follower_id = ?
        AND following_id = ?
    ";

    $check_follow_stmt = $conn->prepare($check_follow_sql);

    $check_follow_stmt->bind_param(
        "ii",
        $current_user_id,
        $profile_user_id
    );

    $check_follow_stmt->execute();

    $check_follow_result =
        $check_follow_stmt->get_result();

    if ($check_follow_result->num_rows > 0) {

        $is_following = true;

    }

}


/* =====================================================
   GET PROFILE USER POSTS
   ===================================================== */

$post_sql = "
    SELECT
        posts.id,
        posts.user_id,
        posts.content,
        posts.post_type,
        posts.created_at,

        users.name,

        (
            SELECT COUNT(*)
            FROM likes
            WHERE likes.post_id = posts.id
        ) AS like_count,

        (
            SELECT COUNT(*)
            FROM comments
            WHERE comments.post_id = posts.id
        ) AS comment_count

    FROM posts

    JOIN users
    ON posts.user_id = users.id

    WHERE posts.user_id = ?

    ORDER BY posts.created_at DESC
";

$post_stmt = $conn->prepare($post_sql);

$post_stmt->bind_param(
    "i",
    $profile_user_id
);

$post_stmt->execute();

$post_result = $post_stmt->get_result();

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        <?php echo htmlspecialchars($profile_user["name"]); ?> - Profile
    </title>

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
    >

    <link rel="stylesheet" href="style.css">


    <style>

        .profile_page {

            width: 100%;
            padding: 20px;

        }


        .profile_header {

            background: white;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);

        }


        .profile_top {

            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 20px;

        }


        .profile_main_image {

            width: 110px;
            height: 110px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #e5e7eb;

        }


        .profile_user_name {

            font-size: 25px;
            font-weight: 700;
            margin: 0 0 7px 0;

        }


        .profile_user_type {

            color: #666;
            margin: 0 0 5px 0;

        }


        .profile_department {

            color: #777;
            margin: 0;

        }


        .profile_actions {

            margin-left: auto;

        }


        .profile_button {

            border: none;
            padding: 10px 18px;
            border-radius: 7px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;

        }


        .edit_profile_button {

            background: #2563eb;
            color: white;
            text-decoration: none;
            display: inline-block;

        }


        .follow_profile_button {

            background: #2563eb;
            color: white;

        }


        .following_profile_button {

            background: #e5e7eb;
            color: #222;

        }


        .profile_stats {

            display: flex;
            gap: 35px;
            border-top: 1px solid #eeeeee;
            padding-top: 20px;

        }


        .profile_stat {

            text-align: center;
            text-decoration: none;
            color: inherit;
            cursor: pointer;

        }


        .profile_stat strong {

            display: block;
            font-size: 22px;
            color: #222;

        }


        .profile_stat span {

            font-size: 14px;
            color: #666;

        }


        .profile_information {

            background: white;
            padding: 22px;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);

        }


        .profile_information h3 {

            margin-top: 0;
            margin-bottom: 18px;

        }


        .information_row {

            display: flex;
            gap: 15px;
            padding: 12px 0;
            border-bottom: 1px solid #eeeeee;

        }


        .information_row:last-child {

            border-bottom: none;

        }


        .information_label {

            width: 130px;
            font-weight: 600;
            color: #555;

        }


        .information_value {

            color: #222;
            word-break: break-word;

        }


        .profile_posts_title {

            font-size: 22px;
            font-weight: 700;
            margin: 25px 0 15px 0;

        }


        .profile_post {

            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 18px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);

        }


        .profile_post_top {

            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 15px;

        }


        .profile_post_image {

            width: 45px;
            height: 45px;
            border-radius: 50%;
            object-fit: cover;

        }


        .profile_post_name {

            font-weight: 700;
            margin: 0;

        }


        .profile_post_time {

            font-size: 12px;
            color: #777;
            margin: 4px 0 0 0;

        }


        .profile_post_content {

            font-size: 15px;
            line-height: 1.7;
            margin-bottom: 18px;
            white-space: pre-wrap;
            word-break: break-word;

        }


        .profile_post_counts {

            display: flex;
            gap: 20px;
            border-bottom: 1px solid #eeeeee;
            padding-bottom: 12px;
            color: #666;
            font-size: 14px;

        }


        .profile_post_actions {

            display: flex;
            gap: 10px;
            margin-top: 12px;

        }


        .profile_action_button {

            border: none;
            background: #f3f4f6;
            padding: 9px 18px;
            border-radius: 6px;
            cursor: pointer;
            color: #333;

        }


        .no_posts {

            background: white;
            padding: 30px;
            text-align: center;
            border-radius: 12px;
            color: #777;

        }


        @media (max-width: 768px) {

            .profile_top {

                flex-direction: column;
                text-align: center;

            }


            .profile_actions {

                margin-left: 0;

            }


            .profile_stats {

                justify-content: center;
                gap: 20px;

            }


            .information_row {

                flex-direction: column;
                gap: 5px;

            }


            .information_label {

                width: auto;

            }

        }

    </style>

</head>


<body>


    <!-- HEADER -->

    <div id="header"></div>


    <!-- MAIN LAYOUT -->

    <main class="home_main">


        <!-- LEFT SIDEBAR -->

        <div id="left-sidebar"></div>


        <!-- MIDDLE CONTENT -->

        <div class="home_middle">


            <div class="profile_page">


                <!-- PROFILE HEADER -->

                <section class="profile_header">


                    <div class="profile_top">


                        <img
                            src="../img/profile_pic.jpg"
                            alt="Profile Picture"
                            class="profile_main_image"
                        >


                        <div>

                            <h1 class="profile_user_name">

                                <?php
                                echo htmlspecialchars(
                                    $profile_user["name"]
                                );
                                ?>

                            </h1>


                            <p class="profile_user_type">

                                <?php

                                if (
                                    strtolower(
                                        $profile_user["account_type"]
                                    ) === "faculty"
                                ) {

                                    echo "Faculty";

                                } else {

                                    echo "Student";

                                }

                                ?>

                            </p>


                            <p class="profile_department">

                                <?php
                                echo htmlspecialchars(
                                    $profile_user["department"] ?? "Not specified"
                                );
                                ?>

                            </p>

                        </div>


                        <div class="profile-actions">

    <a
        href="edit_profile.php"
        class="profile-action-btn"
    >
        Edit Profile
    </a>

    <?php if (strtolower(trim($profile_user["account_type"])) === "faculty"): ?>

        <a
            href="faculty-dashboard.php"
            class="profile-action-btn"
        >
            <i class="fa-solid fa-chalkboard-user"></i>
            Faculty Dashboard
        </a>

    <?php endif; ?>

    <a
        href="my_posts.php"
        class="profile-action-btn secondary"
    >
        My Posts
    </a>

    <a
        href="followers.php"
        class="profile-action-btn secondary"
    >
        Followers
    </a>

    <a
        href="following.php"
        class="profile-action-btn secondary"
    >
        Following
    </a>

</div>


                    </div>


                    <!-- PROFILE STATS -->

                    <div class="profile_stats">


                        <a
                            href="followers.php?id=<?php echo $profile_user_id; ?>"
                            class="profile_stat"
                        >

                            <strong>
                                <?php echo $total_followers; ?>
                            </strong>

                            <span>Followers</span>

                        </a>


                        <a
                            href="following.php?id=<?php echo $profile_user_id; ?>"
                            class="profile_stat"
                        >

                            <strong>
                                <?php echo $total_following; ?>
                            </strong>

                            <span>Following</span>

                        </a>


                    </div>


                </section>



                <!-- PROFILE INFORMATION -->

                <section class="profile_information">


                    <h3>Profile Information</h3>


                    <div class="information_row">


                        <div class="information_label">
                            Name
                        </div>


                        <div class="information_value">

                            <?php
                            echo htmlspecialchars(
                                $profile_user["name"]
                            );
                            ?>

                        </div>


                    </div>


                    <div class="information_row">


                        <div class="information_label">
                            Account Type
                        </div>


                        <div class="information_value">

                            <?php
                            echo htmlspecialchars(
                                $profile_user["account_type"]
                            );
                            ?>

                        </div>


                    </div>


                    <div class="information_row">


                        <div class="information_label">
                            Department
                        </div>


                        <div class="information_value">

                            <?php

                            echo htmlspecialchars(
                                $profile_user["department"]
                                ?? "Not specified"
                            );

                            ?>

                        </div>


                    </div>


                    <?php if ($current_user_id == $profile_user_id): ?>


                        <div class="information_row">


                            <div class="information_label">
                                Email
                            </div>


                            <div class="information_value">

                                <?php

                                echo htmlspecialchars(
                                    $profile_user["email"]
                                );

                                ?>

                            </div>


                        </div>


                    <?php endif; ?>


                </section>



                <!-- USER POSTS -->

                <h2 class="profile_posts_title">

                    <?php

                    if ($current_user_id == $profile_user_id) {

                        echo "My Posts";

                    } else {

                        echo htmlspecialchars(
                            $profile_user["name"]
                        ) . "'s Posts";

                    }

                    ?>

                </h2>



                <?php if ($post_result->num_rows > 0): ?>


                    <?php while ($post = $post_result->fetch_assoc()): ?>


                        <article class="profile_post">


                            <div class="profile_post_top">


                                <img
                                    src="../img/profile_pic.jpg"
                                    alt="Profile Picture"
                                    class="profile_post_image"
                                >


                                <div>


                                    <p class="profile_post_name">

                                        <?php

                                        echo htmlspecialchars(
                                            $post["name"]
                                        );

                                        ?>

                                    </p>


                                    <p class="profile_post_time">

                                        <?php

                                        echo htmlspecialchars(
                                            $post["created_at"]
                                        );

                                        ?>

                                    </p>


                                </div>


                            </div>



                            <div class="profile_post_content">

                                <?php

                                echo nl2br(
                                    htmlspecialchars(
                                        $post["content"]
                                    )
                                );

                                ?>

                            </div>



                            <div class="profile_post_counts">


                                <span>

                                    <i class="fa-regular fa-heart"></i>

                                    <?php
                                    echo (int) $post["like_count"];
                                    ?> Likes

                                </span>


                                <span>

                                    <i class="fa-regular fa-comment"></i>

                                    <?php
                                    echo (int) $post["comment_count"];
                                    ?> Comments

                                </span>


                            </div>



                            <div class="profile_post_actions">


                                <button
                                    class="profile_action_button"
                                    type="button"
                                    onclick="alert('Like feature can be connected here.')"
                                >

                                    <i class="fa-regular fa-heart"></i>
                                    Like

                                </button>


                                <button
                                    class="profile_action_button"
                                    type="button"
                                    onclick="alert('Comment feature can be connected here.')"
                                >

                                    <i class="fa-regular fa-comment"></i>
                                    Comment

                                </button>


                                <button
                                    class="profile_action_button"
                                    type="button"
                                    onclick="alert('Share feature can be connected here.')"
                                >

                                    <i class="fa-solid fa-share"></i>
                                    Share

                                </button>


                            </div>


                        </article>


                    <?php endwhile; ?>


                <?php else: ?>


                    <div class="no_posts">

                        This user has not created any posts yet.

                    </div>


                <?php endif; ?>


            </div>


        </div>



        <!-- RIGHT SIDEBAR -->

        <div id="right-sidebar"></div>


    </main>



    

  



    <!-- FOLLOW / UNFOLLOW SCRIPT -->

    <?php if ($current_user_id != $profile_user_id): ?>


        <script>






        

            const profileFollowButton =
                document.getElementById(
                    "profile-follow-button"
                );


            if (profileFollowButton) {


                profileFollowButton.addEventListener(
                    "click",
                    function() {


                        const userId =
                            this.dataset.userId;


                        const formData =
                            new FormData();


                        formData.append(
                            "following_id",
                            userId
                        );


                        profileFollowButton.disabled = true;


                        fetch(
                            "follow.php",
                            {
                                method: "POST",
                                body: formData,
                                credentials: "same-origin"
                            }
                        )


                        .then(function(response) {

                            return response.json();

                        })


                        .then(function(data) {


                            if (!data.success) {

                                alert(data.message);

                                return;

                            }


                            if (data.following) {


                                profileFollowButton.textContent =
                                    "Following";


                                profileFollowButton.classList.add(
                                    "following_profile_button"
                                );


                            } else {


                                profileFollowButton.textContent =
                                    "Follow";


                                profileFollowButton.classList.remove(
                                    "following_profile_button"
                                );


                            }


                        })


                        .catch(function(error) {

                            console.error(
                                "Profile follow error:",
                                error
                            );

                            alert(
                                "Something went wrong. Please try again."
                            );

                        })


                        .finally(function() {

                            profileFollowButton.disabled = false;

                        });


                    }
                );


            }


        </script>


    <?php endif; ?>
<script src="script.js"></script>

</body>

</html>