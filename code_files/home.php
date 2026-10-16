<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.html");
    exit();
}
include("db.php");
$user_id = $_SESSION["user_id"];
$sql = "SELECT name from users where id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$user_name = $user["name"];

$post_sql = "SELECT 
                posts.id,
                posts.content,
                posts.post_type,
                posts.created_at,
                users.name,
                (SELECT COUNT(*) 
                 FROM likes 
                 WHERE likes.post_id = posts.id) AS like_count,
                (SELECT COUNT(*) 
                 FROM comments 
                 WHERE comments.post_id = posts.id) AS comment_count
             FROM posts
             JOIN users ON posts.user_id = users.id
             ORDER BY posts.created_at DESC";
$post_result=$conn->query($post_sql);




?>






<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Home Page</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="style.css" />
</head>

<body>
    <!-- =========================
                    HOME HEADER
            =========================  -->

    <div id="header"></div>

    <!-- =========================
                        MAIN
            ========================= -->

    <main class="home_main">

       
    <div class="sidebar-column">

        <div id="left-sidebar"></div>

        <?php if (strtolower($_SESSION["account_type"] ?? "") === "faculty"): ?>

            <a href="faculty-dashboard.php" class="faculty-dashboard-btn">
                <i class="fa-solid fa-chalkboard-user"></i>
                Faculty Dashboard
            </a>

        <?php endif; ?>

    </div>


        <!-- =========================
                        HOME MIDDLE CONTENT START
                ========================= -->

        <div class="home_middle">

            <div class="home_middle_top">
                <div class="home_middle_top_top">
                    <div class="profile_pic"><img src="../img/profile_pic.jpg" alt="Profile Picture"
                            class="profile_pic" /></div>
                    <div class="post_input"><input type="text" name="" id=""
                            placeholder="Whats on your mind,<?php echo $user_name; ?>?"></div>
                    <div class="post_create_area">

                        <textarea class="post_text" placeholder="What's on your mind, <?php echo $user_name; ?>">
        </textarea>

                        <div class="post_options">
                            <button>Photo</button>
                            <button>Video</button>
                            <button>Article</button>
                        </div>

                        <button class="create_post_btn">Post</button>

                    </div>

                </div>

                <div class="home_middle_bottom_bottom">
                    <div class="photo"><img src="../img/photo_logo.jpg" alt="photo logo" class="photo_logo">
                        <p>Photo</p>
                    </div>
                    <div class="video"><img src="../img/video_logo.jpg" alt="video logo" class="video_logo">
                        <p>Video</p>
                    </div>
                    <div class="article"><img src="../img/article_logo.jpg" alt="article logo" class="article_logo">
                        <p>Article</p>
                </div>
            </div>

           <div class="posts_feed">
             <div class="home_middle_middle post_template">

                <div class="home_middle_middle_top">
                    <i class="fa-solid fa-bullhorn"></i>
                    <p>OFFICIAL ANNOUNCEMENT</p>
                </div>

                <div class="home_middle_middle_middle">
                    <img src="../img/nusrat.jpg" alt="Nusrat pic" class="nusrat_pic">
                    <div class="post_name_time">
                        <p class="others_name">Dr. Nusrat Jahan</p>
                        <p>1h ago</p>
                    </div>

                </div>
                <div class="home_middle_posts">
                    <p>📢 Important announcement: The Mid-Term Exam schedule for CSE 3102 (Data Structures) has been
                        updated. Please check the university portal for the revised timetable. All students of Batch 41
                        and 42 are required to confirm attendance. Reach out during office hours for any queries.</p>
                </div>
                <div class="like_comment_counter">
                    <p class="like-count">123 Likes</p>
                    <p class="comment-count">23 Comments</p>
                    <p>11 Shares</p>
                </div>
                <div class="home_middle_middle_bottom">
                    <div class="post_actions">
                        <button class="action_btn like">
                            <i class="fa-regular fa-thumbs-up like_icon"></i><span>Like</span>
                        </button>

                        <button class="action_btn comment">
                            <i class="fa-regular fa-comment comment_icon"></i> <span>Comment</span>
                        </button>

                        <button class="action_btn share">
                            <i class="fa-solid fa-share share_icon"></i><span>Share</span>
                        </button>
                    </div>
                </div>

                <!-- Comment Section -->
                <div class="comment_section">
                    <div class="comments_list"></div>

                    <div class="comment_input_box">
                        <img src="../img/profile_pic.jpg" alt="User Profile" class="comment_user_pic">

                        <div class="comment_field_wrapper">
                            <input type="text" class="comment_input" placeholder="Write a comment...">
                            <button class="send_comment_btn" type="button">Comment</button>
                        </div>
                    </div>
                </div>
            </div>
    <?php while($post= $post_result->fetch_assoc()){ ?>
            <div class="home_middle_bottom" data-post-id="<?php echo $post['id']; ?>">
                <div class="home_middle_bottom_top">
                    <img src="../img/bean.jpg" alt="Bean pic">
                    <div class="past_name_time">
                        <p class="others_name"><?php echo $post["name"]; ?></p>
                        <p> <?php echo $post["created_at"]; ?></p>
                    </div>

                </div>
                <div class="middle_posts">
                    <p><?php echo $post["content"]; ?></p>
                    <p class="hash_tags">#UIU #CSE #IoT #SmartCity</p> <img src="../img/post.jpg" alt="Post pic"
                        class="post_pic">
                </div>

                <div class="like_comment_counter">
                    <p class="like-count"><?php echo $post['like_count']; ?> Likes</p>
<p class="comment-count"><?php echo $post['comment_count']; ?> Comments</p>
<p>0 Shares</p>
                </div>

                <div class="home_middle_bottom_bottom">
                    <button class="action_btn like">
                        <i class="fa-regular fa-thumbs-up like_icon"></i><span>Like</span>
                    </button>

                    <button class="action_btn comment">
                        <i class="fa-regular fa-comment comment_icon"></i><span>Comment</span>
                    </button>

                    <button class="action_btn share">
                        <i class="fa-solid fa-share share_icon"></i><span>Share</span>
                    </button>
                </div>

                <!-- Comment Section -->
                <div class="comment_section">
                    <div class="comments_list"></div>

                    <div class="comment_input_box">
                        <img src="../img/profile_pic.jpg" alt="User Profile" class="comment_user_pic">

                        <div class="comment_field_wrapper">
                            <input type="text" class="comment_input" placeholder="Write a comment...">
                            <button class="send_comment_btn" type="button">Post</button>
                        </div>
                    </div>
                </div>

            </div>
            <?php } ?>
           </div>
        </div>
        </div>

        <!-- =========================
                        HOME MIDDLE CONTENT END
                ========================= -->

        <div id="right-sidebar"></div>

    </main>


    <script src="script.js"></script>
</body>

</html>